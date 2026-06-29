<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetBansos extends Model
{
    protected $table = 'target_bansos';

    protected $fillable = [
        'wilayah_id',
        'jenis_bansos_id',
        'periode_id',
        'jumlah_kpm',
        'nominal',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'wilayah_id'      => 'integer',
            'jenis_bansos_id' => 'integer',
            'periode_id'      => 'integer',
            'jumlah_kpm'      => 'integer',
            // Cast ke string bukan integer untuk nominal —
            // PHP integer max 64-bit aman, tapi string lebih aman
            // untuk nilai sangat besar saat serialisasi JSON
            'nominal'         => 'string',
            'created_by'      => 'integer',
            'updated_by'      => 'integer',
        ];
    }

    // =========================================================
    // Scopes
    // =========================================================

    public function scopeByJenis($query, int $jenisBansosId)
    {
        return $query->where('jenis_bansos_id', $jenisBansosId);
    }

    public function scopeByPeriode($query, int $periodeId)
    {
        return $query->where('periode_id', $periodeId);
    }

    public function scopeByWilayah($query, int $wilayahId)
    {
        return $query->where('wilayah_id', $wilayahId);
    }

    // =========================================================
    // Relasi
    // =========================================================

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function jenisBansos()
    {
        return $this->belongsTo(JenisBansos::class, 'jenis_bansos_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}