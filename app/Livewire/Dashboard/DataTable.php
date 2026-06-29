<?php

namespace App\Livewire\Dashboard;

use App\Models\Periode;
use App\Models\SummaryBansos;
use App\Models\Wilayah;
use Livewire\Component;
use Livewire\WithPagination;

class DataTable extends Component
{
    use WithPagination;

    public int   $tahun         = 0;
    // public ?int  $triwulan         = null;
    public array $triwulan = [];
    public ?int  $wilayahId     = null;
    public ?int  $jenisBansosId = null;
    public int   $perHalaman    = 25;
    public string $sortBy       = 'pct_kpm';
    public string $sortDir      = 'asc'; // asc = yang terendah duluan (prioritas perhatian)

    protected $listeners = ['filter-updated' => 'filterBerubah'];

    public function mount(): void
    {
        $this->tahun = (int) now()->year;
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
        $this->resetPage();
    }

    public function sortasi(string $kolom): void
    {
        if ($this->sortBy === $kolom) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $kolom;
            $this->sortDir = 'asc';
        }
    }

    public function render()
    {
        // Ambil periode yang ada datanya saja
        $periodeQuery = Periode::byTahun($this->tahun);
        if (!empty($this->triwulan)) {
            $periodeQuery->whereIn('triwulan', $this->triwulan);
        }
        $periodeList = $periodeQuery->urut()->get();
        $periodeIds  = $periodeList->pluck('id');

        // Base query
        $baseQuery = SummaryBansos::whereIn('periode_id', $periodeIds);

        if ($this->jenisBansosId) {
            $baseQuery->byJenis($this->jenisBansosId);
        }

        if ($this->wilayahId) {
            $wilayah = Wilayah::find($this->wilayahId);
            $baseQuery->dibawahWilayah($wilayah);
        } else {
            $baseQuery->whereHas('wilayah', fn($q) => $q->byLevel('kabupaten'));
        }

        // Ambil semua data flat dulu
        $flatData = $baseQuery
            ->with(['wilayah.parent', 'jenisBansos', 'periode'])
            ->selectRaw('
                wilayah_id,
                jenis_bansos_id,
                periode_id,
                SUM(target_kpm)        as target_kpm,
                SUM(target_nominal)    as target_nominal,
                SUM(penyaluran_kpm)    as penyaluran_kpm,
                SUM(penyaluran_nominal) as penyaluran_nominal,
                SUM(realisasi_kpm)     as realisasi_kpm,
                SUM(realisasi_nominal) as realisasi_nominal,
                ROUND(SUM(realisasi_kpm) / NULLIF(SUM(target_kpm), 0) * 100, 2) as pct_kpm
            ')
            ->groupBy('wilayah_id', 'jenis_bansos_id', 'periode_id')
            ->get();

        // Hanya tampilkan periode yang benar-benar ada datanya
        $periodeAda = $periodeList->filter(
            fn($p) => $flatData->where('periode_id', $p->id)->isNotEmpty()
        )->values();

        // Pivot: group by wilayah + jenis, kolom = triwulan
        $pivoted = $flatData
            ->groupBy(fn($row) => $row->wilayah_id . '_' . $row->jenis_bansos_id)
            ->map(function ($rows) use ($periodeAda) {
                $first = $rows->first();
                $entry = [
                    'wilayah'     => $first->wilayah,
                    'jenisBansos' => $first->jenisBansos,
                    'periodes'    => [],
                ];

                foreach ($periodeAda as $periode) {
                    $match = $rows->firstWhere('periode_id', $periode->id);
                    $entry['periodes'][$periode->id] = $match ? [
                        'target_kpm'         => (int) $match->target_kpm,
                        'target_nominal'     => (int) $match->target_nominal,
                        'penyaluran_kpm'     => (int) $match->penyaluran_kpm,
                        'penyaluran_nominal' => (int) $match->penyaluran_nominal,
                        'realisasi_kpm'      => (int) $match->realisasi_kpm,
                        'realisasi_nominal'  => (int) $match->realisasi_nominal,
                        'pct_kpm'            => (float) $match->pct_kpm,
                    ] : null;
                }

                // Hitung tren: bandingkan pct_kpm periode pertama vs terakhir
                $pctValues = collect($entry['periodes'])
                    ->whereNotNull()
                    ->pluck('pct_kpm')
                    ->values();

                $entry['tren'] = $pctValues->count() >= 2
                    ? ($pctValues->last() <=> $pctValues->slice(-2, 1)->first())
                    : null;

                return $entry;
            })
            ->values();

        // Sort manual setelah pivot
        $pivoted = $pivoted->sortBy(function ($row) use ($periodeAda) {
            $firstPeriode = $periodeAda->first();
            if (!$firstPeriode) return 0;
            $p = $row['periodes'][$firstPeriode->id] ?? null;
            return $p ? ($this->sortDir === 'asc' ? $p[$this->sortBy] : -$p[$this->sortBy]) : 0;
        })->values();

        // Manual paginate
        $page      = $this->getPage();
        $total     = $pivoted->count();
        $items     = $pivoted->slice(($page - 1) * $this->perHalaman, $this->perHalaman)->values();
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $this->perHalaman, $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('livewire.dashboard.data-table', [
            'data'        => $paginator,
            'periodeAda'  => $periodeAda,
        ]);
    }
}