<?php

namespace App\Http\Controllers;

use App\Models\ServiceHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Mpdf\Mpdf;

class ReportController extends Controller
{
    private function getData(): array
    {
        $userId = auth()->id();
        $now    = now();
        $prev   = $now->copy()->subMonth();

        $base = fn ($q) => $q->whereHas('vehicle', fn ($v) => $v->where('user_id', $userId));

        $current = ServiceHistory::where($base)
            ->whereMonth('service_date', $now->month)
            ->whereYear('service_date', $now->year)
            ->selectRaw('COALESCE(SUM(amount),0) as total, COUNT(*) as count')
            ->first();

        $prevTotal = ServiceHistory::where($base)
            ->whereMonth('service_date', $prev->month)
            ->whereYear('service_date', $prev->year)
            ->sum('amount');

        $chart = [];
        for ($i = 5; $i >= 0; $i--) {
            $date  = $now->copy()->subMonths($i);
            $total = ServiceHistory::where($base)
                ->whereMonth('service_date', $date->month)
                ->whereYear('service_date', $date->year)
                ->sum('amount');

            $chart[] = [
                'label'  => ucfirst($date->translatedFormat('M/y')),
                'amount' => (float) $total,
            ];
        }

        $currentTotal = (float) $current->total;
        $currentCount = (int)   $current->count;
        $prevAmount   = (float) $prevTotal;

        $growth = $prevAmount > 0
            ? round((($currentTotal - $prevAmount) / $prevAmount) * 100, 1)
            : null;

        return [
            'current_month'        => [
                'total' => $currentTotal,
                'count' => $currentCount,
                'avg'   => $currentCount > 0 ? round($currentTotal / $currentCount, 2) : 0,
            ],
            'previous_month_total' => $prevAmount,
            'growth_percent'       => $growth,
            'chart'                => $chart,
        ];
    }

    public function financial(): JsonResponse
    {
        return response()->json($this->getData());
    }

    public function exportPdf(): Response
    {
        $data = $this->getData();
        $user = auth()->user();
        $now  = now();

        $fmt = fn (float $v) => 'R$ ' . number_format($v, 2, ',', '.');

        $growthHtml = '';
        if ($data['growth_percent'] !== null) {
            $color      = $data['growth_percent'] >= 0 ? '#16a34a' : '#dc2626';
            $sign       = $data['growth_percent'] >= 0 ? '+' : '';
            $growthHtml = "<span style='color:{$color};font-weight:600'>{$sign}{$data['growth_percent']}%</span> vs mês anterior";
        } else {
            $growthHtml = "<span style='color:#9ca3af'>Sem dados do mês anterior</span>";
        }

        $chartRows = '';
        foreach ($data['chart'] as $month) {
            $chartRows .= "
                <tr>
                    <td>{$month['label']}</td>
                    <td style='text-align:right'>{$fmt($month['amount'])}</td>
                </tr>";
        }

        $companyLine = $user->company_name ? " &nbsp;|&nbsp; {$user->company_name}" : '';

        $html = "
        <html><head><style>
            body { font-family: Arial, sans-serif; color: #1f2937; font-size: 13px; }
            h1 { color: #2563eb; font-size: 22px; margin-bottom: 4px; }
            .subtitle { color: #6b7280; font-size: 12px; margin-bottom: 28px; }
            .cards { width: 100%; border-collapse: separate; border-spacing: 8px; margin-bottom: 28px; }
            .card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; }
            .card-label { color: #6b7280; font-size: 11px; margin-bottom: 6px; }
            .card-value { color: #111827; font-size: 20px; font-weight: 700; }
            .section-title { font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; }
            table.monthly { width: 100%; border-collapse: collapse; }
            table.monthly th { background: #2563eb; color: #fff; padding: 8px 12px; text-align: left; font-size: 12px; }
            table.monthly td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; font-size: 12px; }
            table.monthly tr:nth-child(even) td { background: #f9fafb; }
            .footer { color: #9ca3af; font-size: 10px; margin-top: 32px; text-align: center; }
        </style></head><body>

        <h1>Veekar — Relatório Financeiro</h1>
        <div class='subtitle'>
            Gerado em {$now->format('d/m/Y H:i')} &nbsp;|&nbsp; {$user->name}{$companyLine}
        </div>

        <table class='cards'>
            <tr>
                <td class='card' width='33%'>
                    <div class='card-label'>Faturamento do mês</div>
                    <div class='card-value'>{$fmt($data['current_month']['total'])}</div>
                    <div style='font-size:11px;color:#6b7280;margin-top:4px'>{$growthHtml}</div>
                </td>
                <td class='card' width='33%'>
                    <div class='card-label'>Atendimentos no mês</div>
                    <div class='card-value'>{$data['current_month']['count']}</div>
                </td>
                <td class='card' width='33%'>
                    <div class='card-label'>Ticket médio</div>
                    <div class='card-value'>{$fmt($data['current_month']['avg'])}</div>
                </td>
            </tr>
        </table>

        <div class='section-title'>Faturamento — últimos 6 meses</div>
        <table class='monthly'>
            <tr>
                <th>Mês</th>
                <th style='text-align:right'>Faturamento</th>
            </tr>
            {$chartRows}
        </table>

        <div class='footer'>Veekar · Sistema de Histórico Automotivo · veekar.vercel.app</div>
        </body></html>";

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

        $filename = 'relatorio-veekar-' . $now->format('Y-m') . '.pdf';

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
