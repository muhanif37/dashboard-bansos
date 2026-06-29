<?php

namespace App\Imports;

use App\Services\ImportService;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BansosImport implements ToCollection, WithHeadingRow
{
    private string $namaFile;
    private int $tahun;
    private ImportService $importService;

    private array $jenisBansosMap = [];
    private array $periodeMap = [];

    private const PROGRAM_KE_KODE = [
        'PKH'              => 'PKH',
        'SEMBAKO'          => 'SEMBAKO',
        'STIMULUS SEMBAKO' => 'STIMULUS_SEMBAKO',
        'STIMULUS SMB'     => 'STIMULUS_SEMBAKO',
        'BLT KESRA'        => 'BLT_KESRA',
    ];

    private const PERIODE_KE_TRIWULAN = [
        'JAN-MAR' => 1,
        'APR-JUN' => 2,
        'JUL-SEP' => 3,
        'OKT-DES' => 4,
        'JUN-JUL' => 2,
    ];

    public function __construct(string $namaFile, int $tahun, ImportService $importService)
    {
        $this->namaFile      = $namaFile;
        $this->tahun         = $tahun;
        $this->importService = $importService;

        $this->jenisBansosMap = \App\Models\JenisBansos::aktif()->pluck('id', 'kode')->toArray();

        $this->periodeMap = \App\Models\Periode::all()
            ->mapWithKeys(fn($p) => [$p->tahun . '_' . $p->triwulan => $p->id])
            ->toArray();
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $r = $row->toArray();

            $kodeDagri   = trim((string) ($r['kode_kab_kota_kemendagri'] ?? $r['kode_kabkota_kemendagri'] ?? ''));
            $kabupaten   = trim((string) ($r['kotakabupaten'] ?? $r['kota_kabupaten'] ?? ''));
            $periodeTeks = trim((string) ($r['periode'] ?? ''));
            $programTeks = trim((string) ($r['program'] ?? ''));

            if (empty($kodeDagri) || !is_numeric($kodeDagri)) continue;
            if (empty($periodeTeks) || empty($programTeks)) continue;

            $jenisBansosId = $this->cariJenisBansosId($programTeks);
            $periodeId     = $this->cariPeriodeId($periodeTeks);

            if (!$jenisBansosId || !$periodeId) {
                $this->importService->catatGagal($this->namaFile, [
                    'kode'   => $kodeDagri,
                    'alasan' => !$jenisBansosId
                        ? "Program tidak dikenali: \"{$programTeks}\""
                        : "Periode tidak dikenali: \"{$periodeTeks}\"",
                ]);
                continue;
            }

            $targetKpm        = $this->bersihkanAngka($r['sp2d_kpm'] ?? 0);
            $targetNominal    = $this->bersihkanAngka($r['sp2d_nominal_rp'] ?? $r['sp2d_nominal'] ?? 0);
            $penyaluranKpm    = $this->bersihkanAngka($r['penyaluran_kpm'] ?? 0);
            $penyaluranNominal = $this->bersihkanAngka($r['penyaluran_nominal_rp'] ?? $r['penyaluran_nominal'] ?? 0);
            $realisasiKpm     = $this->bersihkanAngka($r['pencairan_kpm'] ?? 0);
            $realisasiNominal = $this->bersihkanAngka($r['pencairan_nominal_rp'] ?? $r['pencairan_nominal'] ?? 0);

            if ($targetKpm === 0 && $realisasiKpm === 0) continue;

            // Simpan TARGET (SP2D)
            $this->importService->simpanKeStaging(
                $this->namaFile,
                $periodeId,
                $jenisBansosId,
                [
                    'kode'       => $kodeDagri,
                    'kabupaten'  => $kabupaten,
                    'jenis_data' => 'target',
                    'jumlah_kpm' => $targetKpm,
                    'nominal'    => $targetNominal,
                ]
            );

            // Simpan REALISASI (Penyaluran + Pencairan)
            $this->importService->simpanKeStaging(
                $this->namaFile,
                $periodeId,
                $jenisBansosId,
                [
                    'kode'                => $kodeDagri,
                    'kabupaten'           => $kabupaten,
                    'jenis_data'          => 'realisasi',
                    'penyaluran_kpm'      => $penyaluranKpm,
                    'penyaluran_nominal'  => $penyaluranNominal,
                    'jumlah_kpm'          => $realisasiKpm,
                    'nominal'             => $realisasiNominal,
                ]
            );
        }
    }

    private function cariJenisBansosId(string $programTeks): ?int
    {
        $key  = preg_replace('/\s+/', ' ', Str::upper(trim($programTeks)));
        $kode = self::PROGRAM_KE_KODE[$key] ?? null;
        return $kode ? ($this->jenisBansosMap[$kode] ?? null) : null;
    }

    private function cariPeriodeId(string $periodeTeks): ?int
    {
        $bersih   = Str::upper(preg_replace('/\s+/', '', $periodeTeks));
        $triwulan = null;

        foreach (self::PERIODE_KE_TRIWULAN as $pola => $tw) {
            if (Str::contains($bersih, $pola)) {
                $triwulan = $tw;
                break;
            }
        }

        return $triwulan ? ($this->periodeMap[$this->tahun . '_' . $triwulan] ?? null) : null;
    }

    private function bersihkanAngka(mixed $nilai): int
    {
        if (is_null($nilai) || $nilai === '') return 0;
        if (is_float($nilai) || is_int($nilai)) return (int) $nilai;
        $bersih = preg_replace('/[^0-9]/', '', (string) $nilai);
        return (int) ($bersih ?: 0);
    }
}