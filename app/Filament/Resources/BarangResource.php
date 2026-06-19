<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Widgets\BarangChartWidget;
use App\Filament\Widgets\BarangMaterialChart;
use App\Models\Barang;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Data Barang';

    protected static ?string $pluralLabel = 'Data Barang';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('kode_barang')
                        ->label('Kode Barang')
                        ->default(fn (): string => Barang::generateKodeBarang())
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('nama_barang')
                        ->label('Nama Barang')
                        ->required()
                        ->maxLength(255),

                    Select::make('kategori')
                        ->label('Kategori')
                        ->options([
                            'Elektronik' => 'Elektronik',
                            'Pakaian' => 'Pakaian',
                            'Makanan & Minuman' => 'Makanan & Minuman',
                            'Perabot Rumah' => 'Perabot Rumah',
                            'Olahraga' => 'Olahraga',
                            'Lainnya' => 'Lainnya',
                        ])
                        ->required(),

                    Select::make('satuan')
                        ->label('Satuan')
                        ->options([
                            'pcs' => 'pcs',
                            'box' => 'box',
                            'pack' => 'pack',
                            'lusin' => 'lusin',
                            'kg' => 'kg',
                            'liter' => 'liter',
                        ])
                        ->native()
                        ->required(),

                    TextInput::make('harga_beli')
                        ->label('Harga Beli')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),

                    TextInput::make('harga_jual')
                        ->label('Harga Jual')
                        ->numeric()
                        ->prefix('Rp')
                        ->required(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_barang')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('kategori')
                    ->label('Kategori')
                    ->colors([
                        'primary' => 'Elektronik',
                        'success' => 'Pakaian',
                        'warning' => 'Makanan & Minuman',
                        'info' => 'Perabot Rumah',
                        'danger' => 'Olahraga',
                        'gray' => 'Lainnya',
                    ]),

                TextColumn::make('satuan')
                    ->label('Satuan'),

                TextColumn::make('harga_beli')
                    ->label('Harga Beli')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('harga_jual')
                    ->label('Harga Jual')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            BarangChartWidget::class,
            BarangMaterialChart::class,
        ];
    }
}
