<?php

namespace App\Filament\Pages;

use App\Models\ChartOfAccount;
use App\Models\JournalLine;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

/**
 * Buku Besar — General Ledger Report.
 *
 * Read-only report page. Bukan CRUD.
 * Menampilkan SEMUA akun COA yang punya transaksi pada periode yang dipilih.
 * Laporan di-refresh otomatis saat tanggal berubah (tidak ada tombol submit).
 * Dikelompokkan per kategori COA sesuai urutan standar akuntansi.
 */
class BukuBesar extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string  $view            = 'filament.pages.buku-besar';
    protected static ?string $navigationIcon  = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Buku Besar';
    protected static ?string $navigationGroup = 'Akuntansi';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $slug            = 'buku-besar';
    protected static ?string $title           = 'Buku Besar';

    // -------------------------------------------------------------------------
    // Form state (hanya tanggal)
    // -------------------------------------------------------------------------

    public array $data = [];

    // -------------------------------------------------------------------------
    // Report state
    // -------------------------------------------------------------------------

    /**
     * Hasil laporan. Struktur:
     * [
     *   [
     *     'kategori' => 'Aset',
     *     'accounts' => [
     *       [
     *         'kode'        => '1101',
     *         'nama'        => 'Kas Toko',
     *         'saldo_normal'=> 'Debit',
     *         'saldo_awal'  => 0.0,
     *         'saldo_akhir' => -128000.0,
     *         'lines'       => [ [...], ... ],
     *       ],
     *       ...
     *     ],
     *   ],
     *   ...
     * ]
     *
     * @var array<int, array<string, mixed>>
     */
    public array $reportData = [];

    // -------------------------------------------------------------------------
    // Urutan kategori (standar akuntansi)
    // -------------------------------------------------------------------------

    private const KATEGORI_ORDER = [
        'Aset',
        'Liabilitas',
        'Ekuitas',
        'Pendapatan',
        'Harga Pokok Penjualan',
        'Beban',
    ];

    // -------------------------------------------------------------------------
    // Boot
    // -------------------------------------------------------------------------

    public function mount(): void
    {
        $this->form->fill([
            'tanggal_mulai' => now()->startOfMonth()->toDateString(),
            'tanggal_akhir' => now()->endOfMonth()->toDateString(),
        ]);

        // Langsung generate saat halaman dibuka
        $this->generateReport();
    }

    // -------------------------------------------------------------------------
    // Form — hanya dua DatePicker, keduanya live
    // -------------------------------------------------------------------------

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('tanggal_mulai')
                    ->label('Dari Tanggal')
                    ->required()
                    ->native(false)
                    ->displayFormat('d M Y')
                    ->closeOnDateSelection()
                    ->live()
                    ->afterStateUpdated(fn () => $this->generateReport()),

                DatePicker::make('tanggal_akhir')
                    ->label('Sampai Tanggal')
                    ->required()
                    ->native(false)
                    ->displayFormat('d M Y')
                    ->closeOnDateSelection()
                    ->live()
                    ->afterStateUpdated(fn () => $this->generateReport()),
            ])
            ->columns(2)
            ->statePath('data');
    }

    // -------------------------------------------------------------------------
    // Generate laporan
    // -------------------------------------------------------------------------

    /**
     * Hitung buku besar untuk SEMUA akun yang punya mutasi pada periode.
     * Dipanggil otomatis saat mount() dan saat salah satu tanggal berubah.
     */
    public function generateReport(): void
    {
        $mulai = $this->data['tanggal_mulai'] ?? now()->startOfMonth()->toDateString();
        $akhir = $this->data['tanggal_akhir'] ?? now()->endOfMonth()->toDateString();

        if (! $mulai || ! $akhir) {
            $this->reportData = [];
            return;
        }

        $mulaiCarbon = Carbon::parse($mulai)->startOfDay();
        $akhirCarbon = Carbon::parse($akhir)->endOfDay();

        // Satu query: semua journal_lines yang posted, eager-load entry
        $semuaLines = JournalLine::with([
                'journalEntry' => fn ($q) => $q->where('status', 'posted'),
            ])
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
            ->get()
            ->groupBy('chart_of_account_id');

        // Semua COA diurutkan berdasar kode
        $semuaCoa = ChartOfAccount::orderBy('kode_akun')->get();

        $report = [];

        foreach (self::KATEGORI_ORDER as $kategori) {
            $coaKategori = $semuaCoa->filter(fn ($c) => $c->kategori === $kategori);

            if ($coaKategori->isEmpty()) {
                continue;
            }

            $accounts = [];

            foreach ($coaKategori as $coa) {
                $lines = $semuaLines->get($coa->id, collect());

                // Mutasi dalam periode
                $periodLines = $lines
                    ->filter(fn (JournalLine $l) =>
                        $l->journalEntry !== null
                        && Carbon::parse($l->journalEntry->tanggal)->between($mulaiCarbon, $akhirCarbon)
                    )
                    ->sortBy([
                        fn (JournalLine $a, JournalLine $b) =>
                            Carbon::parse($a->journalEntry->tanggal)
                                ->diffInSeconds(Carbon::parse($b->journalEntry->tanggal), false),
                    ])
                    ->sortBy(fn (JournalLine $l) => [
                        $l->journalEntry->tanggal,
                        $l->journal_entry_id,
                        $l->id,
                    ]);

                // Akun tanpa mutasi di periode ini → skip
                if ($periodLines->isEmpty()) {
                    continue;
                }

                // Saldo awal (semua mutasi sebelum mulai)
                $sebelumLines = $lines->filter(fn (JournalLine $l) =>
                    $l->journalEntry !== null
                    && Carbon::parse($l->journalEntry->tanggal)->lt($mulaiCarbon)
                );
                $saldoAwal = $this->hitungSaldo($sebelumLines, $coa->saldo_normal);

                // Running balance per baris
                $saldo     = $saldoAwal;
                $builtLines = [];

                foreach ($periodLines as $l) {
                    $d = (float) $l->debit;
                    $k = (float) $l->kredit;

                    $saldo += $coa->saldo_normal === 'Debit'
                        ? ($d - $k)
                        : ($k - $d);

                    $builtLines[] = [
                        'tanggal'    => Carbon::parse($l->journalEntry->tanggal)->format('d/m/Y'),
                        'no_jurnal'  => $l->journalEntry->nomor_jurnal,
                        'keterangan' => $l->keterangan ?: $l->journalEntry->keterangan,
                        'debit'      => $d,
                        'kredit'     => $k,
                        'saldo'      => $saldo,
                    ];
                }

                $accounts[] = [
                    'kode'         => $coa->kode_akun,
                    'nama'         => $coa->nama_akun,
                    'saldo_normal' => $coa->saldo_normal,
                    'saldo_awal'   => $saldoAwal,
                    'saldo_akhir'  => $saldo,
                    'lines'        => $builtLines,
                ];
            }

            if (! empty($accounts)) {
                $report[] = [
                    'kategori' => $kategori,
                    'accounts' => $accounts,
                ];
            }
        }

        $this->reportData = $report;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function hitungSaldo(Collection $lines, string $saldoNormal): float
    {
        $d = (float) $lines->sum('debit');
        $k = (float) $lines->sum('kredit');

        return $saldoNormal === 'Debit' ? ($d - $k) : ($k - $d);
    }

    public function getPeriodeLabel(): string
    {
        $mulai = $this->data['tanggal_mulai'] ?? null;
        $akhir = $this->data['tanggal_akhir'] ?? null;

        if (! $mulai || ! $akhir) return '';

        return Carbon::parse($mulai)->translatedFormat('d F Y')
            . ' — '
            . Carbon::parse($akhir)->translatedFormat('d F Y');
    }

    public static function rp(float $n): string
    {
        if ($n == 0) return 'Rp 0';
        $abs = number_format(abs($n), 0, ',', '.');
        return $n < 0 ? '(Rp '.$abs.')' : 'Rp '.$abs;
    }
}
