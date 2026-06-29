<?php

namespace App\Http\Controllers;

use App\Exports\BansosExport;
use App\Models\Periode;
use App\Models\SummaryBansos;
use Illuminate\Http\Request;
use App\Models\Wilayah;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function unduh(Request $request)
    {
        $request->validate([
            'tahun'           => ['required', 'integer'],
            'triwulan'        => ['nullable'],      // bisa array atau int
            'wilayah_id'      => ['nullable', 'exists:wilayah,id'],
            'jenis_bansos_id' => ['nullable', 'exists:jenis_bansos,id'],
            'format'          => ['nullable', 'in:xlsx,csv'],
        ]);

        $tahun         = (int) $request->tahun;
        $wilayahId     = $request->wilayah_id;
        $jenisBansosId = $request->jenis_bansos_id;
        $format        = $request->input('format', 'xlsx');

        // Handle triwulan: bisa array atau single int
        $triwulan = $request->triwulan;
        if (is_string($triwulan)) {
            $triwulan = array_filter(explode(',', $triwulan));
        }
        $triwulan = array_map('intval', (array) $triwulan);

        // Query periode
        $periodeQuery = Periode::byTahun($tahun);
        if (!empty($triwulan)) {
            $periodeQuery->whereIn('triwulan', $triwulan);
        }
        $periodeIds = $periodeQuery->pluck('id');

        // Query data — tetap flat untuk export
        $query = SummaryBansos::with(['wilayah.parent', 'jenisBansos', 'periode'])
            ->whereIn('periode_id', $periodeIds);

        if ($jenisBansosId) {
            $query->byJenis((int) $jenisBansosId);
        }

        if ($wilayahId) {
            $wilayah = Wilayah::find($wilayahId);
            $query->dibawahWilayah($wilayah);
        } else {
            $query->whereHas('wilayah', fn($q) => $q->byLevel('kabupaten'));
        }

        $data = $query->orderBy('periode_id')->get();

        // Nama file
        $labelWilayah = $wilayahId ? Wilayah::find($wilayahId)->nama : 'Nasional';
        $labelPeriode = !empty($triwulan)
            ? 'TW' . implode('-', $triwulan) . "_{$tahun}"
            : "Tahun{$tahun}";
        $namaFile = "Bansos_{$labelWilayah}_{$labelPeriode}.{$format}";

        return Excel::download(new BansosExport($data), $namaFile);
    }
}