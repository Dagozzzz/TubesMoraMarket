<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'kategori',
        'saldo_normal',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }
}
