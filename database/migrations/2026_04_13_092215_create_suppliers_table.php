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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string("kode_supplier");
            $table->string('nama_supplier');
            $table->string('no_handphone');
            $table->enum('kategori', [
                'Food & Beverage',
                'Household',
                'Personal Care',
                'Frozen Food',
                'General Merchandise'
            ]);
            $table->string('gambar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};