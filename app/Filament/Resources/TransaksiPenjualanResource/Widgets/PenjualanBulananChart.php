<?php

namespace App\Filament\Resources\TransaksiPenjualanResource\Widgets;

use App\Models\TransaksiPenjualan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PenjualanBulananChart extends ChartWidget
{
    protected static ?string $heading = 'Total Penjualan per Bulan';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $start = now()->subMonths(11)->startOfMonth();

        $transactions = TransaksiPenjualan::query()
            ->where('tanggal_penjualan', '>=', $start)
            ->where('status_pembayaran', 'lunas')
            ->get(['tanggal_penjualan', 'total_harga']);

        if ($transactions->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Total Penjualan',
                        'data' => [0],
                        'backgroundColor' => '#94a3b8',
                        'borderColor' => '#94a3b8',
                    ],
                ],
                'labels' => ['Belum ada data'],
            ];
        }

        $totalsByMonth = $transactions
            ->groupBy(fn (TransaksiPenjualan $transaction): string => $transaction->tanggal_penjualan->format('Y-m'))
            ->map(fn ($items): float => (float) $items->sum('total_harga'));

        $labels = [];
        $values = [];

        for ($i = 0; $i < 12; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $labels[] = $month->translatedFormat('M Y');
            $values[] = round((float) ($totalsByMonth[$key] ?? 0), 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan',
                    'data' => $values,
                    'backgroundColor' => '#2563eb',
                    'borderColor' => '#1d4ed8',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString("id-ID"); }',
                    ],
                ],
            ],
        ];
    }
}
