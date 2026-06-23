<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketTrend extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'market_trend';

    // Kolom yang bisa diisi
    protected $fillable = [

        // Nama trend snack
        'nama_tren',

        // Hasil analisa AI
        'analisis_ai',

        // Referensi gambar / visual
        'referensi_visual',

        // Jenis snack
        'jenis_snack',

        // Rasa yang sedang populer
        'rasa_populer',

        // Bahan utama snack
        'bahan_utama',

        // Warna / tampilan populer
        'warna_populer',

        // Target pasar
        'target_pasar',

        // Range harga
        'range_harga',
    ];

    // Casting otomatis JSON → Array
    protected $casts = [

        'referensi_visual' => 'array',

    ];
}