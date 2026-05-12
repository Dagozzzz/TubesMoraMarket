<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Models\Karyawan;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;

use Filament\Resources\Resource;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form->schema([

            TextInput::make('kode_karyawan')
                ->disabled()
                ->dehydrated(false),

            TextInput::make('nama')->required(),

            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),

            TextInput::make('tempat_lahir')->required(),

            DatePicker::make('tanggal_lahir')->required(),

            Textarea::make('alamat')->required(),

            TextInput::make('nik')
                ->required()
                ->rule('digits:16')
                ->maxLength(16)
                ->unique(ignoreRecord: true),

            TextInput::make('nip')->unique(ignoreRecord: true),

            Select::make('jenis_kelamin')
                ->options([
                    'L' => 'Laki-laki',
                    'P' => 'Perempuan',
                ])
                ->required(),

            Select::make('jabatan_id')
                ->label('Jabatan')
                ->options(function () {
                    return \App\Models\Jabatan::all()->unique('nama_jabatan')->pluck('nama_jabatan', 'id');
                })
                ->required(),

            FileUpload::make('foto')
                ->image()
                ->directory('karyawan'),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([

            TextColumn::make('kode_karyawan'),

            ImageColumn::make('foto')->circular(),

            TextColumn::make('nama')->searchable(),

            TextColumn::make('jabatan.nama_jabatan'),

            TextColumn::make('jenis_kelamin'),

        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }
}