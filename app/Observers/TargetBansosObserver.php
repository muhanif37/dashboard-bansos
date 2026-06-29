<?php

namespace App\Observers;

use App\Models\TargetBansos;
use App\Services\SummaryService;

class TargetBansosObserver
{
    public function __construct(private SummaryService $summaryService) {}

    /**
     * Dipanggil setelah data target baru disimpan.
     */
    public function created(TargetBansos $target): void
    {
        $this->summaryService->hitung(
            $target->wilayah_id,
            $target->jenis_bansos_id,
            $target->periode_id
        );
    }

    /**
     * Dipanggil setelah data target diupdate.
     */
    public function updated(TargetBansos $target): void
    {
        $this->summaryService->hitung(
            $target->wilayah_id,
            $target->jenis_bansos_id,
            $target->periode_id
        );
    }

    /**
     * Dipanggil setelah data target dihapus.
     * Summary tetap diupdate — statusnya akan jadi 'belum_ada'
     * karena tidak ada target yang bisa dihitung.
     */
    public function deleted(TargetBansos $target): void
    {
        $this->summaryService->hitung(
            $target->wilayah_id,
            $target->jenis_bansos_id,
            $target->periode_id
        );
    }
}