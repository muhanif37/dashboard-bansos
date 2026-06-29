<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WilayahFlag extends Model
{
    // Tabel ini tidak punya kolom timestamps (created_at, updated_at)
    public $timestamps = false;

    protected $table = 'wilayah_flag';

    protected $fillable = [
        'wilayah_id',
        'jenis_flag',
        'tahun_berlaku',
    ];

    protected function casts(): array
    {
        return [
            'wilayah_id'    => 'integer',
            'tahun_berlaku' => 'integer',
        ];
    }

    // =========================================================
    // Konstanta jenis flag — pakai konstanta bukan magic string
    // agar refactor lebih aman dan IDE bisa autocomplete
    // =========================================================

    // Flag yang aktif saat ini — hanya level kabupaten/kota
    const PRIORITAS_88 = 'prioritas_88';

    // Disiapkan untuk ekspansi nanti, belum aktif di DB
    // const KEPMENKO_6_2026 = 'kepmenko_6_2026';

    /**
     * Daftar semua jenis flag yang aktif beserta label display-nya.
     * Dipakai untuk dropdown di form admin dan legenda di dashboard.
     */
    public static function daftarJenisFlag(): array
    {
        return [
            self::PRIORITAS_88 => '88 Kab/Kota Prioritas Kemiskinan',
            // Tambah di sini saat flag baru diaktifkan
        ];
    }

    /**
     * Daftar flag beserta level wilayah yang sesuai.
     * Dipakai untuk validasi saat admin input flag —
     * memastikan flag hanya bisa diterapkan ke level wilayah yang benar.
     */
    public static function levelPerFlag(): array
    {
        return [
            self::PRIORITAS_88 => 'kabupaten',
            // self::KEPMENKO_6_2026 => 'desa',
        ];
    }

    // =========================================================
    // Scopes
    // =========================================================

    public function scopeByJenis($query, string $jenisFlag)
    {
        return $query->where('jenis_flag', $jenisFlag);
    }

    public function scopeByTahun($query, int $tahun)
    {
        return $query->where('tahun_berlaku', $tahun);
    }

    // =========================================================
    // Relasi
    // =========================================================

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }
}