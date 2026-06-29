{{--
    Form ini dipakai untuk create dan edit.
    Jika $target ada → mode edit. Jika tidak ada → mode create.
    Hal yang sama berlaku untuk view admin/realisasi/form.blade.php
--}}
@extends('layouts.app')
@section('title', isset($target) ? 'Edit Target' : 'Tambah Target')
@section('page-title', isset($target) ? 'Edit Target Bansos' : 'Tambah Target Bansos')

@section('content')
<div class="max-w-xl">
    <div class="bg-white border border-gray-200 rounded-lg p-6">

        <form action="{{ isset($target) ? route('admin.target.update', $target) : route('admin.target.store') }}"
              method="POST">
            @csrf
            @if(isset($target)) @method('PUT') @endif

            {{-- Wilayah — hanya tampil saat create --}}
            @unless(isset($target))
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
            @else
                {{-- Edit: tampilkan nama saja, tidak bisa diubah --}}
                <div class="mb-4 p-3 bg-gray-50 rounded-md">
                    <p class="text-xs text-gray-500">Wilayah</p>
                    <p class="text-sm font-medium text-gray-700 mt-0.5">
                        {{ $target->wilayah->nama }} — {{ $target->wilayah->parent->nama }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Tidak bisa diubah saat edit.</p>
                </div>
            @endunless

            {{-- Jenis Bansos --}}
            @unless(isset($target))
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
                    <p class="text-xs text-gray-500">Periode & Jenis Bansos</p>
                    <p class="text-sm font-medium text-gray-700 mt-0.5">
                        {{ $target->jenisBansos->kode }} — {{ $target->periode->label }}
                    </p>
                </div>
            @endunless

            {{-- Jumlah KPM --}}
            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1">Jumlah KPM</label>
                <input type="number" name="jumlah_kpm" min="0" required
                       value="{{ old('jumlah_kpm', $target->jumlah_kpm ?? '') }}"
                       class="w-full text-sm border border-gray-200 rounded-md px-3 py-2"
                       placeholder="0">
                @error('jumlah_kpm')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nominal --}}
            <div class="mb-6">
                <label class="block text-xs text-gray-500 mb-1">Nominal (Rp)</label>
                <input type="number" name="nominal" min="0" required
                       value="{{ old('nominal', $target->nominal ?? '') }}"
                       class="w-full text-sm border border-gray-200 rounded-md px-3 py-2"
                       placeholder="0">
                @error('nominal')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-md">
                    {{ isset($target) ? 'Simpan Perubahan' : 'Tambah Target' }}
                </button>
                <a href="{{ route('admin.target.index') }}"
                   class="text-sm text-gray-500 border border-gray-200 px-5 py-2 rounded-md hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Script dropdown kabupaten bertingkat --}}
@unless(isset($target))
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