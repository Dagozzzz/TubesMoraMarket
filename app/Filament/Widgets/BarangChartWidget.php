<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use Filament\Widgets\ChartWidget;

class BarangChartWidget extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Jumlah Barang per Kategori';
    }

    protected function getData(): array
    {
        $items = Barang::query()
            ->selectRaw('kategori, COUNT(*) as total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->pluck('total', 'kategori');

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Barang',
                    'data' => $items->values()->all(),
                    'backgroundColor' => [
                        '#2563eb',
                        '#16a34a',
                        '#f59e0b',
                        '#dc2626',
                        '#7c3aed',
                        '#0891b2',
                    ],
                ],
            ],
            'labels' => $items->keys()->all(),
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
