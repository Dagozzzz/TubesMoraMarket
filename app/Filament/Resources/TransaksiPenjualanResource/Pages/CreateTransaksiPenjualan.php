<?php

namespace App\Filament\Resources\TransaksiPenjualanResource\Pages;

use App\Filament\Resources\TransaksiPenjualanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaksiPenjualan extends CreateRecord
{
    protected static string $resource = TransaksiPenjualanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $total = collect($this->data['detailTransaksi'] ?? [])
            ->sum(fn($item) => (float) ($item['subtotal'] ?? 0));

        $data['total_harga'] = $total;

        return $data;
    }

    // otomatis buat jurnal setelah penjualan disimpan
    protected function afterCreate(): void
    {
        $this->record->buatJurnal();
    }
}