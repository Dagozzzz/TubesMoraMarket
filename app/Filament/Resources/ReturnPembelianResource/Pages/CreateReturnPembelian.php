<?php

namespace App\Filament\Resources\ReturnPembelianResource\Pages;

use App\Filament\Resources\ReturnPembelianResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReturnPembelian extends CreateRecord
{
    protected static string $resource = ReturnPembelianResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Return pembelian berhasil dibuat';
    }

    /**
     * Otomatis buat jurnal setelah return pembelian disimpan.
     * Jurnal hanya benar-benar ditulis jika status = 'disetujui'.
     */
    protected function afterCreate(): void
    {
        $this->record->buatJurnal();
    }
}
