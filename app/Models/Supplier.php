<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $last = static::orderBy('id', 'desc')->first();

            $newNumber = $last ? (int) substr($last->kode_supplier, 3) + 1 : 1;

            $model->kode_supplier = 'SUP' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        });
    }
}