<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasi_bansos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wilayah_id')
                  ->constrained('wilayah')
                  ->restrictOnDelete();

            $table->foreignId('jenis_bansos_id')
                  ->constrained('jenis_bansos')
                  ->restrictOnDelete();

            $table->foreignId('periode_id')
                  ->constrained('periode')
                  ->restrictOnDelete();

            // === PENYALURAN (dana sudah di bank Himbara) ===
            $table->unsignedInteger('penyaluran_kpm')->default(0);
            $table->unsignedBigInteger('penyaluran_nominal')->default(0);

            // === PENCAIRAN / REALISASI (diterima KPM) ===
            $table->unsignedInteger('jumlah_kpm')->default(0);
            $table->unsignedBigInteger('nominal')->default(0);

            $table->date('tanggal_realisasi')->nullable();
            $table->string('sumber_data', 255)->nullable();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->unique(['wilayah_id', 'jenis_bansos_id', 'periode_id']);
            $table->index(['jenis_bansos_id', 'periode_id']);
            $table->index(['wilayah_id', 'periode_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_bansos');
    }
};