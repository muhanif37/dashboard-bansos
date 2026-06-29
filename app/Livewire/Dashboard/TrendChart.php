<?php

namespace App\Livewire\Dashboard;

use App\Models\JenisBansos;
use App\Models\Periode;
use App\Models\SummaryBansos;
use App\Models\Wilayah;
use Livewire\Component;

class TrendChart extends Component
{
    public int  $tahun         = 0;
    public ?int $wilayahId     = null;
    public ?int $jenisBansosId = null;

    // Data chart yang di-pass ke view → Alpine → ECharts
    public array $chartData = [];

    protected $listeners = ['filter-updated' => 'filterBerubah'];

    public function mount(): void
    {
        $this->tahun = (int) (Periode::whereHas('targetBansos')
            ->orWhereHas('realisasiBansos')
            ->max('tahun') ?? now()->year);
        $this->muatData();
    }

    public function filterBerubah(
        int $tahun,
        array $triwulan = [], // terima array tapi diabaikan
        ?int $wilayah_id = null,
        ?int $jenis_bansos_id = null
    ): void {
        $this->tahun         = $tahun;
        $this->wilayahId     = $wilayah_id;
        $this->jenisBansosId = $jenis_bansos_id;
        $this->muatData();
    }

    private function muatData(): void
    {
        $periodeList = Periode::byTahun($this->tahun)->urut()->get();

        $jenisList = $this->jenisBansosId
            ? JenisBansos::where('id', $this->jenisBansosId)->aktif()->get()
            : JenisBansos::aktif()->get();

        $labels  = $periodeList->pluck('label')->toArray();
        $series  = [];

        foreach ($jenisList as $jenis) {
            $targetKpm    = [];
            $realisasiKpm = [];

            foreach ($periodeList as $periode) {
                $query = SummaryBansos::byJenis($jenis->id)->byPeriode($periode->id);

                if ($this->wilayahId) {
                    $wilayah = Wilayah::find($this->wilayahId);
                    $query->dibawahWilayah($wilayah);
                } else {
                    $query->whereHas('wilayah', fn($q) => $q->byLevel('kabupaten'));
                }

                $agregat = $query->selectRaw('
                    SUM(target_kpm) as t,
                    SUM(realisasi_kpm) as r
                ')->first();

                $targetKpm[]    = (int) ($agregat->t ?? 0);
                $realisasiKpm[] = (int) ($agregat->r ?? 0);
            }

            $series[] = [
                'nama'          => $jenis->nama,
                'kode'          => $jenis->kode,
                'target'        => $targetKpm,
                'realisasi'     => $realisasiKpm,
            ];
        }

        $this->chartData = ['labels' => $labels, 'series' => $series];

        // Kirim event ke browser agar Alpine/ECharts render ulang chart
        $this->dispatch('chart-data-updated', data: $this->chartData);
    }

    public function render()
    {
        return view('livewire.dashboard.trend-chart');
    }
}