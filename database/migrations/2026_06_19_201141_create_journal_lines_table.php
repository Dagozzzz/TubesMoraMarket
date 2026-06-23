<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();

            // relasi ke journal_entries
            $table->foreignId('journal_entry_id')
                ->constrained('journal_entries')
                ->onDelete('cascade');

            // relasi ke chart_of_accounts
            $table->foreignId('chart_of_account_id')
                ->constrained('chart_of_accounts')
                ->onDelete('restrict');

            // keterangan per baris (opsional)
            $table->string('keterangan')->nullable();

            // nilai debit (0 jika sisi kredit)
            $table->decimal('debit', 15, 2)->default(0);

            // nilai kredit (0 jika sisi debit)
            $table->decimal('kredit', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
    }
};