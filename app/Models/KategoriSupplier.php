<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriSupplier extends Model
{
    protected $table      = 'kategori_supplier';
    protected $primaryKey = 'id_kategori';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'id_kategori',
        'nama_kategori',
        'deskripsi',
    ];

    /* ------------------------------------------------------------------
     |  Boot — auto-generate ID format KTG001
     * ------------------------------------------------------------------ */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kategori) {
            if (! $kategori->id_kategori) {
                $last   = self::orderBy('id_kategori', 'desc')->first();
                $number = $last ? (int) substr($last->id_kategori, 3) + 1 : 1;
                $kategori->id_kategori = 'KTG' . str_pad($number, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    /* ------------------------------------------------------------------
     |  Relations
     * ------------------------------------------------------------------ */

    // Satu kategori bisa punya banyak supplier
    // PENTING: foreign key di suppliers adalah id_kategori_supplier
    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class, 'id_kategori_supplier', 'id_kategori');
    }

    // Satu kategori bisa punya banyak transaksi return pembelian
    public function returnPembelian(): HasMany
    {
        return $this->hasMany(ReturnPembelian::class, 'id_kategori_supplier', 'id_kategori');
    }
}