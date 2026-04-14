<?php

namespace App\Filament\Resources\KategoriSupplierResource\Pages;

use App\Filament\Resources\KategoriSupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKategoriSupplier extends EditRecord
{
    protected static string $resource = KategoriSupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
