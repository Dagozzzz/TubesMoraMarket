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
        Schema::create('detail_return_pembelian', function (Blueprint $table) {

            $table->string('id_detail_return')->primary();

            $table->string('id_return');

            $table->string('nama_produk');

            $table->text('deskripsi_produk')->nullable();

            $table->integer('jumlah');

            $table->decimal('harga_satuan', 15, 2);

            $table->decimal('subtotal', 15, 2);

            $table->string('kondisi')->nullable();

            $table->timestamps();

            // Foreign Key Return Pembelian
            $table->foreign('id_return')
                  ->references('id_return')
                  ->on('return_pembelian')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_return_pembelian');
    }
};