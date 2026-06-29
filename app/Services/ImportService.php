<?php

namespace App\Services;

use App\Models\RealisasiBansos;
use App\Models\StagingImport;
use App\Models\TargetBansos;
use App\Models\Wilayah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportService
{
    public function __construct(private SummaryService $summaryService) {}

    public function simpanKeStaging(
        string $namaFile,
        int $periodeId,
        int $jenisBansosId,
        array $rawData
    ): StagingImport {
        return StagingImport::create([
            'nama_file'       => $namaFile,
            'periode_id'      => $periodeId,
            'jenis_bansos_id' => $jenisBansosId,
            'raw_data'        => $rawData,
            'status'          => StagingImport::STATUS_PENDING,
        ]);
    }

    public function validasiSemua(string $namaFile): array
    {
        $baris = StagingImport::byFile($namaFile)->pending()->get();

        $valid   = 0;
        $invalid = 0;

        $kodeWilayahValid = Wilayah::byLevel('kabupaten')
            ->pluck('id', 'kode_dagri')
            ->toArray();

        foreach ($baris as $staging) {
            $errors = $this->validasiBaris($staging->raw_data, $kodeWilayahValid);

            if (empty($errors)) {
                $staging->tandaiValid();
                $valid++;
            } else {
                $staging->tandaiInvalid($errors);
                $invalid++;
            }
        }

        return [
            'total'   => $baris->count(),
            'valid'   => $valid,
            'invalid' => $invalid,
        ];
    }

    private function validasiBaris(array $data, array $kodeWilayahValid): array
    {
        $errors = [];

        $kodeDagri = trim($data['kode'] ?? '');
        if (empty($kodeDagri)) {
            $errors[] = 'Kode Kemendagri tidak boleh kosong.';
        } elseif (!isset($kodeWilayahValid[$kodeDagri])) {
            $errors[] = "Kode Kemendagri '{$kodeDagri}' tidak ditemukan di data wilayah kabupaten/kota.";
        }

        if (!isset($data['jumlah_kpm']) || !is_numeric($data['jumlah_kpm'])) {
            $errors[] = 'Jumlah KPM harus berupa angka.';
        } elseif ((int) $data['jumlah_kpm'] < 0) {
            $errors[] = 'Jumlah KPM tidak boleh negatif.';
        }

        if (!isset($data['nominal']) || !is_numeric($data['nominal'])) {
            $errors[] = 'Nominal harus berupa angka.';
        } elseif ((int) $data['nominal'] < 0) {
            $errors[] = 'Nominal tidak boleh negatif.';
        }

        return $errors;
    }

    public function prosesImport(string $namaFile, int $userId): array
    {
        $baris = StagingImport::byFile($namaFile)->valid()->get();

        if ($baris->isEmpty()) {
            return ['berhasil' => 0, 'dilewati' => 0, 'gagal' => 0];
        }

        $kodeKeId = Wilayah::byLevel('kabupaten')->pluck('id', 'kode_dagri')->toArray();

        $berhasil = 0;
        $dilewati = 0;
        $gagal    = 0;

        foreach ($baris as $staging) {
            try {
                DB::transaction(function () use ($staging, $kodeKeId, $userId, &$berhasil, &$dilewati) {
                    $data      = $staging->raw_data;
                    $wilayahId = $kodeKeId[trim($data['kode'])];
                    $periodeId = $staging->periode_id;
                    $jenisId   = $staging->jenis_bansos_id;
                    $jeniData  = $data['jenis_data'] ?? 'realisasi';

                    if ($jeniData === 'target') {
                        $payload = [
                            'wilayah_id'      => $wilayahId,
                            'jenis_bansos_id' => $jenisId,
                            'periode_id'      => $periodeId,
                            'jumlah_kpm'      => (int) $data['jumlah_kpm'],
                            'nominal'         => (int) $data['nominal'],
                            'sumber_data'     => $staging->nama_file,
                            'created_by'      => $userId,
                            'updated_by'      => $userId,
                        ];

                        $existing = TargetBansos::where([
                            'wilayah_id'      => $wilayahId,
                            'jenis_bansos_id' => $jenisId,
                            'periode_id'      => $periodeId,
                        ])->first();

                        $existing ? $existing->update($payload) : TargetBansos::create($payload);

                    } else {
                        // Realisasi: sertakan penyaluran + pencairan
                        $payload = [
                            'wilayah_id'          => $wilayahId,
                            'jenis_bansos_id'     => $jenisId,
                            'periode_id'          => $periodeId,
                            'penyaluran_kpm'      => (int) ($data['penyaluran_kpm'] ?? 0),
                            'penyaluran_nominal'  => (int) ($data['penyaluran_nominal'] ?? 0),
                            'jumlah_kpm'          => (int) $data['jumlah_kpm'],
                            'nominal'             => (int) $data['nominal'],
                            'sumber_data'         => $staging->nama_file,
                            'created_by'          => $userId,
                            'updated_by'          => $userId,
                        ];

                        $existing = RealisasiBansos::where([
                            'wilayah_id'      => $wilayahId,
                            'jenis_bansos_id' => $jenisId,
                            'periode_id'      => $periodeId,
                        ])->first();

                        if ($existing) {
                            $existing->update($payload);
                            $dilewati++;
                        } else {
                            RealisasiBansos::create($payload);
                            $berhasil++;
                        }
                    }

                    $staging->tandaiImported($userId);
                });
            } catch (Throwable $e) {
                Log::error('ImportService: gagal proses baris staging', [
                    'staging_id' => $staging->id,
                    'error'      => $e->getMessage(),
                ]);
                $staging->tandaiInvalid(['Gagal saat insert ke database: ' . $e->getMessage()]);
                $gagal++;
            }
        }

        return [
            'berhasil' => $berhasil,
            'dilewati' => $dilewati,
            'gagal'    => $gagal,
        ];
    }

    public function bersihkanStaging(int $hariLalu = 30): int
    {
        return StagingImport::where('status', StagingImport::STATUS_IMPORTED)
            ->where('imported_at', '<', now()->subDays($hariLalu))
            ->delete();
    }

    public function catatGagal(string $namaFile, array $info): void
    {
        Log::warning('Import: baris dilewati saat parsing', array_merge(
            ['nama_file' => $namaFile],
            $info
        ));
    }
}