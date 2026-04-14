<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensi'; // Nama tabel

    protected $primaryKey = 'id_presensi'; // Primary key custom

    protected $guarded = [];

    // Optional: biar format tanggal & jam lebih rapi
    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime:H:i',
        'jam_keluar' => 'datetime:H:i',
        'is_admin' => 'boolean',
    ];

    // Optional: relasi ke karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }
}