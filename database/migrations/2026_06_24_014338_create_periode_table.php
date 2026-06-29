<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tahun')->unsigned();

            // 1–4, granularitas per triwulan
            // TW I   = 1 (Jan-Mar)
            // TW II  = 2 (Apr-Jun)
            // TW III = 3 (Jul-Sep)
            // TW IV  = 4 (Okt-Des)
            $table->tinyInteger('triwulan')->unsigned();

            // Label human-readable untuk display di UI
            // Contoh: 'TW I 2025', 'TW II 2026'
            $table->string('label', 30);

            $table->unique(['tahun', 'triwulan']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode');
    }
};