<?php

namespace App\Http\Controllers;

use App\Models\JenisBansos;
use App\Models\Periode;
use App\Models\TargetBansos;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TargetBansosController extends Controller
{
    public function index(Request $request)
    {
        $query = TargetBansos::with(['wilayah', 'jenisBansos', 'periode'])
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

        return view('admin.target.index', compact('data', 'periode', 'jenisBansos', 'provinsi'));
    }

    public function create()
    {
        $periode     = Periode::urut()->get();
        $jenisBansos = JenisBansos::aktif()->get();
        $provinsi    = Wilayah::semuaProvinsi();

        return view('admin.target.form', compact('periode', 'jenisBansos', 'provinsi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'wilayah_id'      => ['required', 'exists:wilayah,id'],
            'jenis_bansos_id' => ['required', 'exists:jenis_bansos,id'],
            'periode_id'      => ['required', 'exists:periode,id'],
            'jumlah_kpm'      => ['required', 'integer', 'min:0'],
            'nominal'         => ['required', 'integer', 'min:0'],
        ]);

        // Pastikan wilayah yang dipilih level kabupaten
        $wilayah = Wilayah::find($validated['wilayah_id']);
        if ($wilayah->level !== 'kabupaten') {
            return back()->withErrors(['wilayah_id' => 'Input hanya bisa dilakukan di level kabupaten/kota.']);
        }

        // Cek duplikat
        $sudahAda = TargetBansos::where([
            'wilayah_id'      => $validated['wilayah_id'],
            'jenis_bansos_id' => $validated['jenis_bansos_id'],
            'periode_id'      => $validated['periode_id'],
        ])->exists();

        if ($sudahAda) {
            return back()->withErrors(['duplikat' => 'Target untuk kombinasi wilayah, jenis bansos, dan periode ini sudah ada. Gunakan fitur edit.']);
        }

        TargetBansos::create(array_merge($validated, [
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]));

        return redirect()->route('admin.target.index')
            ->with('sukses', 'Target berhasil disimpan.');
    }

    public function edit(TargetBansos $target)
    {
        $periode     = Periode::urut()->get();
        $jenisBansos = JenisBansos::aktif()->get();
        $provinsi    = Wilayah::semuaProvinsi();

        return view('admin.target.form', compact('target', 'periode', 'jenisBansos', 'provinsi'));
    }

    public function update(Request $request, TargetBansos $target)
    {
        $validated = $request->validate([
            'jumlah_kpm' => ['required', 'integer', 'min:0'],
            'nominal'    => ['required', 'integer', 'min:0'],
        ]);

        // Wilayah, jenis, dan periode tidak boleh diubah via edit —
        // harus hapus dan buat baru jika salah kombinasi
        $target->update(array_merge($validated, ['updated_by' => Auth::id()]));

        return redirect()->route('admin.target.index')
            ->with('sukses', 'Target berhasil diperbarui.');
    }

    public function destroy(TargetBansos $target)
    {
        $target->delete();

        return redirect()->route('admin.target.index')
            ->with('sukses', 'Target berhasil dihapus.');
    }
}
