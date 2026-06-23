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
        Schema::create('market_trend', function (Blueprint $table) {
            $table->id();

            // Nama tren snack
            $table->string('nama_tren');
            // Contoh:
            // "Tren Snack Basah Pedas 2026"
            // "Tren Snack Kering Gurih 2026"

            // Hasil analisa AI Gemini
            $table->text('analisis_ai');

            // Referensi gambar / visual produk
            $table->json('referensi_visual')->nullable();

            // Jenis snack
            $table->enum('jenis_snack', [
                'Snack Basah',
                'Snack Kering'
            ]);

            // Rasa yang sedang populer
            $table->string('rasa_populer')->nullable();
            // Contoh:
            // Pedas Daun Jeruk
            // Keju Jagung Bakar
            // Coklat Lumer

            // Bahan utama snack
            $table->string('bahan_utama')->nullable();
            // Contoh:
            // Singkong
            // Kentang
            // Tepung Tapioka
            // Ayam
            // Keju

            // Warna / tampilan yang menarik
            $table->string('warna_populer')->nullable();
            // Contoh:
            // Coklat Gold
            // Merah Cabe
            // Hijau Matcha

            // Target pasar
            $table->string('target_pasar')->nullable();
            // Contoh:
            // Mahasiswa
            // Anak Sekolah
            // Pekerja Kantoran

            // Harga rekomendasi
            $table->string('range_harga')->nullable();
            // Contoh:
            // 5k - 10k
            // 15k - 25k

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_trend');
    }
};