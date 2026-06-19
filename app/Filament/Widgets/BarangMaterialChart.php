<?php

namespace App\Filament\Widgets;

use App\Models\Barang;
use Filament\Widgets\ChartWidget;

class BarangMaterialChart extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Top 10 Harga Jual Barang';
    }

    protected function getData(): array
    {
        $items = Barang::query()
            ->orderByDesc('harga_jual')
            ->limit(10)
            ->get(['nama_barang', 'harga_jual']);

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
        ];
    }
}
