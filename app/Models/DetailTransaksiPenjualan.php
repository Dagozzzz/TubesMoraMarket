<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksiPenjualan extends Model
{
    protected $table = 'detail_transaksi_penjualan';

    protected $fillable = [
        'id_transaksi_penjualan',
        'id_barang',
        'kode_barang',
        'nama_barang',
        'kategori',
        'satuan',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function transaksiPenjualan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'id_transaksi_penjualan', 'id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id');
    }
}
