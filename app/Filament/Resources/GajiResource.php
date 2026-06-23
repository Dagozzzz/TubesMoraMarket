<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GajiResource\Pages;
use App\Models\Gaji;
use App\Models\Karyawan;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\GajiDibayarMail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

// tambahan komponen form
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Set;

// tambahan komponen tabel
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;

class GajiResource extends Resource
{
    protected static ?string $model = Gaji::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Penggajian';

    protected static ?string $navigationGroup = 'Transaksi';

    /**
     * Daftar nama bulan dalam Bahasa Indonesia
     */
    private static function daftarBulan(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    /**
     * Hitung ulang ringkasan presensi & potongan setiap kali karyawan/periode berubah
     */
    private static function hitungDariPresensi(Get $get, Set $set): void
    {
        $karyawanId = $get('karyawan_id');
        $bulan = $get('periode_bulan');
        $tahun = $get('periode_tahun');

        if (!$karyawanId || !$bulan || !$tahun) {
            return;
        }

        // Ambil ringkasan kehadiran dari data presensi
        $ringkasan = Presensi::ringkasanBulanan($karyawanId, $bulan, $tahun);

        $set('jumlah_hadir', $ringkasan['hadir']);
        $set('jumlah_izin', $ringkasan['izin']);
        $set('jumlah_sakit', $ringkasan['sakit']);
        $set('jumlah_alpa', $ringkasan['alpa']);

        // Potongan otomatis: Rp 100.000 per hari Alpa
        $potongan = $ringkasan['alpa'] * 100000;
        $set('potongan', $potongan);

        // Buat keterangan otomatis berdasarkan kehadiran
        $keterangan = "Kehadiran bulan " . self::daftarBulan()[(int)$bulan] . " {$tahun}: ";
        $keterangan .= "Hadir {$ringkasan['hadir']} hari";

        if ($ringkasan['izin'] > 0) {
            $keterangan .= ", Izin {$ringkasan['izin']} hari";
        }
        if ($ringkasan['sakit'] > 0) {
            $keterangan .= ", Sakit {$ringkasan['sakit']} hari";
        }
        if ($ringkasan['alpa'] > 0) {
            $keterangan .= ", Alpa {$ringkasan['alpa']} hari";
            $keterangan .= ". Potongan: {$ringkasan['alpa']}x Alpa = Rp " . number_format($potongan, 0, ',', '.');
        }

        $set('keterangan', $keterangan);

        // Hitung ulang total gaji
        self::hitungTotal($get, $set);
    }

    /**
     * Hitung total gaji = pokok + tunjangan - potongan
     */
    private static function hitungTotal(Get $get, Set $set): void
    {
        $pokok = (float) ($get('gaji_pokok') ?? 0);
        $tunjangan = (float) ($get('tunjangan') ?? 0);
        $potongan = (float) ($get('potongan') ?? 0);
        $set('total_gaji', $pokok + $tunjangan - $potongan);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Gaji')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        TextInput::make('no_slip')
                            ->default(fn () => Gaji::getNoSlip())
                            ->label('Nomor Slip')
                            ->required()
                            ->readonly(),
                        
                        DatePicker::make('tgl')
                            ->label('Tanggal Penggajian')
                            ->default(now())
                            ->required(),

                        Select::make('karyawan_id')
                            ->label('Karyawan')
                            ->options(Karyawan::pluck('nama', 'id')->toArray())
                            ->required()
                            ->searchable()
                            ->placeholder('Pilih Karyawan')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if (!$state) {
                                    $set('gaji_pokok', 0);
                                } else {
                                    $karyawan = Karyawan::find($state);
                                    if ($karyawan && $karyawan->jabatan) {
                                        $jabatan = strtolower($karyawan->jabatan);
                                        
                                        // Gaji dibagi berdasarkan jabatan
                                        if (str_contains($jabatan, 'kebersihan')) {
                                            $set('gaji_pokok', 1500000);
                                        } elseif (str_contains($jabatan, 'karyawan') || str_contains($jabatan, 'staff')) {
                                            $set('gaji_pokok', 3000000);
                                        } elseif (str_contains($jabatan, 'manajer') || str_contains($jabatan, 'manager')) {
                                            $set('gaji_pokok', 5000000);
                                        } else {
                                            $set('gaji_pokok', 2000000);
                                        }
                                    } else {
                                        $set('gaji_pokok', 0);
                                    }
                                }
                                
                                // Tarik data presensi otomatis
                                self::hitungDariPresensi($get, $set);
                            }),

