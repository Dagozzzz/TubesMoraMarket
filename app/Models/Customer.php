<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan di database[cite: 2]
    protected $table = 'customers';

    // Menentukan primary key kustom[cite: 2]
    protected $primaryKey = 'id_customer';

    // Karena id_customer menggunakan format string 'FF01', auto-increment harus dimatikan
    public $incrementing = false;

    // Menentukan tipe data primary key sebagai string
    protected $keyType = 'string';

    // Memungkinkan semua kolom diisi secara mass-assignment[cite: 2]
    protected $guarded = [];

    public function transaksiPenjualan(): HasMany
    {
        return $this->hasMany(TransaksiPenjualan::class, 'id_customer', 'id_customer');
    }
}
