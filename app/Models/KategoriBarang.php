<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $kode_kategori
 * @property string|null $nama_barang
 * @property string|null $jenis_kategori
 */
class KategoriBarang extends Model
{
    use HasFactory;

    protected $table = 'kategori_barang';

    protected $guarded = [];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (KategoriBarang $model): void {
            /** @var self|null $last */
            $last = static::query()->latest($model->getKeyName())->first();

            $number = $last && $last->kode_kategori !== null
                ? ((int) substr($last->kode_kategori, 3)) + 1
                : 1;

            $model->kode_kategori = 'BRG'.str_pad($number, 3, '0', STR_PAD_LEFT);
        });
    }
}
