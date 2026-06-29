<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SummaryService;

class RekomputasiSummary extends Command
{
    protected $signature = 'bansos:rekomputasi
                            {--wilayah= : ID wilayah spesifik (opsional)}
                            {--periode= : ID periode spesifik (opsional)}
                            {--jenis=   : ID jenis bansos spesifik (opsional)}';

    protected $description = 'Rekomputasi tabel summary_bansos dari data target dan realisasi';

    public function __construct(private SummaryService $summaryService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $wilayahId    = $this->option('wilayah');
        $periodeId    = $this->option('periode');
        $jenisBansosId = $this->option('jenis');

        // Rekomputasi spesifik satu kombinasi
        if ($wilayahId && $periodeId && $jenisBansosId) {
            $this->info("Rekomputasi untuk wilayah={$wilayahId}, periode={$periodeId}, jenis={$jenisBansosId}...");
            $this->summaryService->hitung((int) $wilayahId, (int) $jenisBansosId, (int) $periodeId);
            $this->info('Selesai.');
            return self::SUCCESS;
        }

        // Rekomputasi semua — tampilkan progress bar
        $this->info('Memulai rekomputasi seluruh summary...');
        $this->warn('Proses ini bisa memakan beberapa menit tergantung jumlah data.');

        $this->withProgressBar(
            \App\Models\TargetBansos::select('wilayah_id', 'jenis_bansos_id', 'periode_id')->get(),
            function ($item) {
                $this->summaryService->hitung(
                    $item->wilayah_id,
                    $item->jenis_bansos_id,
                    $item->periode_id
                );
            }
        );

        $this->newLine();
        $this->info('Rekomputasi selesai.');

        return self::SUCCESS;
    }
}