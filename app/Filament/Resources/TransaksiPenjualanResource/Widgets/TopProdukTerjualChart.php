<?php

namespace App\Filament\Resources\TransaksiPenjualanResource\Widgets;

use App\Models\DetailTransaksiPenjualan;
use Filament\Widgets\ChartWidget;

class TopProdukTerjualChart extends ChartWidget
{
    protected static ?string $heading = 'Top 10 Produk Terjual';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '350px';

    protected function getData(): array
    {
        $items = DetailTransaksiPenjualan::query()
            ->selectRaw('nama_barang, SUM(jumlah) as total_terjual')
            ->groupBy('nama_barang')
            ->orderByDesc('total_terjual')
            ->limit(10)
            ->get();

        if ($items->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Qty Terjual',
                        'data' => [0],
                        'backgroundColor' => '#94a3b8',
                    ],
                ],
                'labels' => ['No Data Penjualan'],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Qty Terjual',
                    'data' => $items
                        ->map(fn ($item): int => (int) $item->total_terjual)
                        ->all(),
                    'backgroundColor' => [
                        '#16a34a',
                        '#2563eb',
                        '#f59e0b',
                        '#dc2626',
                        '#7c3aed',
                        '#0891b2',
                        '#4f46e5',
                        '#0d9488',
                        '#ea580c',
                        '#be123c',
                    ],
                ],
            ],
            'labels' => $items
                ->map(fn ($item): string => (string) $item->nama_barang)
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
