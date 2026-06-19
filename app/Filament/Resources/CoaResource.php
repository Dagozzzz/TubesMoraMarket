<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoaResource\Pages;
use App\Models\ChartOfAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CoaResource extends Resource
{
    private const KODE_AKUN_COLUMN = 'kode_akun';

    protected static ?string $model = ChartOfAccount::class;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Chart Of Account';

    protected static ?string $navigationGroup = 'Master Data';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy(self::KODE_AKUN_COLUMN);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make(self::KODE_AKUN_COLUMN)
                    ->label('Kode Akun')
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('nama_akun')
                    ->label('Nama Akun')
                    ->required(),

                Forms\Components\Select::make('kategori')
                    ->options([
                        'Aset' => 'Aset',
                        'Liabilitas' => 'Liabilitas',
                        'Ekuitas' => 'Ekuitas',
                        'Pendapatan' => 'Pendapatan',
                        'Harga Pokok Penjualan' => 'Harga Pokok Penjualan',
                        'Beban' => 'Beban',
                    ])
                    ->native()
                    ->required(),

                Forms\Components\Select::make('saldo_normal')
                    ->options([
                        'Debit' => 'Debit',
                        'Kredit' => 'Kredit',
                    ])
                    ->native()
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make(self::KODE_AKUN_COLUMN)
                    ->label('Kode Akun')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_akun')
                    ->label('Nama Akun')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kategori')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('saldo_normal')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->options([
                        'Aset' => 'Aset',
                        'Liabilitas' => 'Liabilitas',
                        'Ekuitas' => 'Ekuitas',
                        'Pendapatan' => 'Pendapatan',
                        'Harga Pokok Penjualan' => 'Harga Pokok Penjualan',
                        'Beban' => 'Beban',
                    ]),
            ])
            ->paginated(false)
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make()
                //     ->label('Hapus')
                //     ->icon('heroicon-o-trash')
                //     ->button()
                //     ->color('danger')
                //     ->requiresConfirmation()
                //     ->modalHeading('Hapus Chart Of Account')
                //     ->modalDescription('Apakah Anda yakin ingin menghapus akun ini? Data yang sudah dihapus tidak dapat dikembalikan.')
                //     ->modalSubmitActionLabel('Hapus')
                //     ->modalCancelActionLabel('Batal')
                //     ->successNotificationTitle('Chart Of Account berhasil dihapus')
                //     ->extraAttributes([
                //         'class' => 'relative z-10',
                //         'onclick' => 'event.stopPropagation()',
                //     ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoas::route('/'),
            'create' => Pages\CreateCoa::route('/create'),
            'edit' => Pages\EditCoa::route('/{record}/edit'),
            'buku-besar' => Pages\BukuBesar::route('/buku-besar'),
        ];
    }

    public static function getNavigationItems(): array
    {
        return [
            ...parent::getNavigationItems(),
            NavigationItem::make('Buku Besar')
                ->url(static::getUrl('buku-besar'))
                ->icon('heroicon-o-book-open')
                ->group('Master Data'),
        ];
    }
}
