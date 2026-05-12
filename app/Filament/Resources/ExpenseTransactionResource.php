<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseTransactionResource\Pages;
use App\Models\ChartOfAccount;
use App\Models\ExpenseTransaction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExpenseTransactionResource extends Resource
{
    protected static ?string $model = ExpenseTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationLabel = 'Input Beban';

    protected static ?string $pluralModelLabel = 'Transaksi Input Beban';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('no_transaksi')
                        ->label('No Transaksi')
                        ->default(fn () => self::generateTransactionNumber())
                        ->readOnly()
                        ->required()
                        ->unique(ignoreRecord: true),

                    DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->default(now())
                        ->required(),

                    Select::make('chart_of_account_id')
                        ->label('Akun Beban')
                        ->options(fn () => self::getExpenseAccountOptions())
                        ->native()
                        ->required(),

                    TextInput::make('jumlah')
                        ->label('Jumlah')
                        ->prefix('IDR')
                        ->numeric()
                        ->minValue(1)
                        ->required(),

                    Select::make('metode_pembayaran')
                        ->label('Metode Pembayaran')
                        ->options([
                            'Kas' => 'Kas',
                            'Transfer Bank' => 'Transfer Bank',
                            'QRIS' => 'QRIS',
                            'E-Wallet' => 'E-Wallet',
                        ])
                        ->default('Kas')
                        ->required(),

                    TextInput::make('dibayar_kepada')
                        ->label('Dibayar Kepada')
                        ->placeholder('Contoh: Karyawan, pemilik ruko, PLN'),

                    TextInput::make('deskripsi')
                        ->label('Deskripsi Beban')
                        ->placeholder('Contoh: Bayar listrik toko bulan Mei')
                        ->required()
                        ->columnSpan(2),

                    TextInput::make('nomor_bukti')
                        ->label('Nomor Bukti / Referensi')
                        ->placeholder('Contoh: nomor nota, invoice, atau transfer'),

                    FileUpload::make('bukti_pembayaran')
                        ->label('Upload Bukti')
                        ->directory('bukti-beban')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                        ->maxSize(2048),

                    Textarea::make('catatan')
                        ->label('Catatan')
                        ->rows(3)
                        ->columnSpan(2),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_transaksi')
                    ->label('No Transaksi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('chartOfAccount.kode_akun')
                    ->label('Kode Akun')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('chartOfAccount.nama_akun')
                    ->label('Akun Beban')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->searchable()
                    ->limit(35),

                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge(),

                TextColumn::make('dibayar_kepada')
                    ->label('Dibayar Kepada')
                    ->searchable()
                    ->placeholder('-'),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                SelectFilter::make('chart_of_account_id')
                    ->label('Akun Beban')
                    ->options(fn () => self::getExpenseAccountOptions()),

                SelectFilter::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->options([
                        'Kas' => 'Kas',
                        'Transfer Bank' => 'Transfer Bank',
                        'QRIS' => 'QRIS',
                        'E-Wallet' => 'E-Wallet',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenseTransactions::route('/'),
            'create' => Pages\CreateExpenseTransaction::route('/create'),
            'edit' => Pages\EditExpenseTransaction::route('/{record}/edit'),
        ];
    }

    private static function generateTransactionNumber(): string
    {
        $lastTransaction = ExpenseTransaction::orderBy('id', 'desc')->first();
        $newNumber = $lastTransaction ? (int) substr($lastTransaction->no_transaksi, 3) + 1 : 1;

        return 'BBN' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    private static function getExpenseAccountOptions(): array
    {
        return ChartOfAccount::query()
            ->where('kategori', 'Beban')
            ->where('kode_akun', 'like', '6%')
            ->orderBy('kode_akun')
            ->get()
            ->mapWithKeys(fn (ChartOfAccount $account): array => [
                $account->id => "{$account->kode_akun} - {$account->nama_akun}",
            ])
            ->all();
    }
}
