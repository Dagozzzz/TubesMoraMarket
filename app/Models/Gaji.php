<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Gaji extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_slip',
        'karyawan_id',
        'tgl',
        'periode_bulan',
        'periode_tahun',
        'gaji_pokok',
        'tunjangan',
        'potongan',
        'total_gaji',
        'status',
        'keterangan',
    ];

    // Relasi ke Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    // Fungsi otomatis membuat no_slip
    public static function getNoSlip()
    {
        $sql = "SELECT IFNULL(MAX(no_slip), 'SLP-0000000') as no_slip FROM gaji";
        $kode = DB::select($sql);

        foreach ($kode as $k) {
            $kd = $k->no_slip;
        }

        $noawal = substr($kd, -7);
        $noakhir = (int)$noawal + 1;
        $noakhir = 'SLP-' . str_pad($noakhir, 7, "0", STR_PAD_LEFT);
        return $noakhir;
    }
}
