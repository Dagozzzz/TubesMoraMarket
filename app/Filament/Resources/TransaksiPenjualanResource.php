<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiPenjualanResource\Pages;
use App\Models\Customer;
use App\Models\TransaksiPenjualan;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransaksiPenjualanResource extends Resource
{
    protected static ?string $model = TransaksiPenjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Transaksi Penjualan';
    protected static ?string $pluralModelLabel = 'Transaksi Penjualan';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Penjualan')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('kode_penjualan')
                            ->label('Kode Penjualan')
                            ->default(fn () => TransaksiPenjualan::generateKodePenjualan())
                            ->disabled()
                            ->dehydrated(true)
                            ->required(),

                        DateTimePicker::make('tanggal_penjualan')
                            ->label('Tanggal Penjualan')
                            ->default(now())
                            ->seconds(false)
                            ->required(),

                        Select::make('status_pembayaran')
                            ->label('Status Pembayaran')
                            ->options([
                                'pending' => 'Pending',
                                'lunas' => 'Lunas',
                                'gagal' => 'Gagal',
                                'expired' => 'Expired',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('id_customer')
                            ->label('Customer')
                            ->options(fn () => Customer::query()
                                ->orderBy('nama_customer')
                                ->pluck('nama_customer', 'id_customer'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('metode_pembayaran')
                            ->label('Metode Pembayaran')
                            ->options([
                                'midtrans' => 'Midtrans',
                                'tunai' => 'Tunai',
                                'transfer' => 'Transfer',
                            ])
                            ->default('midtrans')
                            ->required()
                            ->native(false),
                    ]),

                    Grid::make(3)->schema([
                        TextInput::make('midtrans_order_id')
                            ->label('Order ID Midtrans')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('midtrans_snap_token')
                            ->label('Snap Token')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('total_harga')
                            ->label('Total Harga')
                            ->prefix('Rp')
                            ->numeric()
                            ->required()
                            ->default(0),
                    ]),

                    Textarea::make('catatan')
                        ->label('Catatan')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Section::make('Detail Barang Penjualan')
                ->icon('heroicon-o-shopping-bag')
                ->schema([
                    Repeater::make('detailTransaksi')
                        ->relationship()
                        ->label('')
                        ->schema([
                            Grid::make(6)->schema([
                                TextInput::make('kode_barang')
                                    ->label('Kode')
                                    ->required(),

                                TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('satuan')
                                    ->label('Satuan')
                                    ->default('pcs'),

                                TextInput::make('jumlah')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->default(1),

                                TextInput::make('harga_satuan')
                                    ->label('Harga')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->required(),
                            ]),

                            Grid::make(2)->schema([
                                TextInput::make('kategori')
                                    ->label('Kategori'),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->required()
                                    ->default(0),
                            ]),
                        ])
                        ->columns(1)
                        ->collapsible()
                        ->reorderableWithButtons()
                        ->addActionLabel('+ Tambah Barang'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No Transaksi Penjualan')
            ->emptyStateDescription('Belum ada data yang cocok. Jika data seeder sudah dijalankan, reset pencarian atau filter tabel.')
            ->columns([
                TextColumn::make('kode_penjualan')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('tanggal_penjualan')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('customer.nama_customer')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kasir.name')
                    ->label('Kasir')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('jumlah_item')
                    ->label('Jml. Item')
                    ->getStateUsing(fn ($record) => $record->detailTransaksi->sum('jumlah'))
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_harga')
                    ->label('Total')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'midtrans' => 'info',
                        'tunai' => 'success',
                        'transfer' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('status_pembayaran')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lunas' => 'success',
                        'pending' => 'warning',
                        'gagal' => 'danger',
                        'expired' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lunas' => 'Lunas',
                        'pending' => 'Pending',
                        'gagal' => 'Gagal',
                        'expired' => 'Expired',
                        default => $state,
                    }),

                TextColumn::make('midtrans_order_id')
                    ->label('Order ID')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_penjualan', 'desc')
            ->filters([
                SelectFilter::make('status_pembayaran')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'lunas' => 'Lunas',
                        'gagal' => 'Gagal',
                        'expired' => 'Expired',
                    ]),

                SelectFilter::make('id_customer')
                    ->label('Customer')
                    ->relationship('customer', 'nama_customer'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'kasir', 'detailTransaksi']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksiPenjualans::route('/'),
            'create' => Pages\CreateTransaksiPenjualan::route('/create'),
            'edit' => Pages\EditTransaksiPenjualan::route('/{record}/edit'),
        ];
    }
}
