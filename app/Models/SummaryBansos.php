<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SummaryBansos extends Model
{
    public $timestamps  = false;
    public $incrementing = false;

    protected $table = 'summary_bansos';

    protected $fillable = [
        'wilayah_id',
        'jenis_bansos_id',
        'periode_id',
        'target_kpm',
        'target_nominal',
        'penyaluran_kpm',
        'penyaluran_nominal',
        'realisasi_kpm',
        'realisasi_nominal',
        'selisih_kpm',
        'selisih_nominal',
        'pct_kpm',
        'pct_nominal',
        'pct_penyaluran_kpm',
        'pct_penyaluran_nominal',
        'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'wilayah_id'             => 'integer',
            'jenis_bansos_id'        => 'integer',
            'periode_id'             => 'integer',
            'target_kpm'             => 'integer',
            'target_nominal'         => 'string',
            'penyaluran_kpm'         => 'integer',
            'penyaluran_nominal'     => 'string',
            'realisasi_kpm'          => 'integer',
            'realisasi_nominal'      => 'string',
            'selisih_kpm'            => 'integer',
            'selisih_nominal'        => 'string',
            'pct_kpm'                => 'float',
            'pct_nominal'            => 'float',
            'pct_penyaluran_kpm'     => 'float',
            'pct_penyaluran_nominal' => 'float',
            'computed_at'            => 'datetime',
        ];
    }

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

    public function scopeDibawahWilayah($query, Wilayah $wilayah)
    {
        return $query->whereHas('wilayah', function ($q) use ($wilayah) {
            $q->where('path', 'like', $wilayah->path . '.%')
              ->orWhere('id', $wilayah->id);
        });
    }

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
}