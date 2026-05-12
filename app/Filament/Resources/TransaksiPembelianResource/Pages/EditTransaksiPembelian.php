<?php

namespace App\Filament\Resources\TransaksiPembelianResource\Pages;

use App\Filament\Resources\TransaksiPembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaksiPembelian extends EditRecord
{
    protected static string $resource = TransaksiPembelianResource::class;

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
        $total       = (float) ($data['total_harga'] ?? 0);
        $jumlahBayar = (float) ($data['jumlah_bayar'] ?? 0);

        // ✅ Status dihitung otomatis saat data disimpan ke DB
        if ($jumlahBayar <= 0) {
            $data['status_pembayaran'] = 'belum_lunas';
        } elseif ($jumlahBayar >= $total && $total > 0) {
            $data['status_pembayaran'] = 'lunas';
        } else {
            $data['status_pembayaran'] = 'cicilan';
        }

        $data['kembalian'] = max(0, $jumlahBayar - $total);

        return $data;
    }
}