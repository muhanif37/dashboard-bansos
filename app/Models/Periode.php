<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periode';

    protected $fillable = [
        'tahun',
        'triwulan',
        'label',
    ];

    protected function casts(): array
    {
        return [
            'tahun'    => 'integer',
            'triwulan' => 'integer',
        ];
    }

    // =========================================================
    // Scopes
    // =========================================================

    public function scopeByTahun($query, int $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeByTriwulan($query, int $triwulan)
    {
        return $query->where('triwulan', $triwulan);
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('tahun')->orderBy('triwulan');
    }

    // =========================================================
    // Relasi
    // =========================================================

    public function targetBansos()
    {
        return $this->hasMany(TargetBansos::class, 'periode_id');
    }

    public function realisasiBansos()
    {
        return $this->hasMany(RealisasiBansos::class, 'periode_id');
    }

    public function summaryBansos()
    {
        return $this->hasMany(SummaryBansos::class, 'periode_id');
    }
}