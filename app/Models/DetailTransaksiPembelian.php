<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksiPembelian extends Model
{
    protected $table = 'detail_transaksi_pembelian';

    protected $fillable = [
        'id_transaksi_pembelian',
        'nama_produk',
        'satuan',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'subtotal'     => 'decimal:2',
    ];

    public function transaksiPembelian(): BelongsTo
    {
        return $this->belongsTo(TransaksiPembelian::class, 'id_transaksi_pembelian', 'id');
    }
}