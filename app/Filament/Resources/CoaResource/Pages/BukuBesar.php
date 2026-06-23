<?php

namespace App\Filament\Resources\CoaResource\Pages;

use App\Filament\Resources\CoaResource;
use App\Models\ChartOfAccount;
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

    protected static string $resource = CoaResource::class;
    protected static string $view     = 'filament.resources.coa-resource.pages.buku-besar';
    protected static ?string $title   = 'Buku Besar';

    public ?string $kode_akun      = null;
    public ?string $tanggal_dari   = null;
    public ?string $tanggal_sampai = null;

    public Collection $lines;
    public float $total_debit  = 0;
    public float $total_kredit = 0;
    public float $saldo_akhir  = 0;
    public ?ChartOfAccount $coa = null;

    public function mount(): void
    {
        $this->lines = collect();
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('kode_akun')
                    ->label('Akun')
                    ->options(
                        ChartOfAccount::orderBy('kode_akun')
                            ->get()
                            ->mapWithKeys(fn($coa) => [
                                $coa->kode_akun => "{$coa->kode_akun} - {$coa->nama_akun}"
                            ])
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
        $this->validate([
            'kode_akun'      => 'required',
            'tanggal_dari'   => 'required|date',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_dari',
        ]);

        $this->coa = ChartOfAccount::where('kode_akun', $this->kode_akun)->first();

        if (!$this->coa) return;

        $this->lines = JournalLine::with('journalEntry')
            ->where('chart_of_account_id', $this->coa->id)
            ->whereHas('journalEntry', function ($q) {
                $q->whereBetween('tanggal', [$this->tanggal_dari, $this->tanggal_sampai])
                  ->where('status', 'posted');
            })
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->orderBy('journal_entries.tanggal', 'asc')
            ->orderBy('journal_entries.id', 'asc')
            ->select('journal_lines.*')
            ->get();

        $this->total_debit  = (float) $this->lines->sum('debit');
        $this->total_kredit = (float) $this->lines->sum('kredit');

        if ($this->coa->saldo_normal === 'Debit') {
            $this->saldo_akhir = $this->total_debit - $this->total_kredit;
        } else {
            $this->saldo_akhir = $this->total_kredit - $this->total_debit;
        }
    }
}