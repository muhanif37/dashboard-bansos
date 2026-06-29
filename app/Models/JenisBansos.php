<?php

namespace App\Models;

use App\Models\Periode;
use Illuminate\Database\Eloquent\Model;

class JenisBansos extends Model
{
    protected $table = 'jenis_bansos';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    // =========================================================
    // Scopes
    // =========================================================

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    // =========================================================
    // Relasi
    // =========================================================

    public function targetBansos()
    {
        return $this->hasMany(TargetBansos::class, 'jenis_bansos_id');
    }

    public function realisasiBansos()
    {
        return $this->hasMany(RealisasiBansos::class, 'jenis_bansos_id');
    }

    public function summaryBansos()
    {
        return $this->hasMany(SummaryBansos::class, 'jenis_bansos_id');
    }

    public function periode()
    {
        return $this->hasMany(Periode::class, 'jenis_bansos_id');
    }
}