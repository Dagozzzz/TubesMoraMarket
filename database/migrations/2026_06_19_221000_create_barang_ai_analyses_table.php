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
        Schema::create('barang_ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('judul')->default('Analisis AI Master Barang');
            $table->unsignedInteger('total_barang')->default(0);
            $table->unsignedInteger('total_kategori')->default(0);
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
        Schema::dropIfExists('barang_ai_analyses');
    }
};
