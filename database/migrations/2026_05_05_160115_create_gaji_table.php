<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('gaji', function (Blueprint $table) {
            $table->id();
            $table->string('no_slip')->unique();
            $table->foreignId('karyawan_id')->constrained('karyawan')->onDelete('cascade');
            $table->date('tgl');
            $table->integer('gaji_pokok')->default(0);
            $table->integer('tunjangan')->default(0);
            $table->integer('potongan')->default(0);
            $table->integer('total_gaji')->default(0);
            $table->string('status')->default('draft');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('gaji');
    }
};
