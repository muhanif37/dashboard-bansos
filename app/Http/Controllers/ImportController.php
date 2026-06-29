<?php

namespace App\Http\Controllers;

use App\Imports\BansosImport;
use App\Models\JenisBansos;
use App\Models\Periode;
use App\Models\StagingImport;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportController extends Controller
{
    public function __construct(private ImportService $importService) {}

    /**
     * Halaman utama import — tampilkan form upload dan riwayat import.
     */
    public function index()
    {
        $periode     = Periode::urut()->get();
        $jenisBansos = JenisBansos::aktif()->get();

        // Riwayat import: kelompokkan staging per nama_file
        $riwayat = StagingImport::select('nama_file', 'periode_id', 'jenis_bansos_id', 'created_at')
            ->selectRaw('COUNT(*) as total_baris')
            ->selectRaw('SUM(CASE WHEN status = "valid" THEN 1 ELSE 0 END) as total_valid')
            ->selectRaw('SUM(CASE WHEN status = "invalid" THEN 1 ELSE 0 END) as total_invalid')
            ->selectRaw('SUM(CASE WHEN status = "imported" THEN 1 ELSE 0 END) as total_imported')
            ->groupBy('nama_file', 'periode_id', 'jenis_bansos_id', 'created_at')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.import.index', compact('periode', 'jenisBansos', 'riwayat'));
    }

    /**
     * Proses upload file xlsx ke staging table.
     * Setelah upload, langsung jalankan validasi.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file'  => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            // Tahun wajib diisi — format file baru cuma punya rentang bulan
            // di kolom "Periode" (contoh: "Okt-Des"), tidak ada kolom tahun.
            'tahun' => ['required', 'integer', 'min:2020', 'max:2099'],
        ]);

        $file     = $request->file('file');
        $tahun    = (int) $request->tahun;
        $namaFile = time() . '_' . $file->getClientOriginalName();

        // Pastikan folder imports ada sebelum menyimpan file
        $importDir = storage_path('app/private/imports');
        if (!is_dir($importDir)) {
            mkdir($importDir, 0755, true);
        }

        // Simpan file ke storage untuk referensi/audit
        Storage::disk('local')->putFileAs('imports', $file, $namaFile);

        try {
            // Parse xlsx langsung dari file upload (bukan dari storage)
            // lebih aman dan tidak bergantung pada path storage
            Excel::import(
                new BansosImport(
                    namaFile: $namaFile,
                    tahun: $tahun,
                    importService: $this->importService,
                ),
                $file
            );

            // Langsung validasi setelah upload
            $hasil = $this->importService->validasiSemua($namaFile);

            // Kalau tidak ada baris sama sekali, kemungkinan mapping gagal
            if ($hasil['total'] === 0) {
                StagingImport::byFile($namaFile)->delete();
                return back()->with('error', 'Tidak ada data yang berhasil dibaca dari file. Pastikan format file sesuai template.');
            }

            return redirect()
                ->route('admin.import.review', ['namaFile' => $namaFile])
                ->with('sukses_upload', "File berhasil diupload. {$hasil['valid']} baris valid, {$hasil['invalid']} baris perlu diperhatikan.");

        } catch (Throwable $e) {
            // Hapus staging yang mungkin sudah terlanjur masuk
            StagingImport::byFile($namaFile)->delete();
            Storage::disk('local')->delete("imports/{$namaFile}");

            // Log detail error untuk debugging
            \Illuminate\Support\Facades\Log::error('Import gagal', [
                'file'  => $namaFile,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    /**
     * Halaman review hasil validasi sebelum data dipindah ke produksi.
     */
    public function review(string $namaFile)
    {
        // Ambil semua baris staging dari file ini
        $valid   = StagingImport::byFile($namaFile)->valid()->with(['periode', 'jenisBansos'])->get();
        $invalid = StagingImport::byFile($namaFile)->invalid()->with(['periode', 'jenisBansos'])->get();

        if ($valid->isEmpty() && $invalid->isEmpty()) {
            return redirect()->route('admin.import.index')
                ->with('error', 'File tidak ditemukan atau sudah diproses sebelumnya.');
        }

        return view('admin.import.review', compact('namaFile', 'valid', 'invalid'));
    }

    /**
     * Eksekusi pemindahan data valid dari staging ke tabel produksi.
     */
    public function proses(Request $request)
    {
        $request->validate([
            'nama_file' => ['required', 'string'],
        ]);

        $namaFile = $request->nama_file;

        // Pastikan masih ada baris valid yang belum diproses
        $jumlahValid = StagingImport::byFile($namaFile)->valid()->count();

        if ($jumlahValid === 0) {
            return redirect()->route('admin.import.index')
                ->with('error', 'Tidak ada data valid yang bisa diproses untuk file ini.');
        }

        $hasil = $this->importService->prosesImport($namaFile, Auth::id());

        $pesan = "Import selesai: {$hasil['berhasil']} data baru, {$hasil['dilewati']} data diperbarui";
        if ($hasil['gagal'] > 0) {
            $pesan .= ", {$hasil['gagal']} gagal.";
            return redirect()->route('admin.import.index')->with('warning', $pesan);
        }

        return redirect()->route('admin.import.index')->with('sukses', $pesan . '.');
    }

    /**
     * Hapus staging dari file tertentu — dipakai kalau admin mau cancel import.
     */
    public function batalkan(string $namaFile)
    {
        $dihapus = StagingImport::byFile($namaFile)
            ->whereIn('status', [StagingImport::STATUS_PENDING, StagingImport::STATUS_VALID, StagingImport::STATUS_INVALID])
            ->delete();

        return redirect()->route('admin.import.index')
            ->with('sukses', "Import dibatalkan. {$dihapus} baris staging dihapus.");
    }
}