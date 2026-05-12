<?php

namespace App\Filament\Resources\TransaksiPembelianResource\Pages;

use App\Filament\Resources\TransaksiPembelianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransaksiPembelians extends ListRecords
{
    protected static string $resource = TransaksiPembelianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('+ Tambah Transaksi'),
        ];
    }
}