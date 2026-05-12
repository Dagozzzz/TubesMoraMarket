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
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;

use Filament\Forms\Get;
use Filament\Forms\Set;

use Illuminate\Support\HtmlString;

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

                /*
                |--------------------------------------------------------------------------
                | INFORMASI TRANSAKSI
                |--------------------------------------------------------------------------
                */

                Section::make('Informasi Transaksi')
                    ->icon('heroicon-o-document-text')
                    ->schema([

                        Grid::make(2)->schema([

                            TextInput::make('kode_pembelian')
                                ->label('Kode Pembelian')
                                ->default(function () {

                                    $last = TransaksiPembelian::latest('id')->first();

                                    $number = $last
                                        ? ((int) substr($last->kode_pembelian, 4)) + 1
                                        : 1;

                                    return 'PBL-' . str_pad($number, 4, '0', STR_PAD_LEFT);
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

                /*
                |--------------------------------------------------------------------------
                | DATA SUPPLIER
                |--------------------------------------------------------------------------
                */

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
                                                $supplier->kode_supplier . ' - ' . $supplier->nama_supplier
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
                                }),

                            TextInput::make('nama_supplier_display')
                                ->label('Nama Supplier')
                                ->disabled()
                                ->dehydrated(false),

                        ]),

                        Grid::make(2)->schema([

                            TextInput::make('no_hp_supplier_display')
                                ->label('No HP Supplier')
                                ->disabled()
                                ->dehydrated(false),

                            Select::make('metode_pembayaran')
                                ->label('Metode Pembayaran')
                                ->options([
                                    'tunai'    => 'Tunai',
                                    'transfer' => 'Transfer',
                                    'cek'      => 'Cek / Giro',
                                ])
                                ->default('tunai')
                                ->required()
                                ->native(false),

                        ]),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | DETAIL PRODUK
                |--------------------------------------------------------------------------
                */

                Section::make('Detail Produk Pembelian')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([

                        Repeater::make('detailTransaksi')
                            ->relationship()
                            ->label('')
                            ->defaultItems(1)
                            ->collapsible()
                            ->reorderableWithButtons()
                            ->live()
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

                                    /*
                                    |--------------------------------------------------------------------------
                                    | JUMLAH
                                    |--------------------------------------------------------------------------
                                    */

                                    TextInput::make('jumlah')
                                        ->label('Jumlah')
                                        ->default(1)
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, Get $get) {

                                            $jumlah = (float) ($get('jumlah') ?? 0);

                                            $hargaSatuan = (float) preg_replace(
                                                '/[^0-9]/',
                                                '',
                                                $get('harga_satuan') ?? 0
                                            );

                                            $subtotal = $jumlah * $hargaSatuan;

                                            $set('subtotal', $subtotal);

                                            $items = $get('../../detailTransaksi') ?? [];

                                            $total = collect($items)->sum(function ($item) {

                                                return (float) ($item['subtotal'] ?? 0);
                                            });

                                            $set('../../total_harga', $total);

                                            $jumlahBayar = (float) preg_replace(
                                                '/[^0-9]/',
                                                '',
                                                $get('../../jumlah_bayar') ?? 0
                                            );

                                            $set('../../kembalian', max(0, $jumlahBayar - $total));
                                        }),

                                    /*
                                    |--------------------------------------------------------------------------
                                    | HARGA SATUAN
                                    |--------------------------------------------------------------------------
                                    */
TextInput::make('harga_satuan')
    ->label('Harga Satuan (Rp)')
    ->prefix('Rp')
    ->required()
    ->default(0)
    ->numeric()
    ->live(onBlur: true)

    // format titik saat selesai input
    ->formatStateUsing(fn ($state) =>
        $state ? number_format((float) $state, 0, ',', '.') : null
    )

    // simpan tanpa titik
    ->dehydrateStateUsing(fn ($state) =>
        preg_replace('/[^0-9]/', '', $state)
    )

    ->afterStateUpdated(function (Set $set, Get $get) {

        $jumlah = (float) ($get('jumlah') ?? 0);

        $hargaSatuan = (float) preg_replace(
            '/[^0-9]/',
            '',
            $get('harga_satuan') ?? 0
        );

        $subtotal = $jumlah * $hargaSatuan;

        $set('subtotal', $subtotal);

        $items = $get('../../detailTransaksi') ?? [];

        $total = collect($items)->sum(function ($item) {

            return (float) ($item['subtotal'] ?? 0);
        });

        $set('../../total_harga', $total);

        $jumlahBayar = (float) preg_replace(
            '/[^0-9]/',
            '',
            $get('../../jumlah_bayar') ?? 0
        );

        $set('../../kembalian', max(0, $jumlahBayar - $total));
    }),

                                ]),

                                /*
                                |--------------------------------------------------------------------------
                                | SUBTOTAL
                                |--------------------------------------------------------------------------
                                */

                                Grid::make(2)->schema([

                                    Placeholder::make('subtotal_display')
                                        ->label('Subtotal (Rp)')
                                        ->live()
                                        ->content(function (Get $get): HtmlString {

                                            $subtotal = (float) ($get('subtotal') ?? 0);

                                            return new HtmlString(
                                                '<span class="font-semibold text-sm">
                                                    Rp ' . number_format($subtotal, 0, ',', '.') . '
                                                </span>'
                                            );
                                        }),

                                    Hidden::make('subtotal')
                                        ->default(0)
                                        ->dehydrated(true),

                                ]),

                            ])
                            ->addActionLabel('+ Tambah Produk'),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | RINGKASAN PEMBAYARAN
                |--------------------------------------------------------------------------
                */

                Section::make('Ringkasan Pembayaran')
                    ->icon('heroicon-o-banknotes')
                    ->schema([

                        Grid::make(3)->schema([

                            /*
                            |--------------------------------------------------------------------------
                            | TOTAL HARGA
                            |--------------------------------------------------------------------------
                            */

                            Placeholder::make('total_harga_display')
                                ->label('Total Harga (Rp)')
                                ->live()
                                ->content(function (Get $get): HtmlString {

                                    $total = (float) ($get('total_harga') ?? 0);

                                    return new HtmlString(
                                        '<span class="font-semibold text-sm">
                                            Rp ' . number_format($total, 0, ',', '.') . '
                                        </span>'
                                    );
                                }),

                            Hidden::make('total_harga')
                                ->default(0)
                                ->dehydrated(true),

                            /*
                            |--------------------------------------------------------------------------
                            | JUMLAH BAYAR
                            |--------------------------------------------------------------------------
                            */

                            TextInput::make('jumlah_bayar')
                                ->label('Jumlah Bayar (Rp)')
                                ->prefix('Rp')
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)

                                ->formatStateUsing(fn($state) =>
                                    $state
                                        ? number_format((float) $state, 0, ',', '.')
                                        : null
                                )

                                ->dehydrateStateUsing(fn($state) =>
                                    preg_replace('/[^0-9]/', '', $state)
                                )

                                ->afterStateUpdated(function (Set $set, Get $get) {

                                    $totalHarga = (float) ($get('total_harga') ?? 0);

                                    $jumlahBayar = (float) preg_replace(
                                        '/[^0-9]/',
                                        '',
                                        $get('jumlah_bayar') ?? 0
                                    );

                                    $set('kembalian', max(0, $jumlahBayar - $totalHarga));

                                    /*
                                    |--------------------------------------------------------------------------
                                    | STATUS PEMBAYARAN
                                    |--------------------------------------------------------------------------
                                    */

                                    if ($jumlahBayar <= 0) {

                                        $set('status_pembayaran', 'belum_lunas');

                                    } elseif ($jumlahBayar >= $totalHarga && $totalHarga > 0) {

                                        $set('status_pembayaran', 'lunas');

                                    } else {

                                        $set('status_pembayaran', 'cicilan');
                                    }
                                }),

                            /*
                            |--------------------------------------------------------------------------
                            | KEMBALIAN
                            |--------------------------------------------------------------------------
                            */

                            Placeholder::make('kembalian_display')
                                ->label('Kembalian (Rp)')
                                ->live()
                                ->content(function (Get $get): HtmlString {

                                    $jumlahBayar = (float) preg_replace(
                                        '/[^0-9]/',
                                        '',
                                        $get('jumlah_bayar') ?? 0
                                    );

                                    $totalHarga = (float) ($get('total_harga') ?? 0);

                                    $kembalian = max(0, $jumlahBayar - $totalHarga);

                                    return new HtmlString(
                                        '<span class="font-semibold text-sm">
                                            Rp ' . number_format($kembalian, 0, ',', '.') . '
                                        </span>'
                                    );
                                }),

                            Hidden::make('kembalian')
                                ->default(0)
                                ->dehydrated(true),

                        ]),

                        Grid::make(2)->schema([

                            /*
                            |--------------------------------------------------------------------------
                            | STATUS PEMBAYARAN
                            |--------------------------------------------------------------------------
                            */

                            Placeholder::make('status_pembayaran_display')
                                ->label('Status Pembayaran')
                                ->live()
                                ->content(function (Get $get): string {

                                    $status = $get('status_pembayaran');

                                    return match ($status) {

                                        'lunas'        => '✅ Lunas',
                                        'cicilan'      => '🔄 Cicilan',
                                        'belum_lunas'  => '❌ Belum Lunas',

                                        default => '-',
                                    };
                                }),

                            Hidden::make('status_pembayaran')
                                ->default('belum_lunas')
                                ->dehydrated(true),

                            /*
                            |--------------------------------------------------------------------------
                            | CATATAN
                            |--------------------------------------------------------------------------
                            */

                            Textarea::make('catatan')
                                ->label('Catatan')
                                ->rows(2),

                        ]),

                    ]),

            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('kode_pembelian')
                    ->label('Kode Pembelian')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('tanggal_pembelian')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('supplier.kode_supplier')
                    ->label('Kode Supplier'),

                TextColumn::make('supplier.nama_supplier')
                    ->label('Nama Supplier'),

                /*
                |--------------------------------------------------------------------------
                | JUMLAH ITEM
                |--------------------------------------------------------------------------
                */

               TextColumn::make('jumlah_item')
                ->label('Jml. Item')
                ->getStateUsing(function ($record) {

                    return $record->detailTransaksi->sum('jumlah');

                })
                ->badge()
                ->color('info'),

    
   

                /*
                |--------------------------------------------------------------------------
                | TOTAL HARGA
                |--------------------------------------------------------------------------
                */

                TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | METODE PEMBAYARAN
                |--------------------------------------------------------------------------
                */

                TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {

                        'tunai'    => 'success',
                        'transfer' => 'info',
                        'cek'      => 'warning',

                        default => 'gray',
                    }),

                /*
                |--------------------------------------------------------------------------
                | STATUS PEMBAYARAN
                |--------------------------------------------------------------------------
                */

                TextColumn::make('status_pembayaran')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {

                        'lunas'        => 'success',
                        'cicilan'      => 'warning',
                        'belum_lunas'  => 'danger',

                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {

                        'lunas'        => 'Lunas',
                        'cicilan'      => 'Cicilan',
                        'belum_lunas'  => 'Belum Lunas',

                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i'),

            ])

            ->defaultSort('created_at', 'desc')

            ->filters([

                SelectFilter::make('status_pembayaran')
                    ->options([
                        'lunas'        => 'Lunas',
                        'cicilan'      => 'Cicilan',
                        'belum_lunas'  => 'Belum Lunas',
                    ]),

                SelectFilter::make('metode_pembayaran')
                    ->options([
                        'tunai'    => 'Tunai',
                        'transfer' => 'Transfer',
                        'cek'      => 'Cek',
                    ]),

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