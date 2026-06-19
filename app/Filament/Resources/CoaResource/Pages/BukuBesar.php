<?php

namespace App\Filament\Resources\CoaResource\Pages;

use App\Filament\Resources\CoaResource;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class BukuBesar extends Page implements HasForms
{
    use InteractsWithForms;

    private const KODE_AKUN_COLUMN = 'kode_akun';

    private const CHART_OF_ACCOUNT_ID_COLUMN = 'chart_of_account_id';

    protected static string $resource = CoaResource::class;

    protected static string $view = 'filament.resources.coa-resource.pages.buku-besar';

    protected static ?string $title = 'Buku Besar';

    protected static ?string $navigationLabel = 'Buku Besar';

    public ?string $kode_akun = null;

    public ?string $tanggal_dari = null;

    public ?string $tanggal_sampai = null;

    public ?string $saldo_normal = null;

    /** @var Collection<int, JournalLine> */
    public Collection $lines;

    public float $saldo_awal = 0;

    public float $total_debit = 0;

    public float $total_kredit = 0;

    public float $saldo_akhir = 0;

    public function mount(): void
    {
        $this->lines = collect();
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make(self::KODE_AKUN_COLUMN)
                    ->label('Akun')
                    ->options(
                        ChartOfAccount::orderBy(self::KODE_AKUN_COLUMN)
                            ->get()
                            ->mapWithKeys(fn (ChartOfAccount $coa): array => [
                                $coa->kode_akun => "{$coa->kode_akun} - {$coa->nama_akun}",
                            ])
                            ->all()
                    )
                    ->searchable()
                    ->required(),

                DatePicker::make('tanggal_dari')
                    ->label('Dari Tanggal')
                    ->required(),

                DatePicker::make('tanggal_sampai')
                    ->label('Sampai Tanggal')
                    ->required(),
            ])
            ->columns(3);
    }

    public function tampilkan(): void
    {
        $this->form->validate();

        $coa = ChartOfAccount::where(self::KODE_AKUN_COLUMN, $this->kode_akun)->first();

        if (! $coa) {
            return;
        }

        $this->saldo_normal = $coa->saldo_normal;

        // Ambil semua lines untuk akun ini dalam rentang tanggal
        $this->lines = JournalLine::with('journalEntry')
            ->where(self::CHART_OF_ACCOUNT_ID_COLUMN, $coa->id)
            ->whereHas('journalEntry', function ($q) {
                $q->whereBetween('tanggal', [$this->tanggal_dari, $this->tanggal_sampai])
                    ->where('status', 'posted');
            })
            ->orderBy(
                JournalEntry::select('tanggal')
                    ->whereColumn('id', 'journal_lines.journal_entry_id'),
                'asc'
            )
            ->get();

        $this->total_debit = $this->lines->sum(
            fn (JournalLine $line): float => (float) $line->debit
        );
        $this->total_kredit = $this->lines->sum(
            fn (JournalLine $line): float => (float) $line->kredit
        );

        // Hitung saldo akhir berdasarkan saldo normal
        if ($coa->saldo_normal === 'Debit') {
            $this->saldo_akhir = $this->total_debit - $this->total_kredit;
        } else {
            $this->saldo_akhir = $this->total_kredit - $this->total_debit;
        }
    }
}
