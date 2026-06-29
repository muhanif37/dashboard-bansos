<?php

namespace App\Models;

use App\Models\RealisasiBansos;
use App\Models\SummaryBansos;
use App\Models\TargetBansos;
use App\Models\WilayahFlag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    protected $table = 'wilayah';

    protected $fillable = [
        'kode_dagri',
        'nama',
        'level',
        'parent_id',
        'path',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
        ];
    }

    // =========================================================
    // Scopes — dipakai di filter dashboard
    // =========================================================

    /**
     * Filter berdasarkan level wilayah.
     * Contoh: Wilayah::byLevel('provinsi')->get()
     */
    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Ambil semua turunan dari wilayah ini menggunakan materialized path.
     * Lebih efisien dari recursive query karena cukup satu LIKE query.
     * Contoh: $jateng->turunan()->byLevel('kabupaten')->get()
     */
    public function scopeTurunan($query)
    {
        return $query->where('path', 'like', $this->path . '.%');
    }

    /**
     * Filter wilayah yang termasuk dalam flag tertentu di tahun tertentu.
     * Contoh: Wilayah::byLevel('kabupaten')->denganFlag('prioritas_88', 2026)->get()
     */
    public function scopeDenganFlag($query, string $jenisFlag, int $tahun)
    {
        return $query->whereHas('flags', function ($q) use ($jenisFlag, $tahun) {
            $q->where('jenis_flag', $jenisFlag)
              ->where('tahun_berlaku', $tahun);
        });
    }

    // =========================================================
    // Relasi
    // =========================================================

    public function parent()
    {
        return $this->belongsTo(Wilayah::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Wilayah::class, 'parent_id');
    }

    public function flags()
    {
        return $this->hasMany(WilayahFlag::class, 'wilayah_id');
    }

    public function targetBansos()
    {
        return $this->hasMany(TargetBansos::class, 'wilayah_id');
    }

    public function realisasiBansos()
    {
        return $this->hasMany(RealisasiBansos::class, 'wilayah_id');
    }

    public function summaryBansos()
    {
        return $this->hasMany(SummaryBansos::class, 'wilayah_id');
    }

    // =========================================================
    // Helpers
    // =========================================================

    /**
     * Cek apakah wilayah ini punya flag tertentu di tahun tertentu.
     * Berguna di Blade untuk conditional rendering badge flag.
     */
    public function punyaFlag(string $jenisFlag, int $tahun): bool
    {
        return $this->flags
            ->where('jenis_flag', $jenisFlag)
            ->where('tahun_berlaku', $tahun)
            ->isNotEmpty();
    }

    /**
     * Ambil semua provinsi — sering dipakai untuk filter dropdown level pertama.
     */
    public static function semuaProvinsi(): Collection
    {
        return static::byLevel('provinsi')->orderBy('nama')->get();
    }

    /**
     * Ambil kabupaten/kota berdasarkan parent provinsi.
     * Dipakai untuk dropdown bertingkat di filter dashboard.
     */
    public static function kabupatenByProvinsi(int $provinsiId): Collection
    {
        return static::byLevel('kabupaten')
            ->where('parent_id', $provinsiId)
            ->orderBy('nama')
            ->get();
    }
}