<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {

            $table->id();

            $table->string('kode_supplier')->unique();
            $table->string('nama_supplier');
            $table->string('no_handphone');

            // samakan dengan primary key kategori
            $table->string('id_kategori_supplier')->nullable();

            // foreign key manual
            $table->foreign('id_kategori_supplier')
                ->references('id_kategori')
                ->on('kategori_supplier')
                ->nullOnDelete();

            $table->string('gambar')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};