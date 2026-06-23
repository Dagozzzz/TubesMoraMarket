<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Gaji extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_slip',
        'karyawan_id',
        'tgl',
        'periode_bulan',
        'periode_tahun',
        'gaji_pokok',
        'tunjangan',
        'potongan',
        'total_gaji',
        'status',
        'keterangan',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    // -------------------------------------------------------------------------
    // Auto Number
    // -------------------------------------------------------------------------

    /**
     * Generate nomor slip gaji otomatis (format: SLP-0000001).
     */
    public static function getNoSlip(): string
    {
        $last = self::orderByDesc('id')->first();

        if (! $last || ! $last->no_slip) {
            return 'SLP-0000001';
        }

        $number = (int) substr($last->no_slip, 4) + 1;

        return 'SLP-' . str_pad($number, 7, '0', STR_PAD_LEFT);
    }

    // -------------------------------------------------------------------------
    // Journal Generation
    // -------------------------------------------------------------------------

    /**
     * Buat atau perbarui jurnal otomatis saat gaji berstatus "dibayar".
     *
     * Jurnal:
     *   DEBIT  → Beban Gaji Karyawan (6101)
     *   KREDIT → Kas Toko (1101)
     *
     * Catatan: Metode ini hanya dipanggil ketika status diubah menjadi 'dibayar'.
     */
    public function buatJurnal(): void
    {
        // Hanya buat jurnal jika status sudah dibayar
        if ($this->status !== 'dibayar') {
            return;
        }

        $nomorJurnal = 'GAJI-' . $this->no_slip;

        $akunBebanGaji = $this->resolveAccount('6101'); // Beban Gaji Karyawan
        $akunKas       = $this->resolveAccount('1101'); // Kas Toko

        DB::transaction(function () use ($nomorJurnal, $akunBebanGaji, $akunKas) {
            // Hapus jurnal lama agar tidak duplikat
            JournalEntry::where('nomor_jurnal', $nomorJurnal)->delete();

            $namaKaryawan = optional($this->karyawan)->nama ?? 'Karyawan';

            $entry = JournalEntry::create([
                'nomor_jurnal' => $nomorJurnal,
                'tanggal'      => $this->tgl,
                'keterangan'   => 'Pembayaran gaji - ' . $namaKaryawan . ' (' . $this->no_slip . ')',
                'status'       => 'posted',
            ]);

            $totalGaji = (float) $this->total_gaji;

            // DEBIT — Beban Gaji Karyawan
            JournalLine::create([
                'journal_entry_id'    => $entry->id,
                'chart_of_account_id' => $akunBebanGaji->id,
                'keterangan'          => 'Gaji ' . $namaKaryawan . ' - ' . $this->no_slip,
                'debit'               => $totalGaji,
                'kredit'              => 0,
            ]);

            // KREDIT — Kas Toko
            JournalLine::create([
                'journal_entry_id'    => $entry->id,
                'chart_of_account_id' => $akunKas->id,
                'keterangan'          => 'Bayar gaji ' . $namaKaryawan . ' - ' . $this->no_slip,
                'debit'               => 0,
                'kredit'              => $totalGaji,
            ]);
        });
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    private function resolveAccount(string $kodeAkun): ChartOfAccount
    {
        $account = ChartOfAccount::where('kode_akun', $kodeAkun)->first();

        if (! $account) {
            throw new InvalidArgumentException(
                "Chart of Account dengan kode [{$kodeAkun}] tidak ditemukan. "
                . 'Pastikan ChartOfAccountSeeder sudah dijalankan.'
            );
        }

        return $account;
    }
}
