<?php

namespace App\Livewire\Dashboard;

use App\Models\Periode;
use App\Models\Wilayah;
use Illuminate\Support\Collection;
use Livewire\Component;

class FilterPanel extends Component
{
    // Data untuk dropdown statis
    public $provinsiList    = [];
    public $jenisBansosList = [];
    public $tahunList       = [];

    // State filter aktif
    public int    $tahun          = 0;
    // public ?int   $triwulan       = null;
    public array $triwulanDipilih = [];
    public ?int   $jenisBansosId  = null;
    public ?int   $provinsiId     = null;
    public ?int   $kabupatenId    = null;

    // Data dropdown dinamis (diisi saat provinsi dipilih)
    public array $kabupatenList = [];

    // Event yang dikirim ke sibling components
    protected $listeners = [];

    public function mount(Collection $provinsiList, Collection $jenisBansosList, Collection $tahunList)
    {
        $this->provinsiList    = $provinsiList->map(fn($w) => ['id' => $w->id, 'nama' => $w->nama])->toArray();
        $this->jenisBansosList = $jenisBansosList->map(fn($j) => ['id' => $j->id, 'kode' => $j->kode, 'nama' => $j->nama])->toArray();
        $this->tahunList       = $tahunList->toArray();

        $this->tahun = (int) (Periode::whereHas('targetBansos')
            ->orWhereHas('realisasiBansos')
            ->max('tahun') ?? now()->year);

        // Default ke jenis pertama — tidak boleh null
        if (!$this->jenisBansosId && count($this->jenisBansosList) > 0) {
            $this->jenisBansosId = $this->jenisBansosList[0]['id'];
        }

        $this->terapkan();
    }

    public function resetFilter(): void
    {
        $this->tahun              = (int) (Periode::whereHas('targetBansos')
            ->orWhereHas('realisasiBansos')
            ->max('tahun') ?? now()->year);
        $this->triwulanDipilih    = []; // reset ke kosong = semua triwulan
        $this->jenisBansosId      = count($this->jenisBansosList) > 0
            ? $this->jenisBansosList[0]['id']
            : null;
        $this->provinsiId         = null;
        $this->kabupatenId        = null;
        $this->kabupatenList      = [];
        $this->terapkan();
    }

    /**
     * Saat provinsi berubah, muat daftar kabupaten secara reaktif.
     * Livewire akan memanggil ini otomatis karena nama method: updated{PropertyName}
     */
    public function updatedProvinsiId(?int $value): void
    {
        $this->kabupatenId   = null;
        $this->kabupatenList = [];

        if ($value) {
            $this->kabupatenList = Wilayah::kabupatenByProvinsi($value)
                ->map(fn($w) => ['id' => $w->id, 'nama' => $w->nama])
                ->toArray();
        }

        $this->terapkan();
    }

    public function updatedKabupatenId(): void
    {
        $this->terapkan();
    }

    public function updatedTahun(): void
    {
        $this->terapkan();
    }

    public function updatedTriwulanDipilih(): void
    {
        $this->terapkan();
    }

    public function updatedJenisBansosId(): void
    {
        $this->terapkan();
    }

    /**
     * Kirim event ke semua sibling Livewire components
     * yang mendengarkan 'filter-updated'.
     */
    public function terapkan(): void
    {
        $wilayahId = $this->kabupatenId ?? $this->provinsiId ?? null;

        $filter = [
            'tahun'           => $this->tahun,
            'triwulan'        => $this->triwulanDipilih, // kirim array
            'wilayah_id'      => $wilayahId,
            'jenis_bansos_id' => $this->jenisBansosId,
        ];

        $this->dispatch('filter-updated', ...$filter);
        $this->dispatch('filter-changed', ...$filter);
    }

    public function render()
    {
        return view('livewire.dashboard.filter-panel');
    }
}