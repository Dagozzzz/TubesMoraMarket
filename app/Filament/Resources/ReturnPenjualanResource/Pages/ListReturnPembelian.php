<?php

namespace App\Filament\Resources\ReturnPembelianResource\Pages;

use App\Filament\Resources\ReturnPembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReturnPembelian extends ListRecords
{
    protected static string $resource = ReturnPembelianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Return Baru'),
        ];
    }
}