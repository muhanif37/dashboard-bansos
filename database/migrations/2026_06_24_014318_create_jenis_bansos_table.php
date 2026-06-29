<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_bansos', function (Blueprint $table) {
            $table->id();

            // Kode singkat untuk identifier internal dan display
            // Contoh: 'PKH', 'SEMBAKO', 'PIP', 'KIPK'
            $table->string('kode', 20)->unique();

            $table->string('nama', 100);

            // Deskripsi lengkap program
            // Contoh: 'Program Keluarga Harapan', 'Bantuan Pangan Non-Tunai'
            $table->string('deskripsi', 255)->nullable();

            // Soft disable — jangan hapus, nonaktifkan saja
            // agar data historis tidak orphan
            $table->boolean('aktif')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_bansos');
    }
};