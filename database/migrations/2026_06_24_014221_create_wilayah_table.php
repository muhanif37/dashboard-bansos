<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('kode_dagri', 20)->unique();
            $table->string('nama', 150);
            $table->enum('level', ['provinsi', 'kabupaten', 'kecamatan', 'desa']);
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('wilayah')
                  ->nullOnDelete();
            $table->string('path', 100)->nullable();
            $table->timestamps();
            $table->index('level');
            $table->index('parent_id');
            $table->index('path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah');
    }
};