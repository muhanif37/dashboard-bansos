<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Wilayah;

/**
 * WilayahSeeder — Import data wilayah Kemendagri ke database
 *
 * CARA PENGGUNAAN:
 * 1. Siapkan file CSV data wilayah Kemendagri di: storage/app/seeds/wilayah_kemendagri.csv
 * 2. Format CSV yang diharapkan (header baris pertama):
 *    kode_dagri,nama,level,kode_parent
 *
 *    Contoh isi:
 *    11,Aceh,provinsi,
 *    1101,Kabupaten Aceh Selatan,kabupaten,11
 *    110101,Kecamatan Bakongan,kecamatan,1101
 *    1101010001,Desa Keude Bakongan,desa,110101
 *
 * 3. Jalankan:
 *    php artisan db:seed --class=WilayahSeeder
 *
 * CATATAN PENTING:
 * - Seeder ini menggunakan chunking untuk efisiensi memori
 * - Path dibangun setelah semua wilayah diinsert
 * - Estimasi waktu: 83.000+ baris desa → sekitar 5-10 menit
 * - Jalankan sekali saja — gunakan firstOrCreate untuk idempotent
 */
class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = storage_path('app/seeds/wilayah_dagri.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("File tidak ditemukan: {$csvPath}");
            $this->command->info("Letakkan file CSV di: storage/app/seeds/wilayah_dagri.csv");
            return;
        }

        $this->command->info('Memulai import wilayah...');

        // === TAHAP 1: Insert semua wilayah tanpa path dulu ===
        // Kita perlu semua row ada di DB sebelum bisa resolve parent_id

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle); // Skip header baris pertama

        $batch   = [];
        $counter = 0;
        $now     = now();

        while (($row = fgetcsv($handle)) !== false) {
            [$kode_dagri, $nama, $level, $kode_parent] = $row;

            $batch[] = [
                'kode_dagri' => trim($kode_dagri),
                'nama'       => trim($nama),
                'level'      => trim($level),
                'parent_id'  => null, // Akan diisi di tahap 2
                'path'       => null, // Akan diisi di tahap 3
                'created_at' => $now,
                'updated_at' => $now,
                '_kode_parent' => trim($kode_parent), // Sementara, dihapus setelah dipakai
            ];

            $counter++;

            // Insert per 1000 baris untuk efisiensi memori
            if (count($batch) >= 1000) {
                $this->insertBatch($batch);
                $batch = [];
                $this->command->info("  Inserted {$counter} baris...");
            }
        }

        if (!empty($batch)) {
            $this->insertBatch($batch);
        }

        fclose($handle);
        $this->command->info("Tahap 1 selesai: {$counter} wilayah diinsert.");

        // === TAHAP 2: Isi parent_id berdasarkan kode_parent ===
        $this->command->info('Tahap 2: Mengisi parent_id...');

        // Baca ulang CSV untuk mapping parent
        $handle = fopen($csvPath, 'r');
        fgetcsv($handle); // Skip header

        // Load semua kode → id ke memori (hanya kode dan id saja)
        $kodeToId = Wilayah::pluck('id', 'kode_dagri')->toArray();

        while (($row = fgetcsv($handle)) !== false) {
            [$kode_dagri, , , $kode_parent] = $row;
            $kode_parent = trim($kode_parent);

            if (!empty($kode_parent) && isset($kodeToId[$kode_parent])) {
                DB::table('wilayah')
                    ->where('kode_dagri', trim($kode_dagri))
                    ->update(['parent_id' => $kodeToId[$kode_parent]]);
            }
        }

        fclose($handle);
        $this->command->info('Tahap 2 selesai.');

        // === TAHAP 3: Bangun materialized path ===
        $this->command->info('Tahap 3: Membangun path hierarki...');
        $this->buildPaths();
        $this->command->info('Tahap 3 selesai.');

        $this->command->info("✓ Import wilayah selesai. Total: {$counter} wilayah.");
    }

    private function insertBatch(array $batch): void
    {
        // Hapus field temporary sebelum insert
        $cleanBatch = array_map(function ($row) {
            unset($row['_kode_parent']);
            return $row;
        }, $batch);

        // insertOrIgnore agar idempotent kalau dijalankan ulang
        DB::table('wilayah')->insertOrIgnore($cleanBatch);
    }

    private function buildPaths(): void
    {
        // Proses per level dari atas ke bawah
        // Provinsi: path = kode_dagri
        DB::table('wilayah')
            ->where('level', 'provinsi')
            ->update(['path' => DB::raw('kode_dagri')]);

        // Kabupaten: path = parent.path + '.' + kode_dagri
        DB::statement("
            UPDATE wilayah AS anak
            JOIN wilayah AS induk ON anak.parent_id = induk.id
            SET anak.path = CONCAT(induk.path, '.', anak.kode_dagri)
            WHERE anak.level = 'kabupaten'
        ");

        // Kecamatan
        DB::statement("
            UPDATE wilayah AS anak
            JOIN wilayah AS induk ON anak.parent_id = induk.id
            SET anak.path = CONCAT(induk.path, '.', anak.kode_dagri)
            WHERE anak.level = 'kecamatan'
        ");

        // Desa
        DB::statement("
            UPDATE wilayah AS anak
            JOIN wilayah AS induk ON anak.parent_id = induk.id
            SET anak.path = CONCAT(induk.path, '.', anak.kode_dagri)
            WHERE anak.level = 'desa'
        ");
    }
}