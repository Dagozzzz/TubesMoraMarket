<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KategoriSupplierResource\Pages;
use App\Models\KategoriSupplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Form
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;

// Table
use Filament\Tables\Columns\TextColumn;

class KategoriSupplierResource extends Resource
{
    protected static ?string $model = KategoriSupplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Kategori Supplier';
    protected static ?string $pluralLabel = 'Data Kategori Supplier';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Grid::make(2)->schema([

                    TextInput::make('id_kategori')
                        ->label('ID Kategori')
                        ->disabled()
                        ->dehydrated(true)
                        ->afterStateHydrated(function ($component, $state, $record) {

                            // kalau edit
                            if ($record) {
                                $component->state($record->id_kategori);
                                return;
                            }

                            // generate realtime
                          $last = KategoriSupplier::orderBy('id_kategori', 'desc')->first();

                            if ($last) {
                                $number = (int) substr($last->id_kategori, 3) + 1;
                            } else {
                                $number = 1;
                            }

                            $component->state('KTG' . str_pad($number, 3, '0', STR_PAD_LEFT));
                        }),

                    TextInput::make('nama_kategori')
                        ->label('Nama Kategori')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Textarea::make('deskripsi')
                        ->label('Deskripsi')
                        ->required()
                        ->columnSpan(2),

                ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_kategori')
                    ->label('ID Kategori')
                    ->sortable(),

                TextColumn::make('nama_kategori')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->deskripsi),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime()
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
            'index' => Pages\ListKategoriSuppliers::route('/'),
            'create' => Pages\CreateKategoriSupplier::route('/create'),
            'edit' => Pages\EditKategoriSupplier::route('/{record}/edit'),
        ];
    }
}