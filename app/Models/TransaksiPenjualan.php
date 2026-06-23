<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransaksiPenjualan extends Model
{
    protected $table = 'transaksi_penjualan';

    protected $fillable = [
        'kode_penjualan',
        'id_customer',
        'id_kasir',
        'tanggal_penjualan',
        'status_pembayaran',
        'metode_pembayaran',
        'midtrans_order_id',
        'midtrans_snap_token',
        'midtrans_redirect_url',
        'total_harga',
        'catatan',
    ];

    protected $casts = [
        'tanggal_penjualan' => 'datetime',
        'total_harga'       => 'decimal:2',
    ];

    public static function generateKodePenjualan(): string
    {
        $last   = self::latest('id')->first();
        $number = $last ? ((int) substr($last->kode_penjualan, 4)) + 1 : 1;

        return 'PJL-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($penjualan) {
            if (! $penjualan->kode_penjualan) {
                $penjualan->kode_penjualan = self::generateKodePenjualan();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_kasir', 'id');
    }

    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksiPenjualan::class, 'id_transaksi_penjualan', 'id');
    }

    // -------------------------------------------------------------------------
    // Journal Generation
    // -------------------------------------------------------------------------

    /**
     * Buat atau perbarui jurnal otomatis untuk transaksi penjualan.
     *
     * Aturan bisnis:
     *  - lunas              → Debit Kas Toko (1101)
     *  - pending/belum_lunas→ Debit Piutang Pelanggan (1120)
     *  - gagal/expired      → HAPUS jurnal lama (jika ada), TIDAK buat baru
     *
     * Setiap penjualan lunas / piutang menghasilkan DUA journal entry:
     *  1. Revenue  : Debit Kas/Piutang  | Credit Penjualan (4101)
     *  2. COGS/HPP : Debit HPP (5101)   | Credit Persediaan (1130)
     */
    public function buatJurnal(): void
    {
        // kode_penjualan sudah berformat 'PJL-xxxx'.
        // Nomor jurnal menggunakan prefix REV- dan HPP- di depan kode.
        $nomorJurnalRevenue = 'REV-' . $this->kode_penjualan;
        $nomorJurnalHpp     = 'HPP-' . $this->kode_penjualan;

        // Transaksi gagal / expired → hapus jurnal lama jika ada, lalu keluar
        if (in_array($this->status_pembayaran, ['gagal', 'expired'], true)) {
            JournalEntry::whereIn('nomor_jurnal', [$nomorJurnalRevenue, $nomorJurnalHpp])->delete();
            return;
        }

        // Ambil akun yang dibutuhkan (kode sesuai COA seeder)
        $akunKas        = $this->resolveAccount('1101'); // Kas Toko
        $akunPiutang    = $this->resolveAccount('1120'); // Piutang Pelanggan
        $akunPenjualan  = $this->resolveAccount('4101'); // Penjualan Barang Kelontong
        $akunHpp        = $this->resolveAccount('5101'); // HPP Barang Kelontong
        $akunPersediaan = $this->resolveAccount('1130'); // Persediaan Barang Dagang

        // Tentukan akun debit berdasarkan status pembayaran
        $akunDebit = ($this->status_pembayaran === 'lunas') ? $akunKas : $akunPiutang;

        DB::transaction(function () use (
            $nomorJurnalRevenue, $nomorJurnalHpp,
            $akunDebit, $akunPenjualan, $akunHpp, $akunPersediaan
        ) {
            // ── Hapus jurnal lama agar tidak duplikat saat edit ──────────────
            JournalEntry::whereIn('nomor_jurnal', [
                $nomorJurnalRevenue,
                $nomorJurnalHpp,
            ])->delete();

            $tanggal    = $this->tanggal_penjualan->toDateString();
            $totalHarga = (float) $this->total_harga;

            // ── Entry 1: Revenue ─────────────────────────────────────────────
            $entryRevenue = JournalEntry::create([
                'nomor_jurnal' => $nomorJurnalRevenue,
                'tanggal'      => $tanggal,
                'keterangan'   => 'Penjualan barang - ' . $this->kode_penjualan,
                'status'       => 'posted',
            ]);

            JournalLine::create([
                'journal_entry_id'    => $entryRevenue->id,
                'chart_of_account_id' => $akunDebit->id,
                'keterangan'          => 'Penjualan - ' . $this->kode_penjualan,
                'debit'               => $totalHarga,
                'kredit'              => 0,
            ]);

            JournalLine::create([
                'journal_entry_id'    => $entryRevenue->id,
                'chart_of_account_id' => $akunPenjualan->id,
                'keterangan'          => 'Pendapatan penjualan - ' . $this->kode_penjualan,
                'debit'               => 0,
                'kredit'              => $totalHarga,
            ]);

            // ── Entry 2: COGS / HPP ──────────────────────────────────────────
            // Hitung total HPP dari detail barang yang terjual
            $totalHpp = $this->hitungTotalHpp();

            if ($totalHpp > 0) {
                $entryHpp = JournalEntry::create([
                    'nomor_jurnal' => $nomorJurnalHpp,
                    'tanggal'      => $tanggal,
                    'keterangan'   => 'HPP penjualan - ' . $this->kode_penjualan,
                    'status'       => 'posted',
                ]);

                JournalLine::create([
                    'journal_entry_id'    => $entryHpp->id,
                    'chart_of_account_id' => $akunHpp->id,
                    'keterangan'          => 'HPP - ' . $this->kode_penjualan,
                    'debit'               => $totalHpp,
                    'kredit'              => 0,
                ]);

                JournalLine::create([
                    'journal_entry_id'    => $entryHpp->id,
                    'chart_of_account_id' => $akunPersediaan->id,
                    'keterangan'          => 'Keluar persediaan - ' . $this->kode_penjualan,
                    'debit'               => 0,
                    'kredit'              => $totalHpp,
                ]);
            }
        });
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Ambil total HPP dari detail transaksi menggunakan harga_beli barang.
     * Jika barang tidak ada / harga_beli 0, lewati.
     */
    private function hitungTotalHpp(): float
    {
        return (float) $this->detailTransaksi()
            ->with('barang')
            ->get()
            ->sum(function (DetailTransaksiPenjualan $detail): float {
                $hargaBeli = (float) optional($detail->barang)->harga_beli;
                return $hargaBeli * (int) $detail->jumlah;
            });
    }

    /**
     * Resolve ChartOfAccount by kode_akun.
     * Lempar exception agar tidak crash dengan null->id.
     */
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