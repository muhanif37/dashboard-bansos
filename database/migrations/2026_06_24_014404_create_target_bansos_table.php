<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_bansos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wilayah_id')
                  ->constrained('wilayah')
                  ->restrictOnDelete(); // Jangan hapus wilayah kalau masih ada target

            $table->foreignId('jenis_bansos_id')
                  ->constrained('jenis_bansos')
                  ->restrictOnDelete();

            $table->foreignId('periode_id')
                  ->constrained('periode')
                  ->restrictOnDelete();

            // Jumlah Keluarga Penerima Manfaat yang ditargetkan
            $table->unsignedInteger('jumlah_kpm');

            // Nilai nominal dalam rupiah
            // Gunakan bigInteger — nominal bansos bisa ratusan miliar per kabupaten
            // Hindari decimal/float untuk nilai uang
            $table->unsignedBigInteger('nominal');

            // Audit trail
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // Constraint: satu wilayah hanya boleh punya satu target
            // per jenis bansos per periode
            $table->unique(['wilayah_id', 'jenis_bansos_id', 'periode_id']);

            // Index untuk query dashboard — filter by periode + jenis
            $table->index(['jenis_bansos_id', 'periode_id']);
            $table->index(['wilayah_id', 'periode_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_bansos');
    }
};