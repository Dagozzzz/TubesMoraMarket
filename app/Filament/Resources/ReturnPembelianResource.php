<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnPembelianResource\Pages;
use App\Models\KategoriSupplier;
use App\Models\ReturnPembelian;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReturnPembelianResource extends Resource
{
    protected static ?string $model           = ReturnPembelian::class;
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationLabel = 'Return Pembelian';
    protected static ?string $pluralLabel     = 'Data Return Pembelian';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int    $navigationSort  = 10;

    /* ================================================================== */
    /*  FORM                                                                */
    /* ================================================================== */
    public static function form(Form $form): Form
    {
        return $form->schema([

            /* ── Informasi Header ──────────────────────────────────────── */
            Section::make('Informasi Return')
                ->icon('heroicon-o-document-text')
                ->schema([

                    Grid::make(3)->schema([

                        // No. Return — auto generate, read only
                        TextInput::make('id_return')
                            ->label('No. Return')
                            ->disabled()
                            ->dehydrated(true)
                            ->columnSpan(1)
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record) {
                                    $component->state($record->id_return);
                                    return;
                                }
                                $last   = ReturnPembelian::orderBy('id_return', 'desc')->first();
                                $number = $last ? (int) substr($last->id_return, 3) + 1 : 1;
                                $component->state('RTN' . str_pad($number, 5, '0', STR_PAD_LEFT));
                            }),

                        DatePicker::make('tanggal_return')
                            ->label('Tanggal Return')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft'     => 'Draft',
                                'diajukan'  => 'Diajukan',
                                'disetujui' => 'Disetujui',
                                'ditolak'   => 'Ditolak',
                            ])
                            ->default('draft')
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                    ]),

                    Grid::make(2)->schema([

                        // Pilih Kategori → filter Supplier di bawah
                        Select::make('id_kategori_supplier')
                            ->label('Kategori Supplier')
                            ->options(KategoriSupplier::pluck('nama_kategori', 'id_kategori'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('id_supplier', null))
                            ->columnSpan(1),

                        // Supplier difilter berdasarkan kategori yang dipilih
                        // Foreign key di suppliers = id_kategori_supplier
                        Select::make('id_supplier')
                            ->label('Supplier')
                            ->options(function (Get $get) {
                                $kategoriId = $get('id_kategori_supplier');
                                $query      = Supplier::query();
                                if ($kategoriId) {
                                    $query->where('id_kategori_supplier', $kategoriId);
                                }
                                return $query->pluck('nama_supplier', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                    ]),

                    Textarea::make('alasan_return')
                        ->label('Alasan Return')
                        ->required()
                        ->rows(3)
                        ->maxLength(1000)
                        ->columnSpanFull(),

                    Textarea::make('catatan')
                        ->label('Catatan Tambahan')
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),

            /* ── Detail Item Return ────────────────────────────────────── */
            Section::make('Detail Item Yang Dikembalikan')
                ->icon('heroicon-o-shopping-cart')
                ->schema([

                    Repeater::make('detailReturn')
                        ->label('')
                        ->relationship()
                        ->schema([
                            Grid::make(2)->schema([

                                TextInput::make('nama_produk')
                                    ->label('Nama Produk')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                Textarea::make('deskripsi_produk')
                                    ->label('Deskripsi Produk')
                                    ->rows(2)
                                    ->columnSpan(2),

                                Select::make('kondisi')
                                    ->label('Kondisi Barang')
                                    ->options([
                                        'rusak'          => 'Rusak',
                                        'cacat_produksi' => 'Cacat Produksi',
                                        'salah_kirim'    => 'Salah Kirim',
                                        'kadaluarsa'     => 'Kadaluarsa',
                                        'lainnya'        => 'Lainnya',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->default('rusak')
                                    ->columnSpan(1),

                                TextInput::make('jumlah')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $set('subtotal_display', number_format(
                                            (int) ($get('jumlah') ?? 0) * (float) ($get('harga_satuan') ?? 0),
                                            0, ',', '.'
                                        ));
                                    })
                                    ->columnSpan(1),

                                TextInput::make('harga_satuan')
                                    ->label('Harga Satuan (Rp)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $set('subtotal_display', number_format(
                                            (int) ($get('jumlah') ?? 0) * (float) ($get('harga_satuan') ?? 0),
                                            0, ',', '.'
                                        ));
                                    })
                                    ->columnSpan(1),

                                TextInput::make('subtotal_display')
                                    ->label('Subtotal (Rp)')
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(1),
                            ]),
                        ])
                        ->addActionLabel('+ Tambah Item')
                        ->reorderableWithButtons()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['nama_produk'] ?? 'Item Baru')
                        ->columnSpanFull(),

                    // Total nilai return (tampil setelah record disimpan)
                    Placeholder::make('total_nilai_return')
                        ->label('Total Nilai Return')
                        ->content(function ($record): string {
                            if (! $record) return 'Rp 0';
                            return 'Rp ' . number_format($record->total_nilai_return, 0, ',', '.');
                        }),
                ]),
        ]);
    }

    /* ================================================================== */
    /*  TABLE                                                               */
    /* ================================================================== */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_return')
                    ->label('No. Return')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('tanggal_return')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('kategoriSupplier.nama_kategori')
                    ->label('Kategori Supplier')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'     => 'gray',
                        'diajukan'  => 'warning',
                        'disetujui' => 'success',
                        'ditolak'   => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'     => 'Draft',
                        'diajukan'  => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'ditolak'   => 'Ditolak',
                        default     => $state,
                    }),

                TextColumn::make('total_nilai_return')
                    ->label('Total Nilai (Rp)')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('detailReturn_count')
                    ->counts('detailReturn')
                    ->label('Jml Item')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'     => 'Draft',
                        'diajukan'  => 'Diajukan',
                        'disetujui' => 'Disetujui',
                        'ditolak'   => 'Ditolak',
                    ]),

                SelectFilter::make('id_kategori_supplier')
                    ->label('Kategori Supplier')
                    ->relationship('kategoriSupplier', 'nama_kategori'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->status === 'draft'),

                Tables\Actions\Action::make('ajukan')
                    ->label('Ajukan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Ajukan Return?')
                    ->modalDescription('Return akan diajukan ke admin untuk diproses.')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(fn ($record) => $record->update(['status' => 'diajukan'])),

                Tables\Actions\Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Return?')
                    ->modalDescription('Return ini akan disetujui dan diproses.')
                    ->visible(fn ($record) => $record->status === 'diajukan')
                    ->action(fn ($record) => $record->update(['status' => 'disetujui'])),

                Tables\Actions\Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Return?')
                    ->modalDescription('Return ini akan ditolak.')
                    ->visible(fn ($record) => $record->status === 'diajukan')
                    ->action(fn ($record) => $record->update(['status' => 'ditolak'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /* ================================================================== */
    /*  PAGES — index, create, edit                                         */
    /* ================================================================== */
    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReturnPembelian::route('/'),
            'create' => Pages\CreateReturnPembelian::route('/create'),
            'edit'   => Pages\EditReturnPembelian::route('/{record}/edit'),
        ];
    }
}