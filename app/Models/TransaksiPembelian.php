<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransaksiPembelian extends Model
{
    protected $table = 'transaksi_pembelian';

    protected $fillable = [
        'kode_pembelian',
        'id_supplier',
        'tanggal_pembelian',
        'status_pembayaran',
        'metode_pembayaran',
        'total_harga',
        'jumlah_bayar',
        'kembalian',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pembelian' => 'date',
        'total_harga'       => 'decimal:2',
        'jumlah_bayar'      => 'decimal:2',
        'kembalian'         => 'decimal:2',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id');
    }

    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksiPembelian::class, 'id_transaksi_pembelian', 'id');
    }

    // -------------------------------------------------------------------------
    // Journal Generation
    // -------------------------------------------------------------------------

    /**
     * Buat atau perbarui jurnal otomatis untuk transaksi pembelian.
     *
     * Aturan:
     *  - lunas       → Debit Persediaan | Credit Kas
     *  - belum_lunas → Debit Persediaan | Credit Hutang Usaha
     *  - cicilan     → Debit Persediaan | Credit Kas (sebagian) + Hutang (sisa)
     */
    public function buatJurnal(): void
    {
        // kode_pembelian sudah berformat 'PBL-xxxx', pakai langsung sebagai nomor jurnal
        $nomorJurnal = $this->kode_pembelian;

        $akunPersediaan = $this->resolveAccount('1130'); // Persediaan Barang Dagang
        $akunKas        = $this->resolveAccount('1101'); // Kas Toko
        $akunUtang      = $this->resolveAccount('2101'); // Hutang Usaha Supplier

        DB::transaction(function () use ($nomorJurnal, $akunPersediaan, $akunKas, $akunUtang) {
            // Hapus jurnal lama agar tidak duplikat saat edit
            JournalEntry::where('nomor_jurnal', $nomorJurnal)->delete();

            $entry = JournalEntry::create([
                'nomor_jurnal' => $nomorJurnal,
                'tanggal'      => $this->tanggal_pembelian,
                'keterangan'   => 'Pembelian barang - ' . $this->kode_pembelian,
                'status'       => 'posted',
            ]);

            $totalHarga  = (float) $this->total_harga;
            $jumlahBayar = (float) $this->jumlah_bayar;

            // DEBIT — Persediaan Barang
            JournalLine::create([
                'journal_entry_id'    => $entry->id,
                'chart_of_account_id' => $akunPersediaan->id,
                'keterangan'          => 'Pembelian barang - ' . $this->kode_pembelian,
                'debit'               => $totalHarga,
                'kredit'              => 0,
            ]);

            if ($this->status_pembayaran === 'lunas') {

                // KREDIT — Kas Toko
                JournalLine::create([
                    'journal_entry_id'    => $entry->id,
                    'chart_of_account_id' => $akunKas->id,
                    'keterangan'          => 'Bayar lunas pembelian - ' . $this->kode_pembelian,
                    'debit'               => 0,
                    'kredit'              => $totalHarga,
                ]);

            } elseif ($this->status_pembayaran === 'belum_lunas') {

                // KREDIT — Hutang Usaha Supplier
                JournalLine::create([
                    'journal_entry_id'    => $entry->id,
                    'chart_of_account_id' => $akunUtang->id,
                    'keterangan'          => 'Utang pembelian - ' . $this->kode_pembelian,
                    'debit'               => 0,
                    'kredit'              => $totalHarga,
                ]);

            } elseif ($this->status_pembayaran === 'cicilan') {

                // KREDIT — Kas (porsi yang sudah dibayar)
                if ($jumlahBayar > 0) {
                    JournalLine::create([
                        'journal_entry_id'    => $entry->id,
                        'chart_of_account_id' => $akunKas->id,
                        'keterangan'          => 'Bayar sebagian - ' . $this->kode_pembelian,
                        'debit'               => 0,
                        'kredit'              => $jumlahBayar,
                    ]);
                }

                // KREDIT — Hutang (sisa)
                $sisa = $totalHarga - $jumlahBayar;
                if ($sisa > 0) {
                    JournalLine::create([
                        'journal_entry_id'    => $entry->id,
                        'chart_of_account_id' => $akunUtang->id,
                        'keterangan'          => 'Sisa utang - ' . $this->kode_pembelian,
                        'debit'               => 0,
                        'kredit'              => $sisa,
                    ]);
                }
            }
        });
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve ChartOfAccount by kode_akun.
     * Lempar exception yang jelas agar tidak crash dengan null->id.
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