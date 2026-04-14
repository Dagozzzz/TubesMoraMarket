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
        Schema::create('customers', function (Blueprint $table) {
            $table->id('id_customer'); // ID Customer (custom primary key)
            $table->string('nama_customer'); // Nama Customer
            $table->string('email')->unique(); // Email Customer
            $table->string('no_telepon'); // Nomor Telepon (BARU)
            $table->string('no_transaksi'); // Nomor Transaksi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};