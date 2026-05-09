<?php
namespace App\Filament\Resources\PresensiResource\Pages;
use App\Filament\Resources\PresensiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditPresensi extends EditRecord
{
    protected static string $resource = PresensiResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // TAMBAHAN: redirect ke list setelah save
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}