<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_karyawan',
        'nama',
        'email',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'nik',
        'nip',
        'jenis_kelamin',
        'jabatan_id', // pakai ini kalau pakai relasi
        'foto',
    ];

    /**
     * Auto generate kode karyawan
     */
    protected static function booted()
    {
        static::creating(function ($karyawan) {
            $last = self::latest()->first();

            $number = $last ? (int) substr($last->kode_karyawan, 3) : 0;
            $number++;

            $karyawan->kode_karyawan = 'KRY' . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Relasi ke jabatan
     */
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }
    protected $appends = ['foto_url'];

public function getFotoUrlAttribute()
{
    return $this->foto ? asset('storage/' . $this->foto) : null;
}
}