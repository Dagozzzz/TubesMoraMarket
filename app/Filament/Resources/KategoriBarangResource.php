<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriBarangResource\Pages;
use App\Models\KategoriBarang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Komponen form
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select; // tambahan

// Komponen kolom tabel
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn; // tambahan

class KategoriBarangResource extends Resource
{
    protected static ?string $model = KategoriBarang::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Kategori Barang';

    protected static ?string $pluralModelLabel = 'Kategori Barang';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // DIUBAH: dari required/unique jadi disabled + auto-generate
                TextInput::make('kode_kategori')
                    ->label('Kode Kategori')
                    ->disabled()
                    ->placeholder('Auto-generate (BRG001)')
                    ->dehydrated(false),

                // DIUBAH: nama_kategori -> nama_barang
                TextInput::make('nama_barang')
                    ->label('Nama Barang')
                    ->required()
                    ->maxLength(100),

                // Diubah dari TextInput ke Select
                Select::make('jenis_kategori')
                    ->label('Jenis Kategori')
                    ->options([
                        'Elektronik'        => 'Elektronik',
                        'Pakaian'           => 'Pakaian',
                        'Makanan & Minuman' => 'Makanan & Minuman',
                        'Perabot Rumah'     => 'Perabot Rumah',
                        'Olahraga'          => 'Olahraga',
                        'Lainnya'           => 'Lainnya',
                    ])
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_kategori')
                    ->label('Kode Kategori')
                    ->searchable()
                    ->sortable(),

                // DIUBAH: nama_kategori -> nama_barang
                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('jenis_kategori')
                    ->label('Jenis Kategori')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                //
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKategoriBarangs::route('/'),
            'create' => Pages\CreateKategoriBarang::route('/create'),
            'edit' => Pages\EditKategoriBarang::route('/{record}/edit'),
        ];
    }
}