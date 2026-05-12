<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class COA extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'kategori',
        'saldo_normal',
    ];
}
