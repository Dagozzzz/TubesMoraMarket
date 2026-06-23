<?php

namespace App\Filament\Resources\TransaksiPenjualanResource\Pages;

use App\Filament\Resources\TransaksiPenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaksiPenjualan extends EditRecord
{
    protected static string $resource = TransaksiPenjualanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $total = collect($this->data['detailTransaksi'] ?? [])
            ->sum(fn($item) => (float) ($item['subtotal'] ?? 0));

        $data['total_harga'] = $total;

        return $data;
    }

    // otomatis update jurnal saat penjualan diedit
    protected function afterSave(): void
    {
        $this->record->buatJurnal();
    }
}

