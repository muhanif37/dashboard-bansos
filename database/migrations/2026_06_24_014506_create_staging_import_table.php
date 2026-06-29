<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staging_import', function (Blueprint $table) {
            $table->id();

            // Nama file asli yang diupload — untuk referensi admin
            $table->string('nama_file', 255);

            // Konteks periode yang diklaim file ini
            // Diisi admin saat upload sebelum file diproses
            $table->foreignId('periode_id')
                  ->nullable()
                  ->constrained('periode')
                  ->nullOnDelete();

            $table->foreignId('jenis_bansos_id')
                  ->nullable()
                  ->constrained('jenis_bansos')
                  ->nullOnDelete();

            // Raw data per baris dari file xlsx, disimpan sebagai JSON
            // Contoh: {"kode":"1101","provinsi":"Aceh","kabupaten":"Aceh Selatan",
            //          "target_kpm_pkh":12170,"realisasi_kpm_pkh":12148,...}
            $table->json('raw_data');

            // Status pipeline validasi per baris
            $table->enum('status', [
                'pending',    // Baru diupload, belum divalidasi
                'valid',      // Lolos validasi, siap dipindah ke produksi
                'invalid',    // Gagal validasi — lihat error_log
                'imported',   // Sudah berhasil dipindah ke tabel produksi
            ])->default('pending');

            // Detail error validasi dalam format JSON array
            // Contoh: ["Kode Kemendagri '9999' tidak ditemukan di tabel wilayah",
            //          "Nominal tidak boleh negatif"]
            $table->json('error_log')->nullable();

            $table->foreignId('imported_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Kapan baris ini berhasil dipindah ke produksi
            $table->timestamp('imported_at')->nullable();

            $table->timestamps();

            // Index untuk filter per batch import (by nama_file + status)
            $table->index(['nama_file', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staging_import');
    }
};