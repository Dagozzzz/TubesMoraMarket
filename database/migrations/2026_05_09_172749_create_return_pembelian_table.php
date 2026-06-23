<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Tabel Header Return ───────────────────────────────────────────
        Schema::create('return_pembelian', function (Blueprint $table) {
            $table->string('id_return')->primary();

            // Foreign key ke suppliers (pakai id bawaan Laravel)
            $table->unsignedBigInteger('id_supplier')->nullable();
            $table->foreign('id_supplier')
                  ->references('id')
                  ->on('supplier')
                  ->nullOnDelete();

            // Foreign key ke kategori_supplier
            $table->string('id_kategori_supplier')->nullable();
            $table->foreign('id_kategori_supplier')
                  ->references('id_kategori')
                  ->on('kategori_supplier')
                  ->nullOnDelete();

            $table->date('tanggal_return');
            $table->enum('status', ['draft', 'diajukan', 'disetujui', 'ditolak'])->default('draft');
            $table->text('alasan_return');
            $table->decimal('total_nilai_return', 15, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }
    //     // ── Tabel Detail Item Return ──────────────────────────────────────
    //     Schema::create('detail_return_pembelian', function (Blueprint $table) {
    //         $table->string('id_detail_return')->primary();

    //         $table->string('id_return');
    //         $table->foreign('id_return')
    //               ->references('id_return')
    //               ->on('return_pembelian')
    //               ->cascadeOnDelete();

    //         $table->string('nama_produk');
    //         $table->text('deskripsi_produk')->nullable();
    //         $table->integer('jumlah');
    //         $table->decimal('harga_satuan', 15, 2);
    //         $table->decimal('subtotal', 15, 2)->default(0);
    //         $table->enum('kondisi', [
    //             'rusak',
    //             'cacat_produksi',
    //             'salah_kirim',
    //             'kadaluarsa',
    //             'lainnya',
    //         ])->default('rusak');
    //         $table->timestamps();
    //     });

    public function down(): void
    {
        Schema::dropIfExists('detail_return_pembelian');
        Schema::dropIfExists('return_pembelian');
    }
};