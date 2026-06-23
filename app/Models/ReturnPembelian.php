<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReturnPembelian extends Model
{
    protected $table      = 'return_pembelian';
    protected $primaryKey = 'id_return';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'id_return',
        'id_supplier',
        'id_kategori_supplier',
        'tanggal_return',
        'status',
        'alasan_return',
        'total_nilai_return',
        'catatan',
    ];

    protected $casts = [
        'tanggal_return'     => 'date',
        'total_nilai_return' => 'decimal:2',
    ];

    // -------------------------------------------------------------------------
    // Boot — auto-generate ID format RTN00001
    // -------------------------------------------------------------------------

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id_return) {
                $last   = self::orderBy('id_return', 'desc')->first();
                $number = $last ? (int) substr($last->id_return, 3) + 1 : 1;
                $model->id_return = 'RTN' . str_pad($number, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id');
    }

    public function kategoriSupplier(): BelongsTo
    {
        return $this->belongsTo(KategoriSupplier::class, 'id_kategori_supplier', 'id_kategori');
    }

    public function detailReturn(): HasMany
    {
        return $this->hasMany(DetailReturnPembelian::class, 'id_return', 'id_return');
    }

    // -------------------------------------------------------------------------
    // Helper — hitung total dari semua detail
    // -------------------------------------------------------------------------

    public function hitungTotal(): void
    {
        $total = $this->detailReturn()
            ->selectRaw('SUM(jumlah * harga_satuan) as total')
            ->value('total') ?? 0;

        $this->updateQuietly(['total_nilai_return' => $total]);
    }

    // -------------------------------------------------------------------------
    // Journal Generation
    // -------------------------------------------------------------------------

    /**
     * Buat atau perbarui jurnal otomatis untuk return pembelian.
     *
     * Jurnal hanya dibuat ketika status = 'disetujui'.
     *
     * Jurnal:
     *   DEBIT  → Hutang Usaha Supplier (2101) — hutang berkurang
     *   KREDIT → Persediaan Barang Dagang (1130) — persediaan keluar
     *
     * Jika status berubah dari 'disetujui' ke status lain (misal: ditolak),
     * jurnal yang ada akan dihapus.
     */
    public function buatJurnal(): void
    {
        $nomorJurnal = 'RTN-' . $this->id_return;

        // Jika status bukan 'disetujui', hapus jurnal lama lalu keluar
        if ($this->status !== 'disetujui') {
            JournalEntry::where('nomor_jurnal', $nomorJurnal)->delete();
            return;
        }

        $totalReturn = (float) $this->total_nilai_return;

        // Tidak ada yang direturn
        if ($totalReturn <= 0) {
            return;
        }

        $akunUtang      = $this->resolveAccount('2101'); // Hutang Usaha Supplier
        $akunPersediaan = $this->resolveAccount('1130'); // Persediaan Barang Dagang

        DB::transaction(function () use ($nomorJurnal, $totalReturn, $akunUtang, $akunPersediaan) {
            // Hapus jurnal lama agar tidak duplikat saat edit
            JournalEntry::where('nomor_jurnal', $nomorJurnal)->delete();

            $namaSupplier = optional($this->supplier)->nama_supplier ?? 'Supplier';

            $entry = JournalEntry::create([
                'nomor_jurnal' => $nomorJurnal,
                'tanggal'      => $this->tanggal_return,
                'keterangan'   => 'Return pembelian ke ' . $namaSupplier . ' - ' . $this->id_return,
                'status'       => 'posted',
            ]);

            // DEBIT — Hutang Usaha Supplier (hutang berkurang)
            JournalLine::create([
                'journal_entry_id'    => $entry->id,
                'chart_of_account_id' => $akunUtang->id,
                'keterangan'          => 'Pengurangan hutang return - ' . $this->id_return,
                'debit'               => $totalReturn,
                'kredit'              => 0,
            ]);

            // KREDIT — Persediaan Barang (persediaan keluar)
            JournalLine::create([
                'journal_entry_id'    => $entry->id,
                'chart_of_account_id' => $akunPersediaan->id,
                'keterangan'          => 'Keluar persediaan return - ' . $this->id_return,
                'debit'               => 0,
                'kredit'              => $totalReturn,
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