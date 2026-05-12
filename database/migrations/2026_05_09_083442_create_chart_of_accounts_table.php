<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {

            $table->id();

            // kode akun contoh: 1001, 2001, dst
            $table->string('kode_akun')->unique();

            // nama akun
            $table->string('nama_akun');

            // kategori akun
            $table->enum('kategori', [
                'Aset',
                'Liabilitas',
                'Ekuitas',
                'Pendapatan',
                'Harga Pokok Penjualan',
                'Beban'
            ]);

            // saldo normal akun
            $table->enum('saldo_normal', [
                'Debit',
                'Kredit'
            ]);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
