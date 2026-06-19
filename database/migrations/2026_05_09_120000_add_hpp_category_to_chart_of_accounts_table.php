<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE chart_of_accounts MODIFY kategori ENUM('Aset', 'Liabilitas', 'Ekuitas', 'Pendapatan', 'Harga Pokok Penjualan', 'Beban') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE chart_of_accounts MODIFY kategori ENUM('Aset', 'Liabilitas', 'Ekuitas', 'Pendapatan', 'Beban') NOT NULL");
    }
};
