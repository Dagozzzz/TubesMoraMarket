<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id');
    }

    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksiPembelian::class, 'id_transaksi_pembelian', 'id');
    }
}
