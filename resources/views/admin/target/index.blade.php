@extends('layouts.app')
@section('title', 'Data Target')
@section('page-title', 'Data Target Bansos')

@section('topbar-actions')
    <a href="{{ route('admin.target.create') }}"
       class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-md hover:bg-blue-700 whitespace-nowrap">
        + Tambah Target
    </a>
@endsection

@section('content')

{{-- Filter --}}
<div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">
    <form method="GET" action="{{ route('admin.target.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="w-full sm:w-auto">
            <label class="block text-xs text-gray-500 mb-1">Periode</label>
            <select name="periode_id" class="w-full sm:w-auto text-sm border border-gray-200 rounded-md pe-8 py-1.5">
                <option value="">Semua periode</option>
                @foreach($periode as $p)
                    <option value="{{ $p->id }}" {{ request('periode_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-auto">
            <label class="block text-xs text-gray-500 mb-1">Jenis Bansos</label>
            <select name="jenis_bansos_id" class="w-full sm:w-auto text-sm border border-gray-200 rounded-md pe-8 py-1.5">
                <option value="">Semua jenis</option>
                @foreach($jenisBansos as $j)
                    <option value="{{ $j->id }}" {{ request('jenis_bansos_id') == $j->id ? 'selected' : '' }}>
                        {{ $j->kode }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button type="submit"
                    class="text-sm bg-gray-800 text-white px-4 py-1.5 rounded-md hover:bg-gray-700">
                Filter
            </button>
            <a href="{{ route('admin.target.index') }}"
               class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
        </div>
    </form>
</div>

{{-- Tabel --}}
<div class="bg-white border border-gray-200 rounded-lg">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[820px]">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">Kabupaten/Kota</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">Jenis</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">Periode</th>
                    <th class="text-right px-4 py-3 text-xs font-medium text-gray-500">Target KPM</th>
                    <th class="text-right px-4 py-3 text-xs font-medium text-gray-500">Nominal (Rp)</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">Diinput oleh</th>
                    <th class="text-center px-4 py-3 text-xs font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <p class="text-xs font-medium text-gray-800">{{ $row->wilayah?->nama }}</p>
                            <p class="text-xs text-gray-400">{{ $row->wilayah?->parent?->nama }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-1.5 py-0.5 bg-gray-100 rounded text-gray-600">
                                {{ $row->jenisBansos?->kode }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">{{ $row->periode?->label }}</td>
                        <td class="px-4 py-3 text-right text-xs tabular-nums text-gray-600">
                            {{ number_format($row->jumlah_kpm,0,',','.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-xs tabular-nums text-gray-600">
                            {{ number_format($row->nominal, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">
                            {{ $row->createdBy?->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-3 whitespace-nowrap">
                                <a href="{{ route('admin.target.edit', $row) }}"
                                   class="text-xs text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('admin.target.destroy', $row) }}" method="POST"
                                      onsubmit="return confirm('Hapus data target ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-400">
                            Belum ada data target. <a href="{{ route('admin.target.create') }}"
                            class="text-blue-500 hover:underline">Tambah sekarang</a> atau gunakan fitur Import.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($data->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $data->onEachSide(1)->links('vendor.pagination.custom-plain') }}
        </div>
    @endif
</div>

@endsection