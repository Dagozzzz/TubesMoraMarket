<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

// Komponen Form
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;

// Komponen Tabel
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Supplier';

    protected static ?string $pluralModelLabel = 'Supplier';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('kode_supplier')
                    ->label('Kode Supplier')
                    ->default(function () {
                        $last = Supplier::orderBy('id', 'desc')->first();
                        $newNumber = $last ? (int) substr($last->kode_supplier, 3) + 1 : 1;
                        return 'SUP' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
                    })
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('nama_supplier')
                    ->label('Nama Supplier')
                    ->required(),

                TextInput::make('no_handphone')
                    ->label('No. Handphone')
                    ->tel()
                    ->required(),

                Select::make('kategori')
                    ->label('Kategori')
                    ->options([
                        'Food & Beverage'     => 'Food & Beverage',
                        'Household'           => 'Household',
                        'Personal Care'       => 'Personal Care',
                        'Frozen Food'         => 'Frozen Food',
                        'General Merchandise' => 'General Merchandise',
                    ])
                    ->required(),

                FileUpload::make('gambar')
                    ->label('Gambar')
                    ->image()
                    ->directory('suppliers')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_supplier')
                    ->label('Kode Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_supplier')
                    ->label('Nama Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('no_handphone')
                    ->label('No. Handphone')
                    ->searchable(),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Food & Beverage'     => 'primary',
                        'Household'           => 'success',
                        'Personal Care'       => 'warning',
                        'Frozen Food'         => 'danger',
                        'General Merchandise' => 'gray',
                        default               => 'secondary',
                    }),

                ImageColumn::make('gambar')
                    ->label('Gambar')
                    ->size(50),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit'   => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}