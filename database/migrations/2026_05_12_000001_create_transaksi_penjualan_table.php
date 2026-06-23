<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penjualan')->unique();
            $table->string('id_customer')->nullable();
            $table->foreign('id_customer')
                ->references('id_customer')
                ->on('customer')
                ->nullOnDelete();
            $table->unsignedBigInteger('id_kasir')->nullable();
            $table->foreign('id_kasir')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->dateTime('tanggal_penjualan');
            $table->enum('status_pembayaran', ['pending', 'lunas', 'gagal', 'expired'])->default('pending');
            $table->string('metode_pembayaran')->default('midtrans');
            $table->string('midtrans_order_id')->nullable()->index();
            $table->string('midtrans_snap_token')->nullable();
            $table->text('midtrans_redirect_url')->nullable();
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_transaksi_penjualan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_transaksi_penjualan');
            $table->foreign('id_transaksi_penjualan')
                ->references('id')
                ->on('transaksi_penjualan')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('id_barang')->nullable();
            $table->foreign('id_barang')
                ->references('id')
                ->on('barang')
                ->nullOnDelete();
            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->string('kategori')->nullable();
            $table->string('satuan')->default('pcs');
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi_penjualan');
        Schema::dropIfExists('transaksi_penjualan');
    }
};
