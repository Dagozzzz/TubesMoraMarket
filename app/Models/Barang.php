<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $kode_barang
 * @property string $nama_barang
 * @property string $kategori
 * @property string $satuan
 * @property int|float|string $harga_beli
 * @property int|float|string $harga_jual
 */
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
            $barang->kode_barang = static::generateKodeBarang();
        });
    }

    public static function generateKodeBarang(): string
    {
        $barang = new static;

        /** @var self|null $lastBarang */
        $lastBarang = static::query()->latest($barang->getKeyName())->first();

        if ($lastBarang && $lastBarang->kode_barang !== null) {
            $lastNumber = (int) substr($lastBarang->kode_barang, 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'BRG'.str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
