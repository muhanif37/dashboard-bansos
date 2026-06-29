<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah_flag', function (Blueprint $table) {
            // Composite PK: satu wilayah bisa punya banyak flag,
            // dan flag yang sama bisa aktif di tahun berbeda
            $table->foreignId('wilayah_id')
                  ->constrained('wilayah')
                  ->cascadeOnDelete();

            // Enum untuk jenis kebijakan/program yang mem-flag wilayah.
            // Saat ini hanya level kabupaten/kota yang aktif.
            // Untuk menambah flag baru (misal level desa), tambah nilai enum
            // di sini lalu buat migration ALTER TABLE untuk production.
            $table->enum('jenis_flag', [
                'prioritas_88',       // 88 Kab/Kota Prioritas Kemiskinan — level KAB/KOTA
                // 'kepmenko_6_2026', // Kepmenko PM No.6 Tahun 2026 — level DESA (belum aktif)
            ]);

            // Tahun keberlakuan — penting karena keanggotaan bisa berubah tiap tahun
            $table->smallInteger('tahun_berlaku')->unsigned();

            // Composite primary key
            $table->primary(['wilayah_id', 'jenis_flag', 'tahun_berlaku']);

            // Index untuk query: "tampilkan semua wilayah dengan flag X di tahun Y"
            $table->index(['jenis_flag', 'tahun_berlaku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah_flag');
    }
};