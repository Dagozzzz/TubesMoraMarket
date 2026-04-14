<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang'; // 🔥 WAJIB (fix error)

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori',
        'satuan',
        'harga_beli',
        'harga_jual',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($barang) {

            $lastBarang = self::orderBy('id', 'desc')->first();

            if ($lastBarang) {
                $lastNumber = (int) substr($lastBarang->kode_barang, 3);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            $barang->kode_barang = 'BRG' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        });
    }
}