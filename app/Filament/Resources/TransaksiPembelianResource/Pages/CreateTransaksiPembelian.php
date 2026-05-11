<?php

namespace App\Filament\Resources\TransaksiPembelianResource\Pages;

use App\Filament\Resources\TransaksiPembelianResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaksiPembelian extends CreateRecord
{
    protected static string $resource = TransaksiPembelianResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $total = 0;

        // hitung total dari semua subtotal repeater
        if (isset($this->data['detailTransaksi'])) {

            foreach ($this->data['detailTransaksi'] as $item) {

                $subtotal = (float) ($item['subtotal'] ?? 0);

                $total += $subtotal;
            }
        }

        // simpan total harga
        $data['total_harga'] = $total;

        // ambil jumlah bayar
        $jumlahBayar = (float) str_replace('.', '', $data['jumlah_bayar'] ?? 0);

        // hitung kembalian
        $data['kembalian'] = max(0, $jumlahBayar - $total);

        // status pembayaran
        if ($jumlahBayar <= 0) {

            $data['status_pembayaran'] = 'belum_lunas';

        } elseif ($jumlahBayar >= $total) {

            $data['status_pembayaran'] = 'lunas';

        } else {

            $data['status_pembayaran'] = 'cicilan';
        }

        return $data;
    }
}