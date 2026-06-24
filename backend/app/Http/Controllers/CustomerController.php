<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function destroy(Customer $customer): JsonResponse
    {
        abort_if($customer->user_id !== auth()->id(), 403);

        $customer->delete();

        return response()->json(null, 204);
    }
}
