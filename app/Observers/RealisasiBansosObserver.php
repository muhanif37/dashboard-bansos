<?php

namespace App\Observers;

use App\Models\RealisasiBansos;
use App\Services\SummaryService;

class RealisasiBansosObserver
{
    public function __construct(private SummaryService $summaryService) {}

    public function created(RealisasiBansos $realisasi): void
    {
        $this->summaryService->hitung(
            $realisasi->wilayah_id,
            $realisasi->jenis_bansos_id,
            $realisasi->periode_id
        );
    }

    public function updated(RealisasiBansos $realisasi): void
    {
        $this->summaryService->hitung(
            $realisasi->wilayah_id,
            $realisasi->jenis_bansos_id,
            $realisasi->periode_id
        );
    }

    public function deleted(RealisasiBansos $realisasi): void
    {
        $this->summaryService->hitung(
            $realisasi->wilayah_id,
            $realisasi->jenis_bansos_id,
            $realisasi->periode_id
        );
    }
}