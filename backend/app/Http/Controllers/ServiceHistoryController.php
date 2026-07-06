<?php

namespace App\Http\Controllers;

use App\Models\ServiceHistory;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class ServiceHistoryController extends Controller
{
    public function index(Vehicle $vehicle): JsonResponse
    {
        abort_if($vehicle->user_id !== auth()->id(), 403);

        $histories = $vehicle->serviceHistories()->with('items')->get();

        return response()->json($histories);
    }

    public function store(Request $request, Vehicle $vehicle): JsonResponse
    {
        abort_if($vehicle->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'service_date'                      => ['required', 'date'],
            'description'                       => ['required', 'string'],
            'mileage'                           => ['required', 'integer', 'min:0'],
            'amount'                            => ['nullable', 'numeric', 'min:0'],
            'notes'                             => ['nullable', 'string'],
            'return_date'                       => ['nullable', 'date'],
            'return_reason'                     => ['nullable', 'string', 'max:255'],
            'entry_checklist'                   => ['nullable', 'array'],
            'entry_checklist.scratches'         => ['nullable', 'boolean'],
            'entry_checklist.dents'             => ['nullable', 'boolean'],
            'entry_checklist.fuel_level'        => ['nullable', 'string', 'in:vazio,1/4,1/2,3/4,cheio'],
            'entry_checklist.observations'      => ['nullable', 'string'],
            'insurer'                           => ['nullable', 'string', 'max:255'],
            'claim_number'                      => ['nullable', 'string', 'max:255'],
            'insurance_status'                  => ['nullable', 'string', 'in:aguardando,aprovado,recusado'],
            'estimated_delivery'                => ['nullable', 'date'],
            'items'                             => ['nullable', 'array'],
            'items.*.description'               => ['required', 'string', 'max:255'],
            'items.*.quantity'                  => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price'                => ['required', 'numeric', 'min:0'],
        ]);

        $history = DB::transaction(function () use ($validated, $vehicle) {
            $items = $validated['items'] ?? [];

            $amount = count($items) > 0
                ? collect($items)->sum(fn ($i) => $i['quantity'] * $i['unit_price'])
                : ($validated['amount'] ?? null);

            $history = $vehicle->serviceHistories()->create([
                'service_date'    => $validated['service_date'],
                'description'     => $validated['description'],
                'mileage'         => $validated['mileage'],
                'amount'          => $amount,
                'notes'           => $validated['notes'] ?? null,
                'return_date'      => $validated['return_date'] ?? null,
                'return_reason'    => $validated['return_reason'] ?? null,
                'entry_checklist'  => $validated['entry_checklist'] ?? null,
                'insurer'             => $validated['insurer'] ?? null,
                'claim_number'        => $validated['claim_number'] ?? null,
                'insurance_status'    => $validated['insurance_status'] ?? null,
                'estimated_delivery'  => $validated['estimated_delivery'] ?? null,
                'tracking_token'      => Str::random(32),
            ]);

            foreach ($items as $item) {
                $history->items()->create($item);
            }

            if ($validated['mileage'] > $vehicle->mileage) {
                $vehicle->update(['mileage' => $validated['mileage']]);
            }

            return $history->load('items');
        });

        return response()->json($history, 201);
    }

    public function update(Request $request, Vehicle $vehicle, ServiceHistory $serviceHistory): JsonResponse
    {
        abort_if($vehicle->user_id !== auth()->id(), 403);
        abort_if($serviceHistory->vehicle_id !== $vehicle->id, 404);

        $validated = $request->validate([
            'service_date'                      => ['required', 'date'],
            'description'                       => ['required', 'string'],
            'mileage'                           => ['required', 'integer', 'min:0'],
            'notes'                             => ['nullable', 'string'],
            'return_date'                       => ['nullable', 'date'],
            'return_reason'                     => ['nullable', 'string', 'max:255'],
            'insurer'                           => ['nullable', 'string', 'max:255'],
            'claim_number'                      => ['nullable', 'string', 'max:255'],
            'insurance_status'                  => ['nullable', 'string', 'in:aguardando,aprovado,recusado'],
            'estimated_delivery'                => ['nullable', 'date'],
        ]);

        $serviceHistory->update($validated);

        if ($validated['mileage'] > $vehicle->mileage) {
            $vehicle->update(['mileage' => $validated['mileage']]);
        }

        return response()->json($serviceHistory->load('items'));
    }

    public function checklistPdf(Vehicle $vehicle, ServiceHistory $serviceHistory): Response
    {
        abort_if($vehicle->user_id !== auth()->id(), 403);
        abort_if($serviceHistory->vehicle_id !== $vehicle->id, 404);

        $vehicle->load('customer');
        $checklist = $serviceHistory->entry_checklist ?? [];

        $fuelIcons = ['vazio' => '○○○○○', '1/4' => '●○○○○', '1/2' => '●●○○○', '3/4' => '●●●○○', 'cheio' => '●●●●●'];
        $fuelDisplay = $fuelIcons[$checklist['fuel_level'] ?? ''] ?? '—';
        $fuelLevel   = $checklist['fuel_level'] ?? '—';
        $customerName = $vehicle->customer ? $vehicle->customer->name : '—';
        $scratches   = ! empty($checklist['scratches']) ? '&#10003;' : '&#9633;';
        $dents       = ! empty($checklist['dents'])     ? '&#10003;' : '&#9633;';
        $observations = $checklist['observations'] ?? '—';

        $insurer         = $serviceHistory->insurer ?? null;
        $claimNumber     = $serviceHistory->claim_number ?? null;
        $insuranceStatus = $serviceHistory->insurance_status ?? null;
        $insuranceStatusLabels = ['aguardando' => 'Aguardando aprovação', 'aprovado' => 'Aprovado', 'recusado' => 'Recusado'];
        $insuranceStatusLabel  = $insuranceStatus ? ($insuranceStatusLabels[$insuranceStatus] ?? $insuranceStatus) : null;
        $insuranceColors = ['aguardando' => '#b45309', 'aprovado' => '#15803d', 'recusado' => '#dc2626'];
        $insuranceColor  = $insuranceStatus ? ($insuranceColors[$insuranceStatus] ?? '#374151') : '#374151';
        $hasInsurance    = $insurer || $claimNumber || $insuranceStatus;

        $plate       = $vehicle->plate ?? '—';
        $brand       = $vehicle->brand;
        $model       = $vehicle->model;
        $year        = $vehicle->year;
        $color       = $vehicle->color ?? '—';
        $mileage     = $serviceHistory->mileage;
        $date        = $serviceHistory->service_date->format('d/m/Y');
        $description = $serviceHistory->description;

        $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"></head>'
            . '<body style="font-family:Arial,sans-serif;font-size:13px;color:#111;margin:0;padding:0;">'
            . '<div style="border-bottom:3px solid #2563eb;padding-bottom:12px;margin-bottom:20px;">'
            . '<div style="font-size:22px;font-weight:bold;color:#2563eb;">Veekar</div>'
            . '<div style="font-size:10px;color:#6b7280;">Sistema de hist&oacute;rico automotivo &mdash; Data: ' . $date . '</div>'
            . '</div>'
            . '<div style="font-size:16px;font-weight:bold;margin-bottom:16px;">Checklist de Entrada do Ve&iacute;culo</div>'
            . '<table style="width:100%;border-collapse:collapse;margin-bottom:16px;">'
            . '<tr>'
            . '<td style="padding:4px 0;width:50%"><strong>Placa:</strong> ' . $plate . '</td>'
            . '<td style="padding:4px 0"><strong>Marca/Modelo:</strong> ' . $brand . ' ' . $model . '</td>'
            . '</tr><tr>'
            . '<td style="padding:4px 0"><strong>Ano:</strong> ' . $year . '</td>'
            . '<td style="padding:4px 0"><strong>Cor:</strong> ' . $color . '</td>'
            . '</tr><tr>'
            . '<td style="padding:4px 0"><strong>KM entrada:</strong> ' . $mileage . ' km</td>'
            . '<td style="padding:4px 0"><strong>Cliente:</strong> ' . $customerName . '</td>'
            . '</tr></table>'
            . '<div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin-bottom:16px;">'
            . '<div style="font-weight:bold;margin-bottom:12px;font-size:13px;">Condi&ccedil;&otilde;es do Ve&iacute;culo</div>'
            . '<table style="width:100%;border-collapse:collapse;"><tr>'
            . '<td style="padding:8px;border:1px solid #e5e7eb;width:50%;"><span style="font-size:16px;">' . $scratches . '</span>&nbsp;<strong>Riscos / Arranhões</strong></td>'
            . '<td style="padding:8px;border:1px solid #e5e7eb;"><span style="font-size:16px;">' . $dents . '</span>&nbsp;<strong>Amassados / Danos</strong></td>'
            . '</tr></table>'
            . '<div style="margin-top:12px;padding:8px;border:1px solid #e5e7eb;border-radius:4px;">'
            . '<strong>N&iacute;vel de Combust&iacute;vel:</strong>&nbsp;&nbsp;'
            . '<span style="font-family:monospace;font-size:14px;letter-spacing:2px;">' . $fuelDisplay . '</span>'
            . '&nbsp;&nbsp;<span style="color:#6b7280;">(' . $fuelLevel . ')</span>'
            . '</div>'
            . '<div style="margin-top:12px;padding:8px;border:1px solid #e5e7eb;border-radius:4px;min-height:50px;">'
            . '<strong>Observa&ccedil;&otilde;es:</strong><br><span style="color:#374151;">' . $observations . '</span>'
            . '</div></div>'
            . '<div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin-bottom:16px;">'
            . '<div style="font-weight:bold;margin-bottom:4px;">Servi&ccedil;o Solicitado</div>'
            . '<div style="color:#374151;">' . $description . '</div>'
            . '</div>'
            . ($hasInsurance ? (
                '<div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin-bottom:16px;">'
                . '<div style="font-weight:bold;margin-bottom:10px;font-size:13px;">Informa&ccedil;&otilde;es do Seguro</div>'
                . '<table style="width:100%;border-collapse:collapse;"><tr>'
                . '<td style="padding:4px 0;width:50%"><strong>Seguradora:</strong> ' . ($insurer ?? '—') . '</td>'
                . '<td style="padding:4px 0"><strong>N&ordm; Sinistro:</strong> ' . ($claimNumber ?? '—') . '</td>'
                . '</tr><tr>'
                . '<td colspan="2" style="padding:4px 0"><strong>Status:</strong> <span style="color:' . $insuranceColor . ';font-weight:bold;">' . ($insuranceStatusLabel ?? '—') . '</span></td>'
                . '</tr></table>'
                . '</div>'
            ) : '')
            . '<div style="margin-top:40px;"><table style="width:100%;border-collapse:collapse;"><tr>'
            . '<td style="width:45%;text-align:center;padding-top:8px;"><div style="border-top:1px solid #111;padding-top:6px;font-size:11px;color:#6b7280;">Assinatura do Cliente</div></td>'
            . '<td style="width:10%;"></td>'
            . '<td style="width:45%;text-align:center;padding-top:8px;"><div style="border-top:1px solid #111;padding-top:6px;font-size:11px;color:#6b7280;">Respons&aacute;vel pela Oficina</div></td>'
            . '</tr></table></div>'
            . '<div style="margin-top:30px;text-align:center;font-size:10px;color:#9ca3af;">Documento gerado pelo Veekar &middot; ' . $date . '</div>'
            . '</body></html>';

        $tmpDir = storage_path('app/mpdf-tmp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 15,
            'margin_bottom' => 15,
            'tempDir'       => $tmpDir,
        ]);

        $mpdf->WriteHTML($html);

        $filename = 'checklist-' . ($vehicle->plate ?: 'veiculo-' . $vehicle->id) . '-' . $serviceHistory->service_date->format('Y-m-d') . '.pdf';

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function updateStatus(Request $request, Vehicle $vehicle, ServiceHistory $serviceHistory): JsonResponse
    {
        abort_if($vehicle->user_id !== auth()->id(), 403);
        abort_if($serviceHistory->vehicle_id !== $vehicle->id, 404);

        $validated = $request->validate([
            'service_status' => ['required', 'string', 'in:recebido,em_diagnostico,aguardando_pecas,em_servico,pronto,entregue'],
        ]);

        $serviceHistory->update($validated);

        return response()->json($serviceHistory);
    }

    public function updatePaymentStatus(Request $request, Vehicle $vehicle, ServiceHistory $serviceHistory): JsonResponse
    {
        abort_if($vehicle->user_id !== auth()->id(), 403);
        abort_if($serviceHistory->vehicle_id !== $vehicle->id, 404);

        $validated = $request->validate([
            'payment_status' => ['required', 'string', 'in:pendente,parcial,pago'],
        ]);

        $serviceHistory->update($validated);

        return response()->json($serviceHistory);
    }

    public function clientSummaryPdf(Vehicle $vehicle, ServiceHistory $serviceHistory): Response
    {
        abort_if($vehicle->user_id !== auth()->id(), 403);
        abort_if($serviceHistory->vehicle_id !== $vehicle->id, 404);

        $vehicle->load('customer');
        $serviceHistory->load('items');

        $fmt = fn (?string $v) => 'R$ ' . number_format((float) $v, 2, ',', '.');

        $customerName = $vehicle->customer ? $vehicle->customer->name : '—';
        $date         = $serviceHistory->service_date->format('d/m/Y');

        $statusLabels = ['pendente' => 'Pendente', 'parcial' => 'Pago parcialmente', 'pago' => 'Pago'];
        $statusColors = ['pendente' => '#dc2626', 'parcial' => '#b45309', 'pago' => '#16a34a'];
        $paymentStatus = $serviceHistory->payment_status ?? 'pendente';
        $statusLabel   = $statusLabels[$paymentStatus] ?? $paymentStatus;
        $statusColor   = $statusColors[$paymentStatus] ?? '#374151';

        $itemsRows = '';
        foreach ($serviceHistory->items as $item) {
            $subtotal = (float) $item->quantity * (float) $item->unit_price;
            $itemsRows .= '<tr>'
                . '<td style="padding:8px;border-bottom:1px solid #f3f4f6;">' . e($item->description) . '</td>'
                . '<td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:center;">' . (float) $item->quantity . '</td>'
                . '<td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">' . $fmt($item->unit_price) . '</td>'
                . '<td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">' . $fmt((string) $subtotal) . '</td>'
                . '</tr>';
        }

        $itemsTable = $itemsRows !== ''
            ? '<table style="width:100%;border-collapse:collapse;margin-bottom:16px;">'
                . '<tr><th style="text-align:left;padding:8px;background:#2563eb;color:#fff;font-size:12px;">Descrição</th>'
                . '<th style="text-align:center;padding:8px;background:#2563eb;color:#fff;font-size:12px;">Qtd</th>'
                . '<th style="text-align:right;padding:8px;background:#2563eb;color:#fff;font-size:12px;">Unitário</th>'
                . '<th style="text-align:right;padding:8px;background:#2563eb;color:#fff;font-size:12px;">Subtotal</th></tr>'
                . $itemsRows
                . '</table>'
            : '';

        $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"></head>'
            . '<body style="font-family:Arial,sans-serif;font-size:13px;color:#111;margin:0;padding:0;">'
            . '<div style="border-bottom:3px solid #2563eb;padding-bottom:12px;margin-bottom:20px;">'
            . '<div style="font-size:22px;font-weight:bold;color:#2563eb;">Veekar</div>'
            . '<div style="font-size:10px;color:#6b7280;">Resumo do atendimento &mdash; Data: ' . $date . '</div>'
            . '</div>'
            . '<div style="font-size:16px;font-weight:bold;margin-bottom:16px;">Resumo para o Cliente</div>'
            . '<table style="width:100%;border-collapse:collapse;margin-bottom:16px;">'
            . '<tr>'
            . '<td style="padding:4px 0;width:50%"><strong>Cliente:</strong> ' . e($customerName) . '</td>'
            . '<td style="padding:4px 0"><strong>Veículo:</strong> ' . e($vehicle->plate ?? '—') . ' &mdash; ' . e($vehicle->brand) . ' ' . e($vehicle->model) . '</td>'
            . '</tr></table>'
            . '<div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin-bottom:16px;">'
            . '<div style="font-weight:bold;margin-bottom:4px;">Serviço realizado</div>'
            . '<div style="color:#374151;">' . e($serviceHistory->description) . '</div>'
            . '</div>'
            . $itemsTable
            . '<table style="width:100%;border-collapse:collapse;margin-top:8px;">'
            . '<tr>'
            . '<td style="padding:10px 0;font-size:15px;"><strong>Valor total:</strong></td>'
            . '<td style="padding:10px 0;font-size:15px;text-align:right;"><strong>' . $fmt($serviceHistory->amount) . '</strong></td>'
            . '</tr><tr>'
            . '<td style="padding:6px 0;"><strong>Situação do pagamento:</strong></td>'
            . '<td style="padding:6px 0;text-align:right;"><span style="color:' . $statusColor . ';font-weight:bold;">' . $statusLabel . '</span></td>'
            . '</tr></table>'
            . '<div style="margin-top:30px;padding:10px;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;font-size:11px;color:#92400e;">'
            . 'Este documento é um resumo informativo gerado pelo Veekar e não substitui nota fiscal ou recibo oficial.'
            . '</div>'
            . '<div style="margin-top:20px;text-align:center;font-size:10px;color:#9ca3af;">Documento gerado pelo Veekar &middot; ' . $date . '</div>'
            . '</body></html>';

        $tmpDir = storage_path('app/mpdf-tmp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 15,
            'margin_bottom' => 15,
            'tempDir'       => $tmpDir,
        ]);

        $mpdf->WriteHTML($html);

        $filename = 'resumo-' . ($vehicle->plate ?: 'veiculo-' . $vehicle->id) . '-' . $serviceHistory->service_date->format('Y-m-d') . '.pdf';

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function publicShow(string $token): JsonResponse
    {
        $history = ServiceHistory::with(['vehicle.customer', 'items'])
            ->where('tracking_token', $token)
            ->firstOrFail();

        $vehicle = $history->vehicle;

        return response()->json([
            'id'                 => $history->id,
            'description'        => $history->description,
            'service_date'       => $history->service_date?->format('Y-m-d'),
            'estimated_delivery' => $history->estimated_delivery?->format('Y-m-d'),
            'notes'              => $history->notes,
            'service_status'     => $history->service_status ?? 'recebido',
            'amount'             => $history->amount,
            'items'              => $history->items->map(fn ($i) => [
                'description' => $i->description,
                'quantity'    => $i->quantity,
                'unit_price'  => $i->unit_price,
            ]),
            'vehicle' => [
                'plate' => $vehicle->plate,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'year'  => $vehicle->year,
                'color' => $vehicle->color,
            ],
            'customer' => $vehicle->customer ? [
                'name'  => $vehicle->customer->name,
                'phone' => $vehicle->customer->phone,
            ] : null,
        ]);
    }

    public function destroy(Vehicle $vehicle, ServiceHistory $serviceHistory): JsonResponse
    {
        abort_if($vehicle->user_id !== auth()->id(), 403);
        abort_if($serviceHistory->vehicle_id !== $vehicle->id, 404);

        $serviceHistory->delete();

        return response()->json(null, 204);
    }
}
