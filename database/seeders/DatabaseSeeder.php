<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\JenisBansos;
use App\Models\Periode;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            JenisBansosSeeder::class,
            PeriodeSeeder::class,
            // WilayahSeeder dijalankan terpisah via artisan karena datanya besar
            // php artisan db:seed --class=WilayahSeeder
        ]);
    }
}


class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin default — WAJIB ganti password setelah deploy pertama
        User::firstOrCreate(
            ['email' => 'admin@bansos.go.id'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
                'aktif'    => true,
            ]
        );
    }
}


class JenisBansosSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kode'      => 'PKH',
                'nama'      => 'Program Keluarga Harapan',
                'deskripsi' => 'Bantuan tunai bersyarat untuk keluarga miskin',
                'aktif'     => true,
            ],
            [
                'kode'      => 'SEMBAKO',
                'nama'      => 'Program Sembako',
                'deskripsi' => 'Bantuan Pangan Non-Tunai (BPNT)',
                'aktif'     => true,
            ],
            // [
            //     'kode'      => 'STIMULUS_SEMBAKO',
            //     'nama'      => 'Stimulus Sembako',
            //     'deskripsi' => 'Bantuan stimulus program sembako',
            //     'aktif'     => true,
            // ],
            // [
            //     'kode'      => 'BLT_KESRA',
            //     'nama'      => 'Bantuan Langsung Tunai (BLT) Kesejahteraan Sosial',
            //     'deskripsi' => 'Bantuan tunai untuk kesejahteraan sosial',
            //     'aktif'     => true,
            // ],
        ];

        foreach ($data as $item) {
            JenisBansos::firstOrCreate(['kode' => $item['kode']], $item);
        }
    }
}


class PeriodeSeeder extends Seeder
{
    public function run(): void
    {
        // Generate periode dari 2025 sampai 2026
        foreach (range(2025, 2026) as $tahun) {
            foreach (range(1, 4) as $triwulan) {
                $label = 'TW ' . ['I', 'II', 'III', 'IV'][$triwulan - 1] . ' ' . $tahun;
 
                Periode::firstOrCreate(
                    ['tahun' => $tahun, 'triwulan' => $triwulan],
                    ['label' => $label]
                );
            }
        }
    }
}