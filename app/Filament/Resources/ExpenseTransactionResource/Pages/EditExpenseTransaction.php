<?php

namespace App\Filament\Resources\ExpenseTransactionResource\Pages;

use App\Filament\Resources\ExpenseTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpenseTransaction extends EditRecord
{
    protected static string $resource = ExpenseTransactionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Otomatis perbarui jurnal setelah transaksi beban diedit.
     */
    protected function afterSave(): void
    {
        $this->record->buatJurnal();
    }
}