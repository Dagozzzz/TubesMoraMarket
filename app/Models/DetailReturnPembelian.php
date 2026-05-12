<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailReturnPembelian extends Model
{
    protected $table      = 'detail_return_pembelian';
    protected $primaryKey = 'id_detail_return';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'id_detail_return',
        'id_return',
        'nama_produk',
        'deskripsi_produk',
        'jumlah',
        'harga_satuan',
        'subtotal',
        'kondisi',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'subtotal'     => 'decimal:2',
        'jumlah'       => 'integer',
    ];

    /* ------------------------------------------------------------------
     |  Boot
     * ------------------------------------------------------------------ */
    protected static function boot(): void
    {
        parent::boot();

        // Auto-generate ID format DTR00001
        static::creating(function ($model) {
            if (! $model->id_detail_return) {
                $last   = self::orderBy('id_detail_return', 'desc')->first();
                $number = $last ? (int) substr($last->id_detail_return, 3) + 1 : 1;
                $model->id_detail_return = 'DTR' . str_pad($number, 5, '0', STR_PAD_LEFT);
            }
            // Hitung subtotal sebelum insert
            $model->subtotal = (int) $model->jumlah * (float) $model->harga_satuan;
        });

        // Hitung ulang subtotal saat update
        static::updating(function ($model) {
            $model->subtotal = (int) $model->jumlah * (float) $model->harga_satuan;
        });

        // Update total header setelah detail disimpan
        static::saved(function ($model) {
            optional($model->returnPembelian)->hitungTotal();
        });

        // Update total header setelah detail dihapus
        static::deleted(function ($model) {
            optional($model->returnPembelian)->hitungTotal();
        });
    }

    /* ------------------------------------------------------------------
     |  Relations
     * ------------------------------------------------------------------ */
    public function returnPembelian(): BelongsTo
    {
        return $this->belongsTo(ReturnPembelian::class, 'id_return', 'id_return');
    }
}