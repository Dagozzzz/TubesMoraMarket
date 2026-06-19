<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();

            // nomor referensi jurnal, contoh: JU-2024-0001
            $table->string('nomor_jurnal')->unique();

            // tanggal transaksi
            $table->date('tanggal');

            // keterangan/deskripsi transaksi
            $table->text('keterangan');

            // status: draft atau posted
            $table->enum('status', ['draft', 'posted'])->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};