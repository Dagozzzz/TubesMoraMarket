<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers'; // Nama tabel

    protected $primaryKey = 'id_customer'; // Custom primary key

    protected $guarded = []; // Boleh isi semua field
}