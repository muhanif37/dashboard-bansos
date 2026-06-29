<?php

namespace App\Livewire\Dashboard;

use App\Models\JenisBansos;
use App\Models\Periode;
use App\Models\SummaryBansos;
use App\Models\Wilayah;
use Livewire\Component;

class MetricCards extends Component
{
    public int   $tahun          = 0;
    // public ?int  $triwulan          = null;
    public array $triwulan = [];
    public ?int  $wilayahId      = null;
    public ?int  $jenisBansosId  = null;

    // Data yang ditampilkan
    public array $metrics = [];

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
        array $triwulan = [],
        ?int $wilayah_id = null,
        ?int $jenis_bansos_id = null
    ): void {
        $this->tahun         = $tahun;
        $this->triwulan      = $triwulan;
        $this->wilayahId     = $wilayah_id;
        $this->jenisBansosId = $jenis_bansos_id;
        $this->muatData();
    }

    private function muatData(): void
    {
        // Ambil periode sesuai triwulan yang dipilih
        $periodeQuery = Periode::byTahun($this->tahun);
        if (!empty($this->triwulan)) {
            $periodeQuery->whereIn('triwulan', $this->triwulan);
        }
        $periodeList = $periodeQuery->urut()->get();

        $jenisList = $this->jenisBansosId
            ? JenisBansos::where('id', $this->jenisBansosId)->aktif()->get()
            : JenisBansos::aktif()->get();

        $this->metrics = [];

        foreach ($jenisList as $jenis) {
            foreach ($periodeList as $periode) {
                $query = SummaryBansos::byJenis($jenis->id)->byPeriode($periode->id);

                if ($this->wilayahId) {
                    $wilayah = Wilayah::find($this->wilayahId);
                    $query->dibawahWilayah($wilayah);
                } else {
                    $query->whereHas('wilayah', fn($q) => $q->byLevel('kabupaten'));
                }

                $agregat = $query->selectRaw('
                    SUM(target_kpm) as total_target_kpm,
                    SUM(realisasi_kpm) as total_realisasi_kpm,
                    SUM(target_nominal) as total_target_nominal,
                    SUM(realisasi_nominal) as total_realisasi_nominal
                ')->first();

                $targetKpm        = (int) ($agregat->total_target_kpm ?? 0);
                $realisasiKpm     = (int) ($agregat->total_realisasi_kpm ?? 0);
                $pctKpm           = $targetKpm > 0 ? round($realisasiKpm / $targetKpm * 100, 1) : 0;
                $targetNominal    = (int) ($agregat->total_target_nominal ?? 0);
                $realisasiNominal = (int) ($agregat->total_realisasi_nominal ?? 0);

                // Skip jika tidak ada data sama sekali
                if ($targetKpm === 0 && $realisasiKpm === 0) continue;

                $this->metrics[] = [
                    'kode'              => $jenis->kode,
                    'nama'              => $jenis->nama,
                    'periode_label'     => $periode->label, // tambah label periode
                    'triwulan'          => $periode->triwulan,
                    'target_kpm'        => $targetKpm,
                    'realisasi_kpm'     => $realisasiKpm,
                    'pct_kpm'           => $pctKpm,
                    'target_nominal'    => $targetNominal,
                    'realisasi_nominal' => $realisasiNominal,
                    'status'            => $pctKpm >= 90 ? 'tercapai' : ($pctKpm >= 70 ? 'perhatian' : 'kritis'),
                ];
            }
        }
    }

    public function render()
    {
        return view('livewire.dashboard.metric-cards');
    }
}