                        Select::make('periode_bulan')
                            ->label('Bulan Periode')
                            ->options(self::daftarBulan())
                            ->default(now()->month)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::hitungDariPresensi($get, $set);
                            }),

                        Select::make('periode_tahun')
                            ->label('Tahun Periode')
                            ->options(function () {
                                $tahunSekarang = (int) date('Y');
                                $daftar = [];
                                for ($i = $tahunSekarang - 1; $i <= $tahunSekarang + 1; $i++) {
                                    $daftar[$i] = $i;
                                }
                                return $daftar;
                            })
                            ->default((int) date('Y'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::hitungDariPresensi($get, $set);
                            }),

                        TextInput::make('status')
                            ->default('draft')
                            ->hidden(),
                    ])
                    ->columns(3),

                Section::make('Ringkasan Presensi')
                    ->icon('heroicon-m-calendar-days')
                    ->description('Data kehadiran otomatis diambil dari presensi karyawan di periode yang dipilih.')
                    ->schema([
                        TextInput::make('jumlah_hadir')
                            ->label('Hadir')
                            ->suffix('hari')
                            ->numeric()
                            ->default(0)
                            ->readonly()
                            ->dehydrated(false),

                        TextInput::make('jumlah_izin')
                            ->label('Izin')
                            ->suffix('hari')
                            ->numeric()
                            ->default(0)
                            ->readonly()
                            ->dehydrated(false),

                        TextInput::make('jumlah_sakit')
                            ->label('Sakit')
                            ->suffix('hari')
                            ->numeric()
                            ->default(0)
                            ->readonly()
                            ->dehydrated(false),

                        TextInput::make('jumlah_alpa')
                            ->label('Alpa')
                            ->suffix('hari')
                            ->numeric()
                            ->default(0)
                            ->readonly()
                            ->dehydrated(false),
                    ])
                    ->columns(4),

                Section::make('Rincian Gaji')
                    ->schema([
                        TextInput::make('gaji_pokok')
                            ->label('Gaji Pokok')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->prefix('Rp')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::hitungTotal($get, $set);
                            }),

                        TextInput::make('tunjangan')
                            ->label('Tunjangan')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->prefix('Rp')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::hitungTotal($get, $set);
                            }),

                        TextInput::make('potongan')
                            ->label('Potongan')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->prefix('Rp')
                            ->helperText('Otomatis dihitung dari jumlah Alpa (Rp 100.000/hari)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::hitungTotal($get, $set);
                            }),

                        TextInput::make('total_gaji')
                            ->label('Total Gaji')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->prefix('Rp')
                            ->readonly()
                            ->dehydrated(),
                    ])
                    ->columns(4),

                Section::make('Keterangan')
                    ->schema([
                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->helperText('Terisi otomatis berdasarkan data presensi, bisa diedit manual jika perlu.')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_slip')
                    ->label('No Slip')
                    ->searchable(),
                
                TextColumn::make('karyawan.nama')
                    ->label('Nama Karyawan')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('periode_bulan')
                    ->label('Periode')
                    ->formatStateUsing(function ($state, $record) {
                        $namaBulan = self::daftarBulan()[(int)$state] ?? $state;
                        return $namaBulan . ' ' . ($record->periode_tahun ?? '-');
                    })
                    ->sortable(),
                
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'dibayar' => 'success',
                        'draft' => 'warning',
                        default => 'secondary',
                    }),
                
                TextColumn::make('total_gaji')
                    ->label('Total Gaji')
                    ->formatStateUsing(fn (string|int|null $state): string => 'Rp ' . number_format((float)$state, 0, ',', '.'))
                    ->sortable()
                    ->alignment('end'),
                
                TextColumn::make('tgl')
                    ->label('Tgl Bayar')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'draft' => 'Draft',
                        'dibayar' => 'Dibayar',
                    ])
            ])
            ->actions([
                Tables\Actions\Action::make('bayar')
                    ->label('Bayar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Tandai sebagai Telah Dibayar')
                    ->modalDescription('Apakah Anda yakin ingin mengubah status gaji ini menjadi telah dibayar?')
                    ->action(function (Gaji $record) {
                        $record->update(['status' => 'dibayar']);
                        
                        // Kirim email jika karyawan punya email
                        if ($record->karyawan && $record->karyawan->email) {
                            Mail::to($record->karyawan->email)->send(new GajiDibayarMail($record));
                        }
                    })
                    ->visible(fn (Gaji $record) => $record->status === 'draft'),
                
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
            'index' => Pages\ListGaji::route('/'),
            'create' => Pages\CreateGaji::route('/create'),
            'edit' => Pages\EditGaji::route('/{record}/edit'),
        ];
    }
}
