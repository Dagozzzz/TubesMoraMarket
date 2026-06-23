<?php

namespace App\Filament\Resources\ReturnPembelianResource\Pages;

use App\Filament\Resources\ReturnPembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReturnPembelian extends EditRecord
{
    protected static string $resource = ReturnPembelianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === 'draft'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Return pembelian berhasil diperbarui';
    }

    /**
     * Otomatis perbarui jurnal saat return pembelian diedit.
     * - Jika status = 'disetujui' → jurnal dibuat / diperbarui
     * - Jika status lain → jurnal lama dihapus
     */
    protected function afterSave(): void
    {
        $this->record->buatJurnal();
    }
}
