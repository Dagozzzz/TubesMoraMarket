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
        Schema::create('transaksi_penjualan_ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('judul')->default('Analisis AI Penjualan');
            $table->unsignedInteger('total_transaksi')->default(0);
            $table->decimal('total_penjualan', 15, 2)->default(0);
            $table->unsignedInteger('total_item')->default(0);
            $table->unsignedInteger('total_produk')->default(0);
            $table->date('periode_awal')->nullable();
            $table->date('periode_akhir')->nullable();
            $table->text('ringkasan');
            $table->longText('analisis');
            $table->text('saran')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_penjualan_ai_analyses');
    }
};
