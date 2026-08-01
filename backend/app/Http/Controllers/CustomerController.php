<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $query = Customer::where('user_id', $userId);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
                  ->orWhere('cpf', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')->paginate(20);

        return response()->json($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $subscription = $request->attributes->get('subscription');

        if ($subscription?->isTrial() && Customer::where('user_id', $userId)->count() >= 3) {
            return response()->json([
                'message' => 'Limite do período gratuito atingido (3 clientes). Assine o Veekar para continuar.',
                'code'    => 'trial_limit_reached',
            ], 403);
        }

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'cpf'   => ['nullable', 'string', 'max:14', Rule::unique('customers', 'cpf')->where('user_id', $userId)],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['user_id'] = $userId;
        $customer = Customer::create($validated);

        return response()->json($customer, 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        abort_if($customer->user_id !== auth()->id(), 403);

        return response()->json($customer);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $userId = auth()->id();
        abort_if($customer->user_id !== $userId, 403);

        $validated = $request->validate([
            'name'  => ['sometimes', 'required', 'string', 'max:255'],
            'cpf'   => ['nullable', 'string', 'max:14', Rule::unique('customers', 'cpf')->where('user_id', $userId)->ignore($customer->id)],
            'phone' => ['sometimes', 'required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    public function import(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $subscription = $request->attributes->get('subscription');

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        $header = array_shift($rows) ?? [];
        $columns = $this->mapImportColumns($header);

        if (! $columns['name'] || ! $columns['phone']) {
            return response()->json([
                'message' => 'Não encontramos as colunas de Nome e Telefone na planilha. Verifique o cabeçalho.',
            ], 422);
        }

        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2; // +1 pelo header removido, +1 porque planilha começa em 1

            $name  = trim((string) ($row[$columns['name']] ?? ''));
            $phone = trim((string) ($row[$columns['phone']] ?? ''));
            $cpf   = $columns['cpf'] !== null ? (trim((string) ($row[$columns['cpf']] ?? '')) ?: null) : null;
            $email = $columns['email'] !== null ? (trim((string) ($row[$columns['email']] ?? '')) ?: null) : null;

            if ($name === '' && $phone === '') {
                continue; // linha vazia
            }

            if ($subscription?->isTrial() && Customer::where('user_id', $userId)->count() >= 3) {
                $errors[] = ['line' => $lineNumber, 'message' => 'Limite do período gratuito atingido (3 clientes).'];
                break;
            }

            $validator = Validator::make(
                ['name' => $name, 'phone' => $phone, 'cpf' => $cpf, 'email' => $email],
                [
                    'name'  => ['required', 'string', 'max:255'],
                    'phone' => ['required', 'string', 'max:20'],
                    'cpf'   => ['nullable', 'string', 'max:14', Rule::unique('customers', 'cpf')->where('user_id', $userId)],
                    'email' => ['nullable', 'email', 'max:255'],
                ],
                [
                    'name.required'  => 'Nome é obrigatório.',
                    'phone.required' => 'Telefone é obrigatório.',
                    'name.max'       => 'Nome muito longo.',
                    'phone.max'      => 'Telefone muito longo.',
                    'cpf.max'        => 'CPF inválido.',
                    'cpf.unique'     => 'Já existe um cliente com esse CPF.',
                    'email.email'    => 'E-mail inválido.',
                    'email.max'      => 'E-mail muito longo.',
                ],
            );

            if ($validator->fails()) {
                $errors[] = ['line' => $lineNumber, 'message' => $validator->errors()->first()];
                continue;
            }

            Customer::create([
                'user_id' => $userId,
                'name'    => $name,
                'phone'   => $phone,
                'cpf'     => $cpf,
                'email'   => $email,
            ]);

            $imported++;
        }

        return response()->json(['imported' => $imported, 'errors' => $errors]);
    }

    /**
     * Identifica em quais colunas da planilha estão nome, telefone, cpf e e-mail,
     * reconhecendo variações comuns de nome de coluna e ignorando o resto.
     *
     * @return array{name: ?int, phone: ?int, cpf: ?int, email: ?int}
     */
    private function mapImportColumns(array $header): array
    {
        $synonyms = [
            'name'  => ['nome', 'cliente', 'nome do cliente', 'name'],
            'phone' => ['telefone', 'celular', 'whatsapp', 'fone', 'contato', 'phone'],
            'cpf'   => ['cpf', 'documento', 'cpf/cnpj'],
            'email' => ['email', 'e-mail', 'e mail'],
        ];

        $columns = ['name' => null, 'phone' => null, 'cpf' => null, 'email' => null];

        foreach ($header as $index => $rawLabel) {
            $label = \Illuminate\Support\Str::of((string) $rawLabel)->ascii()->lower()->trim()->toString();

            foreach ($synonyms as $field => $options) {
                if ($columns[$field] === null && in_array($label, $options, true)) {
                    $columns[$field] = $index;
                }
            }
        }

        return $columns;
    }

    public function destroy(Customer $customer): JsonResponse
    {
        abort_if($customer->user_id !== auth()->id(), 403);

        $customer->delete();

        return response()->json(null, 204);
    }
}
