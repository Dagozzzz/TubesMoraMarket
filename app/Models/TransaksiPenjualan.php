<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiPenjualan extends Model
{
    protected $table = 'transaksi_penjualan';

    protected $fillable = [
        'kode_penjualan',
        'id_customer',
        'id_kasir',
        'tanggal_penjualan',
        'status_pembayaran',
        'metode_pembayaran',
        'midtrans_order_id',
        'midtrans_snap_token',
        'midtrans_redirect_url',
        'total_harga',
        'catatan',
    ];

    protected $casts = [
        'tanggal_penjualan' => 'datetime',
        'total_harga' => 'decimal:2',
    ];

    public static function generateKodePenjualan(): string
    {
        $last = self::latest('id')->first();
        $number = $last ? ((int) substr($last->kode_penjualan, 4)) + 1 : 1;

        return 'PJL-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($penjualan) {
            if (! $penjualan->kode_penjualan) {
                $penjualan->kode_penjualan = self::generateKodePenjualan();
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_kasir', 'id');
    }

    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksiPenjualan::class, 'id_transaksi_penjualan', 'id');
    }
}
