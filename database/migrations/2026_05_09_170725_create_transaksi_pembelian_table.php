<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_pembelian', function (Blueprint $table) {

            $table->id();

            $table->string('kode_pembelian')->unique();

            // foreign key ke tabel suppliers
            $table->unsignedBigInteger('id_supplier');

            $table->foreign('id_supplier')
                ->references('id')
                ->on('supplier')
                ->restrictOnDelete();

            $table->date('tanggal_pembelian');

            $table->enum('status_pembayaran', [
                'lunas',
                'belum_lunas',
                'cicilan'
            ])->default('belum_lunas');

            $table->enum('metode_pembayaran', [
                'tunai',
                'transfer',
                'cek'
            ])->default('tunai');

            $table->decimal('total_harga', 15, 2)->default(0);
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->decimal('kembalian', 15, 2)->default(0);

            $table->text('catatan')->nullable();

            $table->timestamps();
        });

        Schema::create('detail_transaksi_pembelian', function (Blueprint $table) {

            $table->id();

            // foreign key transaksi pembelian
            $table->unsignedBigInteger('id_transaksi_pembelian');

            $table->foreign('id_transaksi_pembelian')
                ->references('id')
                ->on('transaksi_pembelian')
                ->cascadeOnDelete();

            $table->string('nama_produk');

            $table->string('satuan')->default('pcs');

            $table->integer('jumlah');

            $table->decimal('harga_satuan', 15, 2);

            $table->decimal('subtotal', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi_pembelian');
        Schema::dropIfExists('transaksi_pembelian');
    }
};