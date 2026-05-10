<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiPembelianResource\Pages;
use App\Models\TransaksiPembelian;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Hidden;

use Filament\Forms\Get;
use Filament\Forms\Set;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class TransaksiPembelianResource extends Resource
{
    protected static ?string $model = TransaksiPembelian::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Transaksi Pembelian';
    protected static ?string $pluralModelLabel = 'Transaksi Pembelian';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Informasi Transaksi')
                    ->icon('heroicon-o-document-text')
                    ->schema([

                        Grid::make(2)->schema([

                            TextInput::make('kode_pembelian')
                                ->label('Kode Pembelian')
                                ->default(function () {

                                    $last = TransaksiPembelian::orderBy('id', 'desc')->first();

                                    $newNumber = $last
                                        ? (int) substr($last->kode_pembelian, 4) + 1
                                        : 1;

                                    return 'PBL-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
                                })
                                ->disabled()
                                ->dehydrated(true),

                            DatePicker::make('tanggal_pembelian')
                                ->label('Tanggal Pembelian')
                                ->default(now())
                                ->required()
                                ->native(false)
                                ->displayFormat('d M Y'),
                        ]),
                    ]),

                Section::make('Data Supplier')
                    ->icon('heroicon-o-truck')
                    ->schema([

                        Grid::make(2)->schema([

                            Select::make('id_supplier')
                                ->label('ID / Kode Supplier')
                                ->options(function () {

                                    return Supplier::all()->mapWithKeys(function ($supplier) {

                                        return [
                                            $supplier->id =>
                                                $supplier->kode_supplier .
                                                ' — ' .
                                                $supplier->nama_supplier
                                        ];
                                    });
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set, $state) {

                                    if ($state) {

                                        $supplier = Supplier::find($state);

                                        if ($supplier) {

                                            $set('nama_supplier_display', $supplier->nama_supplier);

                                            $set('no_hp_supplier_display', $supplier->no_handphone);
                                        }
                                    } else {

                                        $set('nama_supplier_display', null);

                                        $set('no_hp_supplier_display', null);
                                    }
                                })
                                ->placeholder('Pilih kode supplier...'),

                            TextInput::make('nama_supplier_display')
                                ->label('Nama Supplier')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('Otomatis terisi setelah memilih supplier'),
                        ]),

                        Grid::make(2)->schema([

                            TextInput::make('no_hp_supplier_display')
                                ->label('No. Handphone Supplier')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('Otomatis terisi setelah memilih supplier'),

                            Select::make('metode_pembayaran')
                                ->label('Metode Pembayaran')
                                ->options([
                                    'tunai'    => '💵 Tunai',
                                    'transfer' => '🏦 Transfer Bank',
                                    
                                ])
                                ->default('tunai')
                                ->required()
                                ->native(false),
                        ]),
                    ]),

                Section::make('Detail Produk Pembelian')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([

                        Repeater::make('detailTransaksi')
                            ->label('')
                            ->relationship()
                            ->schema([

                                Grid::make(5)->schema([

                                    TextInput::make('nama_produk')
                                        ->label('Nama Produk')
                                        ->required()
                                        ->columnSpan(2),

                                    Select::make('satuan')
                                        ->label('Satuan')
                                        ->options([
                                            'pcs'    => 'Pcs',
                                            'lusin'  => 'Lusin',
                                            'kodi'   => 'Kodi',
                                            'karton' => 'Karton',
                                            'kg'     => 'Kg',
                                            'liter'  => 'Liter',
                                            'botol'  => 'Botol',
                                            'pak'    => 'Pak',
                                        ])
                                        ->default('pcs')
                                        ->required()
                                        ->native(false),

                                    TextInput::make('jumlah')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1)
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, Get $get) {

                                            $jumlah = (float) ($get('jumlah') ?? 0);

                                            $hargaSatuan = (float) ($get('harga_satuan') ?? 0);

                                            $set('subtotal', $jumlah * $hargaSatuan);
                                        }),

                                    TextInput::make('harga_satuan')
                                        ->label('Harga Satuan (Rp)')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->minValue(0)
                                        ->default(0)
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, Get $get) {

                                            $jumlah = (float) ($get('jumlah') ?? 0);

                                            $hargaSatuan = (float) ($get('harga_satuan') ?? 0);

                                            $set('subtotal', $jumlah * $hargaSatuan);
                                        }),
                                ]),

                                Grid::make(3)->schema([

                                    TextInput::make('subtotal')
                                        ->label('Subtotal (Rp)')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->disabled()
                                        ->dehydrated(true)
                                        ->default(0),
                                ]),
                            ])
                            ->addActionLabel('+ Tambah Produk')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->defaultItems(1)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {

                                $items = $get('detailTransaksi') ?? [];

                                $total = collect($items)->sum(
                                    fn($item) => (float) ($item['subtotal'] ?? 0)
                                );

                                $set('total_harga', $total);

                                $jumlahBayar = (float) ($get('jumlah_bayar') ?? 0);

                                $kembalian = max(0, $jumlahBayar - $total);

                                $set('kembalian', $kembalian);

                                // otomatis set lunas
                                $set('status_pembayaran', 'Lunas');
                            }),
                    ]),

                Section::make('Ringkasan Pembayaran')
                    ->icon('heroicon-o-banknotes')
                    ->schema([

                        Grid::make(3)->schema([

                            TextInput::make('total_harga')
                                ->label('Total Harga (Rp)')
                                ->prefix('Rp')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(true)
                                ->default(0),

                            TextInput::make('jumlah_bayar')
                                ->label('Jumlah Bayar (Rp)')
                                ->prefix('Rp')
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, Get $get) {

                                    $totalHarga = (float) ($get('total_harga') ?? 0);

                                    $jumlahBayar = (float) ($get('jumlah_bayar') ?? 0);

                                    $kembalian = max(0, $jumlahBayar - $totalHarga);

                                    $set('kembalian', $kembalian);

                                    // otomatis set lunas
                                    $set('status_pembayaran', 'Lunas');
                                }),

                            TextInput::make('kembalian')
                                ->label('Kembalian (Rp)')
                                ->prefix('Rp')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(true)
                                ->default(0),
                        ]),

                        Grid::make(2)->schema([

                            // disimpan ke database
                            Hidden::make('status_pembayaran')
                                ->default('Lunas'),

                            // hanya tampilan
                            TextInput::make('status_lunas')
                                ->label('Status Pembayaran')
                                ->default('Lunas')
                                ->disabled()
                                ->dehydrated(false),

                            Textarea::make('catatan')
                                ->label('Catatan')
                                ->placeholder('Catatan tambahan (opsional)...')
                                ->rows(2),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('kode_pembelian')
                    ->label('Kode Pembelian')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('tanggal_pembelian')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('supplier.kode_supplier')
                    ->label('Kode Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.nama_supplier')
                    ->label('Nama Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('detailTransaksi_count')
                    ->label('Jml. Item')
                    ->counts('detailTransaksi')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'tunai'    => 'success',
                        'transfer' => 'info',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'tunai'    => 'Tunai',
                        'transfer' => 'Transfer',
                      
                        default    => $state,
                    }),

                TextColumn::make('status_pembayaran')
                    ->label('Status')
                    ->badge()
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('created_at', 'desc')

            ->filters([

                SelectFilter::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->options([
                        'tunai'    => 'Tunai',
                        'transfer' => 'Transfer',
                      
                    ]),

                SelectFilter::make('id_supplier')
                    ->label('Supplier')
                    ->relationship('supplier', 'nama_supplier')
                    ->searchable()
                    ->preload(),
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTransaksiPembelians::route('/'),
            'create' => Pages\CreateTransaksiPembelian::route('/create'),
            'edit'   => Pages\EditTransaksiPembelian::route('/{record}/edit'),
        ];
    }
}