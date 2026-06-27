<?php

namespace App\Http\Controllers;

use App\Models\ServiceHistory;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
                'return_date'     => $validated['return_date'] ?? null,
                'return_reason'   => $validated['return_reason'] ?? null,
                'entry_checklist' => $validated['entry_checklist'] ?? null,
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

        $plate       = $vehicle->plate;
        $brand       = $vehicle->brand;
        $model       = $vehicle->model;
        $year        = $vehicle->year;
        $color       = $vehicle->color;
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

        $filename = 'checklist-' . $vehicle->plate . '-' . $serviceHistory->service_date->format('Y-m-d') . '.pdf';

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
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
