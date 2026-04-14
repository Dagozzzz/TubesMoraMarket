<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresensiResource\Pages;
use App\Models\Presensi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// Form Components
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Select;

// Table Columns
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class PresensiResource extends Resource
{
    protected static ?string $model = Presensi::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Presensi';

    protected static ?string $pluralLabel = 'Data Presensi';

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

                        // Jika edit data, tampilkan ID lama
                        if ($record) {
                            $component->state($record->id_kategori);
                            return;
                        }

                        // Ambil data terakhir
                        $last = \App\Models\KategoriSupplier::orderBy('id_kategori', 'desc')->first();

                        if ($last) {
                            $number = (int) substr($last->id_kategori, 3) + 1;
                        } else {
                            $number = 1;
                        }

                        // Generate ID otomatis
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
                TextColumn::make('id_presensi')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('id_karyawan')
                    ->label('ID Karyawan')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('jam_masuk')
                    ->label('Masuk')
                    ->time(),

                TextColumn::make('jam_keluar')
                    ->label('Keluar')
                    ->time(),

                BadgeColumn::make('status_kehadiran')
                    ->label('Status')
                    ->colors([
                        'success' => 'Hadir',
                        'warning' => 'Izin',
                        'info' => 'Sakit',
                        'danger' => 'Alpha',
                    ]),
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
            'index' => Pages\ListPresensis::route('/'),
            'create' => Pages\CreatePresensi::route('/create'),
            'edit' => Pages\EditPresensi::route('/{record}/edit'),
        ];
    }
}