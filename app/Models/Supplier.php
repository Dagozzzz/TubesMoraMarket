<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    protected $table = 'supplier'; // ← tambah ini

    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'no_handphone',
        'id_kategori_supplier',
    
    ];

    public function kategoriSupplier(): BelongsTo
    {
        return $this->belongsTo(KategoriSupplier::class, 'id_kategori_supplier', 'id');
    }
}