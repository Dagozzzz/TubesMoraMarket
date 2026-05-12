<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarang extends EditRecord
{
    protected static string $resource = BarangResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Barang')
                ->modalDescription('Are you sure you would like to do this?')
                ->modalSubmitActionLabel('Delete')
                ->modalCancelActionLabel('Cancel')
                ->successNotificationTitle('Barang berhasil dihapus'),
        ];
    }
}
