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
        Schema::create('presensi', function (Blueprint $table) {
            $table->id('id_presensi'); // ID Presensi
            $table->unsignedBigInteger('id_karyawan'); // ID Karyawan
            $table->string('nama'); // Nama Karyawan
            $table->date('tanggal'); // Tanggal Presensi
            $table->time('jam_masuk')->nullable(); // Jam Masuk
            $table->time('jam_keluar')->nullable(); // Jam Keluar
            $table->enum('status_kehadiran', [
                'Hadir',
                'Izin',
                'Sakit',
                'Alpha'
            ]); // Status Kehadiran
            $table->timestamps();

            // Optional: relasi ke tabel karyawan (kalau ada)
            // $table->foreign('id_karyawan')->references('id')->on('karyawan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};