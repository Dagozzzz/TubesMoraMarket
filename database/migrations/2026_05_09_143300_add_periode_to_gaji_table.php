<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gaji', function (Blueprint $table) {
            $table->unsignedTinyInteger('periode_bulan')->nullable()->after('tgl');
            $table->unsignedSmallInteger('periode_tahun')->nullable()->after('periode_bulan');
        });
    }

    public function down(): void
    {
        Schema::table('gaji', function (Blueprint $table) {
            $table->dropColumn(['periode_bulan', 'periode_tahun']);
        });
    }
};
