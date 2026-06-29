<?php

namespace App\Services;

use App\Models\TargetBansos;
use App\Models\RealisasiBansos;
use Illuminate\Support\Facades\DB;

class SummaryService
{
    public function hitung(int $wilayahId, int $jenisBansosId, int $periodeId): void
    {
        $target = TargetBansos::where([
            'wilayah_id'      => $wilayahId,
            'jenis_bansos_id' => $jenisBansosId,
            'periode_id'      => $periodeId,
        ])->first();

        if (!$target) {
            return;
        }

        $realisasi = RealisasiBansos::where([
            'wilayah_id'      => $wilayahId,
            'jenis_bansos_id' => $jenisBansosId,
            'periode_id'      => $periodeId,
        ])->first();

        $targetKpm        = (int) $target->jumlah_kpm;
        $targetNominal    = (int) $target->nominal;

        $penyaluranKpm     = (int) ($realisasi?->penyaluran_kpm ?? 0);
        $penyaluranNominal = (int) ($realisasi?->penyaluran_nominal ?? 0);
        $realisasiKpm      = (int) ($realisasi?->jumlah_kpm ?? 0);
        $realisasiNominal  = (int) ($realisasi?->nominal ?? 0);

        $selisihKpm     = $targetKpm - $realisasiKpm;
        $selisihNominal = $targetNominal - $realisasiNominal;

        // Cap persentase di 9999.9999 untuk data anomali lapangan
        $pctKpm     = $targetKpm > 0
            ? min(round(($realisasiKpm / $targetKpm) * 100, 4), 9999.9999)
            : 0;

        $pctNominal = $targetNominal > 0
            ? min(round(($realisasiNominal / $targetNominal) * 100, 4), 9999.9999)
            : 0;

        $pctPenyaluranKpm = $targetKpm > 0
            ? min(round(($penyaluranKpm / $targetKpm) * 100, 4), 9999.9999)
            : 0;

        $pctPenyaluranNominal = $targetNominal > 0
            ? min(round(($penyaluranNominal / $targetNominal) * 100, 4), 9999.9999)
            : 0;

        DB::table('summary_bansos')->upsert(
            [
                'wilayah_id'              => $wilayahId,
                'jenis_bansos_id'         => $jenisBansosId,
                'periode_id'              => $periodeId,
                'target_kpm'              => $targetKpm,
                'target_nominal'          => $targetNominal,
                'penyaluran_kpm'          => $penyaluranKpm,
                'penyaluran_nominal'      => $penyaluranNominal,
                'realisasi_kpm'           => $realisasiKpm,
                'realisasi_nominal'       => $realisasiNominal,
                'selisih_kpm'             => $selisihKpm,
                'selisih_nominal'         => $selisihNominal,
                'pct_kpm'                 => $pctKpm,
                'pct_nominal'             => $pctNominal,
                'pct_penyaluran_kpm'      => $pctPenyaluranKpm,
                'pct_penyaluran_nominal'  => $pctPenyaluranNominal,
                'computed_at'             => now(),
            ],
            ['wilayah_id', 'jenis_bansos_id', 'periode_id'],
            [
                'target_kpm', 'target_nominal',
                'penyaluran_kpm', 'penyaluran_nominal',
                'realisasi_kpm', 'realisasi_nominal',
                'selisih_kpm', 'selisih_nominal',
                'pct_kpm', 'pct_nominal',
                'pct_penyaluran_kpm', 'pct_penyaluran_nominal',
                'computed_at',
            ]
        );
    }

    public function hitungSemua(): void
    {
        $kombinasi = TargetBansos::select('wilayah_id', 'jenis_bansos_id', 'periode_id')->get();
        foreach ($kombinasi as $item) {
            $this->hitung($item->wilayah_id, $item->jenis_bansos_id, $item->periode_id);
        }
    }
}