<?php

namespace App\Filament\Widgets;

use App\Models\TransaksiPenjualan;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class TrenPenjualanBulananChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Penjualan Bulanan';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $start = CarbonImmutable::now()->startOfMonth()->subMonths(5);
        $end = CarbonImmutable::now()->endOfMonth();

        $totalsByMonth = TransaksiPenjualan::query()
            ->where('status_pembayaran', 'lunas')
            ->whereBetween('tanggal_penjualan', [$start, $end])
            ->get(['tanggal_penjualan', 'total_harga'])
            ->groupBy(fn (TransaksiPenjualan $transaction): string => $transaction->tanggal_penjualan->format('Y-m'))
            ->map(fn ($transactions): float => (float) $transactions->sum('total_harga'));

        $months = collect(range(0, 5))
            ->map(fn (int $index): CarbonImmutable => $start->addMonths($index));

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan',
                    'data' => $months
                        ->map(fn (CarbonImmutable $month): float => (float) $totalsByMonth->get($month->format('Y-m'), 0))
                        ->all(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.18)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $months
                ->map(fn (CarbonImmutable $month): string => $month->format('M Y'))
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
