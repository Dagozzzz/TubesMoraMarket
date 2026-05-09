<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'waktu_masuk',
        'waktu_keluar',
        'status',
        'keterangan',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    /**
     * Ambil ringkasan presensi bulanan untuk karyawan tertentu
     */
    public static function ringkasanBulanan($karyawanId, $bulan, $tahun)
    {
        $query = self::where('karyawan_id', $karyawanId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        return [
            'hadir' => (clone $query)->where('status', 'Hadir')->count(),
            'izin'  => (clone $query)->where('status', 'Izin')->count(),
            'sakit' => (clone $query)->where('status', 'Sakit')->count(),
            'alpa'  => (clone $query)->where('status', 'Alpa')->count(),
            'total' => (clone $query)->count(),
        ];
    }
}
