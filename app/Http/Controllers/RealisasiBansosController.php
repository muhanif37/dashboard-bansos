<?php

namespace App\Http\Controllers;

use App\Models\JenisBansos;
use App\Models\Periode;
use App\Models\RealisasiBansos;
use App\Models\Wilayah;
use App\Services\SummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RealisasiBansosController extends Controller
{
    public function __construct(private SummaryService $summaryService)
    {
    }

    public function index(Request $request)
    {
        $query = RealisasiBansos::with(['wilayah', 'jenisBansos', 'periode'])
            ->whereHas('wilayah', fn($q) => $q->byLevel('kabupaten'));

        if ($request->filled('periode_id')) {
            $query->byPeriode((int) $request->periode_id);
        }

        if ($request->filled('jenis_bansos_id')) {
            $query->byJenis((int) $request->jenis_bansos_id);
        }

        if ($request->filled('wilayah_id')) {
            $query->byWilayah((int) $request->wilayah_id);
        }

        $data        = $query->orderBy('periode_id')->paginate(25)->withQueryString();
        $periode     = Periode::urut()->get();
        $jenisBansos = JenisBansos::aktif()->get();
        $provinsi    = Wilayah::semuaProvinsi();

        return view('admin.realisasi.index', compact('data', 'periode', 'jenisBansos', 'provinsi'));
    }

    public function create()
    {
        $periode     = Periode::urut()->get();
        $jenisBansos = JenisBansos::aktif()->get();
        $provinsi    = Wilayah::semuaProvinsi();

        return view('admin.realisasi.form', compact('periode', 'jenisBansos', 'provinsi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'wilayah_id'        => ['required', 'exists:wilayah,id'],
            'jenis_bansos_id'   => ['required', 'exists:jenis_bansos,id'],
            'periode_id'        => ['required', 'exists:periode,id'],
            'jumlah_kpm'        => ['required', 'integer', 'min:0'],
            'nominal'           => ['required', 'integer', 'min:0'],
            'tanggal_realisasi' => ['nullable', 'date'],
            'sumber_data'       => ['nullable', 'string', 'max:255'],
        ]);

        $wilayah = Wilayah::find($validated['wilayah_id']);
        if ($wilayah->level !== 'kabupaten') {
            return back()->withErrors(['wilayah_id' => 'Input hanya bisa dilakukan di level kabupaten/kota.']);
        }

        $sudahAda = RealisasiBansos::where([
            'wilayah_id'      => $validated['wilayah_id'],
            'jenis_bansos_id' => $validated['jenis_bansos_id'],
            'periode_id'      => $validated['periode_id'],
        ])->exists();

        if ($sudahAda) {
            return back()->withErrors(['duplikat' => 'Realisasi untuk kombinasi ini sudah ada. Gunakan fitur edit.']);
        }

        RealisasiBansos::create(array_merge($validated, [
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]));

        $this->summaryService->hitung(
            (int) $validated['wilayah_id'],
            (int) $validated['jenis_bansos_id'],
            (int) $validated['periode_id']
        );

        return redirect()->route('admin.realisasi.index')
            ->with('sukses', 'Realisasi berhasil disimpan.');
    }

    public function edit(RealisasiBansos $realisasi)
    {
        $periode     = Periode::urut()->get();
        $jenisBansos = JenisBansos::aktif()->get();
        $provinsi    = Wilayah::semuaProvinsi();

        return view('admin.realisasi.form', compact('realisasi', 'periode', 'jenisBansos', 'provinsi'));
    }

    public function update(Request $request, RealisasiBansos $realisasi)
    {
        $validated = $request->validate([
            'jumlah_kpm'        => ['required', 'integer', 'min:0'],
            'nominal'           => ['required', 'integer', 'min:0'],
            'tanggal_realisasi' => ['nullable', 'date'],
            'sumber_data'       => ['nullable', 'string', 'max:255'],
        ]);

        $realisasi->update(array_merge($validated, ['updated_by' => Auth::id()]));

        $this->summaryService->hitung(
            $realisasi->wilayah_id,
            $realisasi->jenis_bansos_id,
            $realisasi->periode_id
        );

        return redirect()->route('admin.realisasi.index')
            ->with('sukses', 'Realisasi berhasil diperbarui.');
    }

    public function destroy(RealisasiBansos $realisasi)
    {
        $wilayahId      = $realisasi->wilayah_id;
        $jenisBansosId  = $realisasi->jenis_bansos_id;
        $periodeId      = $realisasi->periode_id;

        $realisasi->delete();

        $this->summaryService->hitung($wilayahId, $jenisBansosId, $periodeId);

        return redirect()->route('admin.realisasi.index')
            ->with('sukses', 'Realisasi berhasil dihapus.');
    }
}