<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnPembelian extends Model
{
    protected $table      = 'return_pembelian';
    protected $primaryKey = 'id_return';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'id_return',
        'id_supplier',
        'id_kategori_supplier',
        'tanggal_return',
        'status',
        'alasan_return',
        'total_nilai_return',
        'catatan',
    ];

    protected $casts = [
        'tanggal_return'     => 'date',
        'total_nilai_return' => 'decimal:2',
    ];

    /* ------------------------------------------------------------------
     |  Boot — auto-generate ID format RTN00001
     * ------------------------------------------------------------------ */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id_return) {
                $last   = self::orderBy('id_return', 'desc')->first();
                $number = $last ? (int) substr($last->id_return, 3) + 1 : 1;
                $model->id_return = 'RTN' . str_pad($number, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /* ------------------------------------------------------------------
     |  Relations
     * ------------------------------------------------------------------ */

    // Relasi ke suppliers (primary key = id)
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id');
    }

    // Relasi ke kategori_supplier (primary key = id_kategori)
    public function kategoriSupplier(): BelongsTo
    {
        return $this->belongsTo(KategoriSupplier::class, 'id_kategori_supplier', 'id_kategori');
    }

    // Relasi ke detail item return
    public function detailReturn(): HasMany
    {
        return $this->hasMany(DetailReturnPembelian::class, 'id_return', 'id_return');
    }

    /* ------------------------------------------------------------------
     |  Helper — hitung total dari semua detail
     * ------------------------------------------------------------------ */
    public function hitungTotal(): void
    {
        $total = $this->detailReturn()
            ->selectRaw('SUM(jumlah * harga_satuan) as total')
            ->value('total') ?? 0;

        $this->updateQuietly(['total_nilai_return' => $total]);
    }
}