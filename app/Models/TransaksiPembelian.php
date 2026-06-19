<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $kode_pembelian
 * @property int $id_supplier
 * @property Carbon $tanggal_pembelian
 * @property string $status_pembayaran
 * @property string $metode_pembayaran
 * @property int|float|string $total_harga
 * @property int|float|string $jumlah_bayar
 * @property int|float|string $kembalian
 * @property string|null $catatan
 */
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
        'total_harga' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id');
    }

    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksiPembelian::class, 'id_transaksi_pembelian', 'id');
    }

    public function buatJurnal(): void
    {
        $total = (float) $this->total_harga;

        if ($total <= 0) {
            return;
        }

        $persediaan = ChartOfAccount::where('kode_akun', '1130')->first();
        $kasAtauBank = ChartOfAccount::where(
            'kode_akun',
            $this->metode_pembayaran === 'transfer' ? '1110' : '1101'
        )->first();
        $hutangSupplier = ChartOfAccount::where('kode_akun', '2101')->first();

        if (! $persediaan || ! $kasAtauBank || ! $hutangSupplier) {
            return;
        }

        $jumlahBayar = max(0, (float) $this->jumlah_bayar);
        $dibayar = min($jumlahBayar, $total);
        $sisaHutang = max(0, $total - $dibayar);

        $journalEntry = JournalEntry::updateOrCreate(
            ['nomor_jurnal' => $this->kode_pembelian],
            [
                'tanggal' => $this->tanggal_pembelian,
                'keterangan' => 'Pembelian barang '.$this->kode_pembelian,
                'status' => 'posted',
            ],
        );

        $journalEntry->lines()->delete();

        $journalEntry->lines()->create([
            'chart_of_account_id' => $persediaan->id,
            'keterangan' => 'Persediaan barang dagang',
            'debit' => $total,
            'kredit' => 0,
        ]);

        if ($dibayar > 0) {
            $journalEntry->lines()->create([
                'chart_of_account_id' => $kasAtauBank->id,
                'keterangan' => 'Pembayaran pembelian',
                'debit' => 0,
                'kredit' => $dibayar,
            ]);
        }

        if ($sisaHutang > 0) {
            $journalEntry->lines()->create([
                'chart_of_account_id' => $hutangSupplier->id,
                'keterangan' => 'Hutang usaha supplier',
                'debit' => 0,
                'kredit' => $sisaHutang,
            ]);
        }
    }
}
