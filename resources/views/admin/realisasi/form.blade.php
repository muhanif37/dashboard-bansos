@extends('layouts.app')
@section('title', isset($realisasi) ? 'Edit Realisasi' : 'Tambah Realisasi')
@section('page-title', isset($realisasi) ? 'Edit Realisasi Bansos' : 'Tambah Realisasi Bansos')

@section('content')
<div class="max-w-xl">
    <div class="bg-white border border-gray-200 rounded-lg p-6">

        <form action="{{ isset($realisasi) ? route('admin.realisasi.update', $realisasi) : route('admin.realisasi.store') }}"
              method="POST">
            @csrf
            @if(isset($realisasi)) @method('PUT') @endif

            {{-- Wilayah — hanya saat create --}}
            @unless(isset($realisasi))
                <div class="mb-4">
                    <label class="block text-xs text-gray-500 mb-1">Provinsi</label>
                    <select id="sel-provinsi"
                            class="w-full text-sm border border-gray-200 rounded-md px-3 py-2">
                        <option value="">Pilih provinsi...</option>
                        @foreach($provinsi as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-xs text-gray-500 mb-1">Kabupaten/Kota</label>
                    <select id="sel-kabupaten" name="wilayah_id" required
                            class="w-full text-sm border border-gray-200 rounded-md px-3 py-2">
                        <option value="">Pilih provinsi dulu...</option>
                    </select>
                    @error('wilayah_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-xs text-gray-500 mb-1">Jenis Bansos</label>
                    <select name="jenis_bansos_id" required
                            class="w-full text-sm border border-gray-200 rounded-md px-3 py-2">
                        <option value="">Pilih jenis...</option>
                        @foreach($jenisBansos as $j)
                            <option value="{{ $j->id }}">{{ $j->nama }}</option>
                        @endforeach
                    </select>
                    @error('jenis_bansos_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-xs text-gray-500 mb-1">Periode</label>
                    <select name="periode_id" required
                            class="w-full text-sm border border-gray-200 rounded-md px-3 py-2">
                        <option value="">Pilih periode...</option>
                        @foreach($periode as $p)
                            <option value="{{ $p->id }}">{{ $p->label }}</option>
                        @endforeach
                    </select>
                    @error('periode_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @else
                <div class="mb-4 p-3 bg-gray-50 rounded-md">
                    <p class="text-xs text-gray-500">Wilayah</p>
                    <p class="text-sm font-medium text-gray-700 mt-0.5">
                        {{ $realisasi->wilayah->nama }} — {{ $realisasi->wilayah->parent->nama }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Tidak bisa diubah saat edit.</p>
                </div>
                <div class="mb-4 p-3 bg-gray-50 rounded-md">
                    <p class="text-xs text-gray-500">Periode & Jenis Bansos</p>
                    <p class="text-sm font-medium text-gray-700 mt-0.5">
                        {{ $realisasi->jenisBansos->kode }} — {{ $realisasi->periode->label }}
                    </p>
                </div>
            @endunless

            {{-- Jumlah KPM --}}
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Jumlah KPM</label>
                <input type="number" name="jumlah_kpm" min="0" required
                       value="{{ old('jumlah_kpm', $realisasi->jumlah_kpm ?? '') }}"
                       class="w-full text-sm border border-gray-200 rounded-md px-3 py-2"
                       placeholder="0">
                @error('jumlah_kpm')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nominal --}}
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Nominal (Rp)</label>
                <input type="number" name="nominal" min="0" required
                       value="{{ old('nominal', $realisasi->nominal ?? '') }}"
                       class="w-full text-sm border border-gray-200 rounded-md px-3 py-2"
                       placeholder="0">
                @error('nominal')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Realisasi --}}
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Tanggal Realisasi <span class="text-gray-300">(opsional)</span></label>
                <input type="date" name="tanggal_realisasi"
                       value="{{ old('tanggal_realisasi', isset($realisasi) ? $realisasi->tanggal_realisasi?->format('Y-m-d') : '') }}"
                       class="w-full text-sm border border-gray-200 rounded-md px-3 py-2">
                @error('tanggal_realisasi')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sumber Data --}}
            <div class="mb-6">
                <label class="block text-xs text-gray-500 mb-1">Sumber Data <span class="text-gray-300">(opsional)</span></label>
                <input type="text" name="sumber_data"
                       value="{{ old('sumber_data', $realisasi->sumber_data ?? '') }}"
                       class="w-full text-sm border border-gray-200 rounded-md px-3 py-2"
                       placeholder="Contoh: Laporan Kemensos April 2025">
                @error('sumber_data')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-md">
                    {{ isset($realisasi) ? 'Simpan Perubahan' : 'Tambah Realisasi' }}
                </button>
                <a href="{{ route('admin.realisasi.index') }}"
                   class="text-sm text-gray-500 border border-gray-200 px-5 py-2 rounded-md hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Dropdown kabupaten bertingkat --}}
@unless(isset($realisasi))
<script>
document.getElementById('sel-provinsi').addEventListener('change', async function () {
    const provinsiId = this.value;
    const kabupatenSel = document.getElementById('sel-kabupaten');

    kabupatenSel.innerHTML = '<option value="">Memuat...</option>';

    if (!provinsiId) {
        kabupatenSel.innerHTML = '<option value="">Pilih provinsi dulu...</option>';
        return;
    }

    const res  = await fetch(`/wilayah/anak/${provinsiId}`);
    const data = await res.json();

    kabupatenSel.innerHTML = '<option value="">Pilih kabupaten/kota...</option>';
    data.forEach(k => {
        kabupatenSel.innerHTML += `<option value="${k.id}">${k.nama}</option>`;
    });
});
</script>
@endunless

@endsection