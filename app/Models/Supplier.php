<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'no_handphone',
        'id_kategori_supplier',
        'gambar',
    ];

    public function kategoriSupplier()
    {
        return $this->belongsTo(
            KategoriSupplier::class,
            'id_kategori_supplier',
            'id_kategori'
        );
    }
}