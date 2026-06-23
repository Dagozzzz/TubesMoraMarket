<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangAiAnalysis extends Model
{
    protected $table = 'barang_ai_analyses';

    protected $fillable = [
        'judul',
        'total_barang',
        'total_kategori',
        'ringkasan',
        'analisis',
        'saran',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
