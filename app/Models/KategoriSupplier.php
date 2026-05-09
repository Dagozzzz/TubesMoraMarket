<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriSupplier extends Model
{
    protected $table = 'kategori_supplier';

    protected $primaryKey = 'id_kategori';
    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected $fillable = [
        'id_kategori',
        'nama_kategori',
        'deskripsi',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kategori) {

            if (!$kategori->id_kategori) {

                $last = self::orderBy('id_kategori', 'desc')->first();

                if ($last) {
                    $number = (int) substr($last->id_kategori, 3) + 1;
                } else {
                    $number = 1;
                }

                $kategori->id_kategori = 'KTG' . str_pad($number, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}