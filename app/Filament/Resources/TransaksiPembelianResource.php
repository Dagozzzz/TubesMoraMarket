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

    // =========================================================
    // ICON & LABEL NAVIGASI SIDEBAR
    // Ubah icon  → ganti 'heroicon-o-shopping-cart'
    // Ubah nama menu → ganti string di navigationLabel
    // Ubah grup menu → ganti string di navigationGroup
    // =========================================================
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Transaksi Pembelian';
    protected static ?string $pluralModelLabel = 'Transaksi Pembelian';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // =====================================================
                // SECTION 1 — INFORMASI TRANSAKSI
                // Berisi: kode pembelian (auto) & tanggal pembelian
                // =====================================================
                Section::make('Informasi Transaksi')
                    ->icon('heroicon-o-document-text')
                    ->schema([

                        Grid::make(2)->schema([

                            // --------------------------------------------------
                            // KODE PEMBELIAN — auto-generate, tidak bisa diedit
                            // Format: PBL-0001, PBL-0002, dst.
                            // Ubah prefix → ganti 'PBL-'
                            // Ubah panjang angka → ganti str_pad(..., 4, ...)
                            // --------------------------------------------------
                            TextInput::make('kode_pembelian')
                                ->label('Kode Pembelian')
                                ->default(function () {

                                    $last = TransaksiPembelian::latest('id')->first();

                                    $number = $last
                                        ? ((int) substr($last->kode_pembelian, 4)) + 1
                                        : 1;

                                    // Ubah 'PBL-' untuk ganti prefix kode
                                    // Ubah angka 4 di str_pad untuk ganti panjang digit
                                    return 'PBL-' . str_pad($number, 4, '0', STR_PAD_LEFT);
                                })
                                ->disabled()
                                ->dehydrated(true), // dehydrated(true) = tetap disimpan walau disabled

                            // --------------------------------------------------
                            // TANGGAL PEMBELIAN — default hari ini
                            // Ubah format tampilan → ganti displayFormat('d M Y')
                            // --------------------------------------------------
                            DatePicker::make('tanggal_pembelian')
                                ->label('Tanggal Pembelian')
                                ->default(now())
                                ->required()
                                ->native(false)
                                ->displayFormat('d M Y'),

                        ]),

                    ]),

                // =====================================================
                // SECTION 2 — DATA SUPPLIER
                // Berisi: dropdown supplier, nama, no HP, metode bayar
                // =====================================================
                Section::make('Data Supplier')
                    ->icon('heroicon-o-truck')
                    ->schema([

                        Grid::make(2)->schema([

                            // --------------------------------------------------
                            // SELECT SUPPLIER
                            // Saat dipilih → otomatis isi nama & no HP (live)
                            // Data diambil dari tabel suppliers
                            // --------------------------------------------------
                            Select::make('id_supplier')
                                ->label('ID / Kode Supplier')
                                ->options(function () {

                                    return Supplier::all()->mapWithKeys(function ($supplier) {

                                        // Format dropdown: "SUP-001 - Nama Supplier"
                                        return [
                                            $supplier->id =>
                                                $supplier->kode_supplier . ' - ' . $supplier->nama_supplier
                                        ];
                                    });
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live() // live() = trigger update saat nilai berubah
                                ->afterStateUpdated(function (Set $set, $state) {

                                    // Saat supplier dipilih → isi field nama & no HP otomatis
                                    if ($state) {

                                        $supplier = Supplier::find($state);

                                        if ($supplier) {

                                            $set('nama_supplier_display', $supplier->nama_supplier);

                                            $set('no_hp_supplier_display', $supplier->no_handphone);
                                        }

                                    } else {

                                        // Saat supplier dikosongkan → reset nama & no HP
                                        $set('nama_supplier_display', null);

                                        $set('no_hp_supplier_display', null);
                                    }
                                }),

                            // --------------------------------------------------
                            // NAMA SUPPLIER — hanya tampilan, tidak disimpan sendiri
                            // dehydrated(false) = tidak dikirim ke database
                            // --------------------------------------------------
                            TextInput::make('nama_supplier_display')
                                ->label('Nama Supplier')
                                ->disabled()
                                ->dehydrated(false),

                        ]),

                        Grid::make(2)->schema([

                            // Sama seperti nama, hanya tampilan
                            TextInput::make('no_hp_supplier_display')
                                ->label('No HP Supplier')
                                ->disabled()
                                ->dehydrated(false),

                            // --------------------------------------------------
                            // METODE PEMBAYARAN
                            // Tambah opsi baru → tambahkan di dalam ->options([...])
                            // --------------------------------------------------
                            Select::make('metode_pembayaran')
                                ->label('Metode Pembayaran')
                                ->options([
                                    'tunai'    => 'Tunai',
                                    'transfer' => 'Transfer',

                                    // Tambah metode baru di sini, contoh:
                                    // 'qris' => 'QRIS',
                                ])
                                ->default('tunai')
                                ->required()
                                ->native(false),

                        ]),

                    ]),

                // =====================================================
                // SECTION 3 — DETAIL PRODUK (REPEATER)
                // Bisa tambah baris produk dinamis
                // Setiap baris: nama produk, satuan, jumlah, harga, subtotal
                // =====================================================
                Section::make('Detail Produk Pembelian')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([

                        Repeater::make('detailTransaksi')
                            ->relationship() // relasi ke model DetailTransaksiPembelian
                            ->label('')
                            ->defaultItems(1) // jumlah baris awal saat form dibuka
                            ->collapsible()
                            ->reorderableWithButtons()
                            ->live()
                            ->schema([

                                Grid::make(5)->schema([

                                    // Nama produk, span 2 kolom dari 5
                                    TextInput::make('nama_produk')
                                        ->label('Nama Produk')
                                        ->required()
                                        ->columnSpan(2),

                                    // ------------------------------------------
                                    // SATUAN
                                    // Tambah satuan baru → tambahkan di ->options([...])
                                    // ------------------------------------------
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
                                            // Tambah satuan baru di sini
                                        ])
                                        ->default('pcs')
                                        ->required()
                                        ->native(false),

                                    // ------------------------------------------
                                    // JUMLAH
                                    // Saat berubah → hitung ulang subtotal & total
                                    // live(onBlur: true) = update saat keluar dari field
                                    // ------------------------------------------
                                    TextInput::make('jumlah')
                                        ->label('Jumlah')
                                        ->default(1)
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, Get $get) {

                                            $jumlah = (float) ($get('jumlah') ?? 0);

                                            // Bersihkan format titik ribuan sebelum dihitung
                                            $hargaSatuan = (float) preg_replace(
                                                '/[^0-9]/',
                                                '',
                                                $get('harga_satuan') ?? 0
                                            );

                                            // Hitung subtotal baris ini
                                            $subtotal = $jumlah * $hargaSatuan;
                                            $set('subtotal', $subtotal);

                                            // Ambil semua baris repeater, lalu sum subtotal-nya
                                            // '../../detailTransaksi' = naik 2 level ke parent form
                                            $items = $get('../../detailTransaksi') ?? [];

                                            $total = collect($items)->sum(function ($item) {
                                                return (float) ($item['subtotal'] ?? 0);
                                            });

                                            $set('../../total_harga', $total);

                                            // Reset jumlah_bayar & kembalian saat total berubah
                                            // agar user harus input ulang nominal bayar
                                            $set('../../jumlah_bayar', null);
                                            $set('../../kembalian', 0);
                                            $set('../../status_pembayaran', 'lunas');
                                        }),

                                    // ------------------------------------------
                                    // HARGA SATUAN
                                    // Format tampilan: Rp 1.000.000 (titik ribuan)
                                    // Disimpan ke DB: 1000000 (tanpa titik)
                                    // Logika hitung sama seperti jumlah di atas
                                    // ------------------------------------------
                                    TextInput::make('harga_satuan')
                                        ->label('Harga Satuan (Rp)')
                                        ->prefix('Rp')
                                        ->required()
                                        ->default(0)
                                        ->numeric()
                                        ->live(onBlur: true)

                                        // Format saat ditampilkan: tambah titik ribuan
                                        ->formatStateUsing(fn ($state) =>
                                            $state ? number_format((float) $state, 0, ',', '.') : null
                                        )

                                        // Sebelum disimpan: hapus titik ribuan
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

                                            // Reset jumlah_bayar & kembalian saat total berubah
                                            $set('../../jumlah_bayar', null);
                                            $set('../../kembalian', 0);
                                            $set('../../status_pembayaran', 'lunas');
                                        }),

                                ]),

                                // ------------------------------------------
                                // SUBTOTAL PER BARIS
                                // Placeholder = hanya tampilan (tidak bisa diedit)
                                // Hidden = yang benar-benar disimpan ke DB
                                // ------------------------------------------
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

                                    // Nilai subtotal yang sesungguhnya disimpan di sini (tersembunyi)
                                    Hidden::make('subtotal')
                                        ->default(0)
                                        ->dehydrated(true),

                                ]),

                            ])
                            ->addActionLabel('+ Tambah Produk'), // Ubah teks tombol tambah baris di sini

                    ]),

                // =====================================================
                // SECTION 4 — RINGKASAN PEMBAYARAN
                // Berisi: total harga, jumlah bayar, kembalian,
                //         status pembayaran (selalu lunas), catatan
                //
                // LOGIKA BARU:
                // - Supplier WAJIB melunasi penuh saat transaksi
                // - Jumlah bayar tidak boleh kurang dari total harga
                // - Tidak ada opsi cicilan / belum lunas
                // =====================================================
                Section::make('Ringkasan Pembayaran')
                    ->icon('heroicon-o-banknotes')
                    ->schema([

                        Grid::make(3)->schema([

                            // ------------------------------------------
                            // TOTAL HARGA — hasil sum semua subtotal
                            // Hanya tampilan (Placeholder), nilai asli di Hidden
                            // ------------------------------------------
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

                            // ------------------------------------------
                            // JUMLAH BAYAR — input dari user
                            // WAJIB >= total_harga (tidak boleh kurang)
                            // Kembalian dihitung jika bayar lebih dari total
                            // ------------------------------------------
                            TextInput::make('jumlah_bayar')
                                ->label('Jumlah Bayar (Rp)')
                                ->prefix('Rp')
                                ->default(0)
                                ->required()
                                ->live(onBlur: true)

                                // ------------------------------------------
                                // VALIDASI: jumlah_bayar harus >= total_harga
                                // Jika kurang → form tidak bisa disimpan
                                // ------------------------------------------
                                ->rules([
                                    fn (Get $get): \Closure => function (
                                        string $attribute,
                                        $value,
                                        \Closure $fail
                                    ) use ($get) {
                                        $jumlahBayar = (float) preg_replace('/[^0-9]/', '', $value ?? 0);
                                        $totalHarga  = (float) ($get('total_harga') ?? 0);

                                        if ($totalHarga > 0 && $jumlahBayar < $totalHarga) {
                                            $fail(
                                                'Jumlah bayar harus minimal Rp '
                                                . number_format($totalHarga, 0, ',', '.')
                                                . '. Pembayaran harus dilunasi penuh.'
                                            );
                                        }
                                    },
                                ])

                                ->formatStateUsing(fn ($state) =>
                                    $state ? number_format((float) $state, 0, ',', '.') : null
                                )

                                ->dehydrateStateUsing(fn ($state) =>
                                    preg_replace('/[^0-9]/', '', $state)
                                )

                                ->afterStateUpdated(function (Set $set, Get $get) {

                                    $totalHarga = (float) ($get('total_harga') ?? 0);

                                    $jumlahBayar = (float) preg_replace(
                                        '/[^0-9]/',
                                        '',
                                        $get('jumlah_bayar') ?? 0
                                    );

                                    // Hitung kembalian (boleh lebih, tidak boleh minus)
                                    $kembalian = max(0, $jumlahBayar - $totalHarga);
                                    $set('kembalian', $kembalian);

                                    // Status selalu lunas — tidak ada cicilan / belum lunas
                                    $set('status_pembayaran', 'lunas');
                                }),

                            // ------------------------------------------
                            // KEMBALIAN — hanya tampilan
                            // Dihitung otomatis: jumlah_bayar - total_harga
                            // Jika bayar pas → kembalian Rp 0
                            // ------------------------------------------
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

                            // ------------------------------------------
                            // STATUS PEMBAYARAN — selalu LUNAS
                            // Tidak ada opsi cicilan / belum lunas
                            // ------------------------------------------
                            Placeholder::make('status_pembayaran_display')
                                ->label('Status Pembayaran')
                                ->live()
                                ->content(function (Get $get): string {
                                    // Status selalu lunas, tidak ada kondisi lain
                                    return '✅ Lunas';
                                }),

                            Hidden::make('status_pembayaran')
                                ->default('lunas') // default lunas
                                ->dehydrated(true),

                            // Catatan bebas, opsional
                            Textarea::make('catatan')
                                ->label('Catatan')
                                ->rows(2),

                        ]),

                    ]),

            ]);
    }

    // =========================================================
    // TABLE — halaman index/list semua transaksi
    // =========================================================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Kolom kode, bisa dicari & diurutkan
                TextColumn::make('kode_pembelian')
                    ->label('Kode Pembelian')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // Ubah format tanggal → ganti 'd M Y'
                TextColumn::make('tanggal_pembelian')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                // Data dari relasi supplier
                TextColumn::make('supplier.kode_supplier')
                    ->label('Kode Supplier'),

                TextColumn::make('supplier.nama_supplier')
                    ->label('Nama Supplier'),

                // --------------------------------------------------
                // JUMLAH ITEM — dihitung dari relasi detailTransaksi
                // Bukan kolom di DB, dihitung manual via getStateUsing
                // --------------------------------------------------
                TextColumn::make('jumlah_item')
                    ->label('Jml. Item')
                    ->getStateUsing(function ($record) {
                        // Sum kolom 'jumlah' dari semua baris detail transaksi ini
                        return $record->detailTransaksi->sum('jumlah');
                    })
                    ->badge()
                    ->color('info'),

                // Format otomatis ke Rupiah (IDR)
                TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                // --------------------------------------------------
                // METODE PEMBAYARAN — badge warna
                // Tambah warna baru → tambahkan di match() di bawah
                // --------------------------------------------------
                TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tunai'    => 'success',
                        'transfer' => 'info',
                        'cek'      => 'warning',
                        default    => 'gray',
                    }),

                // --------------------------------------------------
                // STATUS PEMBAYARAN — selalu lunas, badge hijau
                // --------------------------------------------------
                TextColumn::make('status_pembayaran')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lunas' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lunas' => 'Lunas',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i'),

            ])

            ->defaultSort('created_at', 'desc') // Urutan default: terbaru di atas

            // =====================================================
            // FILTER — di pojok kanan atas tabel
            // Filter status dihapus karena selalu lunas
            // =====================================================
            ->filters([

                SelectFilter::make('metode_pembayaran')
                    ->options([
                        'tunai'    => 'Tunai',
                        'transfer' => 'Transfer',
                        'cek'      => 'Cek',
                    ]),

            ])

            // Tombol aksi per baris: View, Edit, Delete
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])

            // Tombol aksi untuk baris yang dicentang (bulk)
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

    // =========================================================
    // PAGES — mapping URL ke class halaman
    // index = list, create = tambah baru, edit = ubah data
    // =========================================================
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTransaksiPembelians::route('/'),
            'create' => Pages\CreateTransaksiPembelian::route('/create'),
            'edit'   => Pages\EditTransaksiPembelian::route('/{record}/edit'),
        ];
    }
}
