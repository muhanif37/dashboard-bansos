<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summary_bansos', function (Blueprint $table) {
            // Composite PK — tidak ada auto-increment
            $table->foreignId('wilayah_id')
                  ->constrained('wilayah')
                  ->cascadeOnDelete();

            $table->foreignId('jenis_bansos_id')
                  ->constrained('jenis_bansos')
                  ->cascadeOnDelete();

            $table->foreignId('periode_id')
                  ->constrained('periode')
                  ->cascadeOnDelete();

            // === SP2D / ALOKASI / TARGET ===
            $table->unsignedInteger('target_kpm')->default(0);
            $table->unsignedBigInteger('target_nominal')->default(0);

            // === PENYALURAN (dana di bank Himbara) ===
            $table->unsignedInteger('penyaluran_kpm')->default(0);
            $table->unsignedBigInteger('penyaluran_nominal')->default(0);

            // === PENCAIRAN / REALISASI (diterima KPM) ===
            $table->unsignedInteger('realisasi_kpm')->default(0);
            $table->unsignedBigInteger('realisasi_nominal')->default(0);

            // === SELISIH & PERSENTASE ===
            $table->integer('selisih_kpm')->default(0);
            $table->bigInteger('selisih_nominal')->default(0);
            $table->decimal('pct_kpm', 10, 4)->default(0);
            $table->decimal('pct_nominal', 10, 4)->default(0);

            // Persentase penyaluran terhadap target
            $table->decimal('pct_penyaluran_kpm', 10, 4)->default(0);
            $table->decimal('pct_penyaluran_nominal', 10, 4)->default(0);

            $table->timestamp('computed_at')->nullable();

            $table->primary(['wilayah_id', 'jenis_bansos_id', 'periode_id']);
            $table->index(['jenis_bansos_id', 'periode_id']);
            $table->index(['wilayah_id', 'jenis_bansos_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summary_bansos');
    }
};