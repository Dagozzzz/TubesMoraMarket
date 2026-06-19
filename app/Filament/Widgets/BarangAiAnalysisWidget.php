<?php

namespace App\Filament\Widgets;

use App\Models\BarangAiAnalysis;
use Filament\Widgets\Widget;

class BarangAiAnalysisWidget extends Widget
{
    protected static string $view = 'filament.widgets.barang-ai-analysis-widget';

    protected int|string|array $columnSpan = 'full';

    public ?BarangAiAnalysis $analysis = null;

    public function mount(): void
    {
        $this->analysis = BarangAiAnalysis::query()->latest()->first();
    }
}
