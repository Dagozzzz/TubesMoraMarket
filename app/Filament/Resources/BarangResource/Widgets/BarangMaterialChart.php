<?php

namespace App\Filament\Resources\BarangResource\Widgets;

use App\Models\Barang;
use Filament\Widgets\ChartWidget;

class BarangMaterialChart extends ChartWidget
{
    protected static ?string $heading = 'Top 10 Harga Jual Barang';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '350px';

    protected function getData(): array
    {
        $items = Barang::query()
            ->orderByDesc('harga_jual')
            ->limit(10)
            ->get(['nama_barang', 'harga_jual']);

        if ($items->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'Harga Jual',
                        'data' => [0],
                        'backgroundColor' => '#94a3b8',
                    ],
                ],
                'labels' => ['Belum ada data'],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Harga Jual',
                    'data' => $items
                        ->map(fn (Barang $barang): float => (float) $barang->harga_jual)
                        ->all(),
                    'backgroundColor' => '#0f766e',
                ],
            ],
            'labels' => $items
                ->map(fn (Barang $barang): string => $barang->nama_barang)
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
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString("id-ID"); }',
                    ],
                ],
            ],
        ];
    }
}
