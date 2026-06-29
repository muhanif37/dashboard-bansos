<?php

namespace App\Http\Controllers;

use App\Models\JenisBansos;
use App\Models\Periode;
use App\Models\SummaryBansos;
use App\Models\Wilayah;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Halaman utama dashboard.
     * Hanya kirim data statis untuk render awal —
     * data chart dan tabel dimuat oleh Livewire secara reaktif.
     */
    public function index()
    {
        $provinsi    = Wilayah::semuaProvinsi();
        $jenisBansos = JenisBansos::aktif()->get();
        $tahunList   = Periode::select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun');

        return view('dashboard.index', compact('provinsi', 'jenisBansos', 'tahunList'));
    }

    /**
     * Endpoint JSON untuk data chart time series.
     * Dipanggil Livewire TrendChart via fetch.
     *
     * Query param:
     * - wilayah_id    : int|null  (null = nasional)
     * - jenis_bansos_id : int|null (null = semua)
     * - tahun         : int
     */
    public function dataChart(Request $request)
    {
        $request->validate([
            'tahun'           => ['required', 'integer', 'min:2020', 'max:2099'],
            'wilayah_id'      => ['nullable', 'exists:wilayah,id'],
            'jenis_bansos_id' => ['nullable', 'exists:jenis_bansos,id'],
        ]);

        $tahun          = (int) $request->tahun;
        $wilayahId      = $request->wilayah_id;
        $jenisBansosId  = $request->jenis_bansos_id;

        // Ambil semua periode dalam tahun ini untuk sumbu X
        $periodeList = Periode::byTahun($tahun)->urut()->get();

        // Susun data per jenis bansos
        $jenisList = $jenisBansosId
            ? JenisBansos::where('id', $jenisBansosId)->aktif()->get()
            : JenisBansos::aktif()->get();

        $series = [];

        foreach ($jenisList as $jenis) {
            $targetKpm     = [];
            $realisasiKpm  = [];
            $targetNominal = [];
            $realisasiNominal = [];

            foreach ($periodeList as $periode) {
                $query = SummaryBansos::byJenis($jenis->id)->byPeriode($periode->id);

                // Filter wilayah: kalau ada wilayah dipilih, ambil semua turunannya
                if ($wilayahId) {
                    $wilayah = Wilayah::find($wilayahId);
                    $query->dibawahWilayah($wilayah);
                }

                // Agregasi — SUM karena bisa banyak kabupaten
                $agregat = $query->selectRaw('
                    SUM(target_kpm) as total_target_kpm,
                    SUM(realisasi_kpm) as total_realisasi_kpm,
                    SUM(target_nominal) as total_target_nominal,
                    SUM(realisasi_nominal) as total_realisasi_nominal
                ')->first();

                $targetKpm[]        = (int) ($agregat->total_target_kpm ?? 0);
                $realisasiKpm[]     = (int) ($agregat->total_realisasi_kpm ?? 0);
                $targetNominal[]    = (int) ($agregat->total_target_nominal ?? 0);
                $realisasiNominal[] = (int) ($agregat->total_realisasi_nominal ?? 0);
            }

            $series[] = [
                'jenis'             => $jenis->kode,
                'nama'              => $jenis->nama,
                'target_kpm'        => $targetKpm,
                'realisasi_kpm'     => $realisasiKpm,
                'target_nominal'    => $targetNominal,
                'realisasi_nominal' => $realisasiNominal,
            ];
        }

        return response()->json([
            'labels' => $periodeList->pluck('label'),
            'series' => $series,
        ]);
    }

    /**
     * Endpoint JSON untuk data tabel rincian per kabupaten.
     * Dipanggil Livewire DataTable via fetch.
     */
    public function dataTabel(Request $request)
    {
        $request->validate([
            'tahun'           => ['required', 'integer'],
            'triwulan'        => ['nullable', 'integer', 'min:1', 'max:4'],
            'wilayah_id'      => ['nullable', 'exists:wilayah,id'],
            'jenis_bansos_id' => ['nullable', 'exists:jenis_bansos,id'],
            'status'          => ['nullable', 'in:tercapai,perhatian,kritis,belum_ada'],
            'per_halaman'     => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $tahun         = (int) $request->tahun;
        $triwulan      = $request->triwulan ? (int) $request->triwulan : null;
        $wilayahId     = $request->wilayah_id;
        $jenisBansosId = $request->jenis_bansos_id;
        $perHalaman    = (int) ($request->per_halaman ?? 25);

        // Bangun query periode
        $periodeQuery = Periode::byTahun($tahun);
        if ($triwulan) {
            $periodeQuery->byTriwulan($triwulan);
        }
        $periodeIds = $periodeQuery->pluck('id');

        // Bangun query summary
        $query = SummaryBansos::with(['wilayah', 'jenisBansos', 'periode'])
            ->whereIn('periode_id', $periodeIds);

        if ($jenisBansosId) {
            $query->byJenis((int) $jenisBansosId);
        }

        if ($request->status) {
            $query->byStatus($request->status);
        }

        // Filter wilayah — kalau tidak ada, tampilkan level kabupaten semua
        if ($wilayahId) {
            $wilayah = Wilayah::find($wilayahId);
            $query->dibawahWilayah($wilayah);
        } else {
            // Nasional: hanya tampilkan level kabupaten
            $query->whereHas('wilayah', fn($q) => $q->byLevel('kabupaten'));
        }

        // Agregasi per wilayah + jenis bansos jika ada banyak periode
        $data = $query->selectRaw('
                wilayah_id,
                jenis_bansos_id,
                SUM(target_kpm) as target_kpm,
                SUM(realisasi_kpm) as realisasi_kpm,
                SUM(target_kpm) - SUM(realisasi_kpm) as selisih_kpm,
                ROUND(SUM(realisasi_kpm) / NULLIF(SUM(target_kpm), 0) * 100, 2) as pct_kpm,
                SUM(target_nominal) as target_nominal,
                SUM(realisasi_nominal) as realisasi_nominal,
                SUM(target_nominal) - SUM(realisasi_nominal) as selisih_nominal,
                ROUND(SUM(realisasi_nominal) / NULLIF(SUM(target_nominal), 0) * 100, 2) as pct_nominal
            ')
            ->groupBy('wilayah_id', 'jenis_bansos_id')
            ->orderByDesc('pct_kpm')
            ->paginate($perHalaman);

        return response()->json($data);
    }

    /**
     * Endpoint JSON dropdown wilayah bertingkat.
     * Dipanggil saat user memilih provinsi → muat kabupaten, dst.
     */
    public function anakWilayah(int $wilayahId)
    {
        $anak = Wilayah::where('parent_id', $wilayahId)
            ->orderBy('nama')
            ->get(['id', 'nama', 'level', 'kode_dagri']);

        return response()->json($anak);
    }
}