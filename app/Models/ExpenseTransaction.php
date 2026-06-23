<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExpenseTransaction extends Model
{
    protected $fillable = [
        'no_transaksi',
        'tanggal',
        'chart_of_account_id',
        'deskripsi',
        'jumlah',
        'metode_pembayaran',
        'dibayar_kepada',
        'nomor_bukti',
        'bukti_pembayaran',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah'  => 'decimal:2',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    // -------------------------------------------------------------------------
    // Journal Generation
    // -------------------------------------------------------------------------

    /**
     * Buat atau perbarui jurnal otomatis untuk transaksi beban.
     *
     * Jurnal:
     *   DEBIT  → Akun Beban (sesuai chart_of_account_id yang dipilih user)
     *   KREDIT → Kas Toko (1101)
     */
    public function buatJurnal(): void
    {
        $nomorJurnal = 'BBN-' . $this->no_transaksi;

        $akunBeban = $this->resolveAccount($this->chart_of_account_id, 'id');
        $akunKas   = $this->resolveAccountByKode('1101'); // Kas Toko

        DB::transaction(function () use ($nomorJurnal, $akunBeban, $akunKas) {
            // Hapus jurnal lama agar tidak duplikat saat edit
            JournalEntry::where('nomor_jurnal', $nomorJurnal)->delete();

            $entry = JournalEntry::create([
                'nomor_jurnal' => $nomorJurnal,
                'tanggal'      => $this->tanggal,
                'keterangan'   => 'Beban - ' . $this->deskripsi,
                'status'       => 'posted',
            ]);

            $jumlah = (float) $this->jumlah;

            // DEBIT — Akun Beban (dipilih oleh user)
            JournalLine::create([
                'journal_entry_id'    => $entry->id,
                'chart_of_account_id' => $akunBeban->id,
                'keterangan'          => $this->deskripsi . ' - ' . $this->no_transaksi,
                'debit'               => $jumlah,
                'kredit'              => 0,
            ]);

            // KREDIT — Kas Toko
            JournalLine::create([
                'journal_entry_id'    => $entry->id,
                'chart_of_account_id' => $akunKas->id,
                'keterangan'          => 'Bayar beban - ' . $this->no_transaksi,
                'debit'               => 0,
                'kredit'              => $jumlah,
            ]);
        });
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve akun beban langsung dari relasi (sudah ada di model ini).
     */
    private function resolveAccount(int|string $id, string $column = 'id'): ChartOfAccount
    {
        $account = ChartOfAccount::where($column, $id)->first();

        if (! $account) {
            throw new InvalidArgumentException(
                "Chart of Account dengan {$column} [{$id}] tidak ditemukan."
            );
        }

        return $account;
    }

    /**
     * Resolve akun berdasarkan kode_akun.
     */
    private function resolveAccountByKode(string $kodeAkun): ChartOfAccount
    {
        return $this->resolveAccount($kodeAkun, 'kode_akun');
    }
}
