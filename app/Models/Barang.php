<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori',
        'satuan',
        'harga_beli',
        'harga_jual',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Barang $barang): void {
            if (blank($barang->kode_barang)) {
                $barang->kode_barang = self::generateKodeBarang();
            }
        });
    }

    public static function generateKodeBarang(): string
    {
        $lastBarang = self::query()
            ->where('kode_barang', 'like', 'BRG%')
            ->orderByDesc('id')
            ->first();

        $lastNumber = $lastBarang ? (int) substr($lastBarang->kode_barang, 3) : 0;

        return 'BRG' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
}
