<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBarang extends Model
{
    use HasFactory;

    protected $table = 'kategori_barang'; 

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $last = static::orderBy('id', 'desc')->first();
            $number = $last ? (intval(substr($last->kode_kategori, 3)) + 1) : 1;
            $model->kode_kategori = 'BRG' . str_pad($number, 3, '0', STR_PAD_LEFT);
        });
    }
}