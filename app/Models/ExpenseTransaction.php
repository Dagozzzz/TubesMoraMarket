<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseTransaction extends Model
{
    protected $fillable = [
        'no_transaksi',
        'tanggal',
        'chart_of_account_id',
        'deskripsi',
        'jumlah',
        'metode_pembayaran',
        'dibayar_kepada',
        'nomor_bukti',
        'bukti_pembayaran',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }
}
