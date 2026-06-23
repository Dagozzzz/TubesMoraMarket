<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiPenjualanAiAnalysis extends Model
{
    protected $table = 'transaksi_penjualan_ai_analyses';

    protected $fillable = [
        'judul',
        'total_transaksi',
        'total_penjualan',
        'total_item',
        'total_produk',
        'periode_awal',
        'periode_akhir',
        'ringkasan',
        'analisis',
        'saran',
        'metadata',
    ];

    protected $casts = [
        'total_penjualan' => 'decimal:2',
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
        'metadata' => 'array',
    ];
}
