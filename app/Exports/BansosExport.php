<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BansosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $data) {}

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Provinsi',
            'Kabupaten/Kota',
            'Jenis Bansos',
            'Periode',
            // Alokasi SP2D
            'Alokasi KPM (SP2D)',
            'Alokasi Nominal (Rp)',
            // Penyaluran
            'Penyaluran KPM',
            'Penyaluran Nominal (Rp)',
            '% Penyaluran KPM',
            // Pencairan / Realisasi
            'Pencairan KPM',
            'Pencairan Nominal (Rp)',
            'Selisih KPM',
            'Selisih Nominal (Rp)',
            '% Pencairan KPM',
            '% Pencairan Nominal',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->wilayah?->parent?->nama ?? '-',
            $row->wilayah?->nama ?? '-',
            $row->jenisBansos?->nama ?? '-',
            $row->periode?->label ?? '-',
            // Alokasi SP2D
            $row->target_kpm,
            $row->target_nominal,
            // Penyaluran
            $row->penyaluran_kpm,
            $row->penyaluran_nominal,
            number_format($row->pct_penyaluran_kpm, 2) . '%',
            // Pencairan
            $row->realisasi_kpm,
            $row->realisasi_nominal,
            $row->selisih_kpm,
            $row->selisih_nominal,
            number_format($row->pct_kpm, 2) . '%',
            number_format($row->pct_nominal, 2) . '%',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->data->count() + 1;

        return [
            // Bold header
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A5F']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ],
            // Semua baris data rata tengah untuk kolom angka
            "F2:P{$lastRow}" => [
                'alignment' => ['horizontal' => 'right'],
            ],
        ];
    }
}