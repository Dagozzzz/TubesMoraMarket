<?php

namespace App\Filament\Resources\KategoriBarangResource\Pages;

use App\Filament\Resources\KategoriBarangResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKategoriBarang extends CreateRecord
{
    protected static string $resource = KategoriBarangResource::class;

    // TAMBAHAN: redirect ke list setelah create
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}