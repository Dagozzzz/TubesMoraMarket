<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('chart_of_accounts') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE chart_of_accounts MODIFY kategori ENUM('Aset', 'Liabilitas', 'Ekuitas', 'Pendapatan', 'Harga Pokok Penjualan', 'Beban') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('chart_of_accounts') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE chart_of_accounts MODIFY kategori ENUM('Aset', 'Liabilitas', 'Ekuitas', 'Pendapatan', 'Beban') NOT NULL");
    }
};
