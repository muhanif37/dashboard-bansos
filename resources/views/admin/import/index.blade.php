@extends('layouts.app')
@section('title', 'Import Data')
@section('page-title', 'Import Data Bansos')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

    {{-- Form Upload --}}
    <div class="col-span-1">
        <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-5">
            <h3 class="text-sm font-medium text-gray-700 mb-4">Upload File Excel</h3>

            <form action="{{ route('admin.import.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4 p-3 bg-blue-50 border border-blue-100 rounded-md">
                    <p class="text-xs text-blue-700">
                        <strong>Info:</strong> File Excel format long (row-by-row) kolom "Periode"
                        berisi rentang bulan dan sesuai template sebelumnya. Pilih tahun datanya di bawah ini.
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-xs text-gray-500 mb-1">Tahun Data</label>
                    <select name="tahun" required
                            class="w-full text-sm text-gray-700 border border-gray-200 rounded-md px-3 py-2">
                        @for ($t = now()->year + 1; $t >= now()->year - 5; $t--)
                            <option value="{{ $t }}" {{ $t === now()->year ? 'selected' : '' }}>{{ $t }}</option>
                        @endfor
                    </select>
                    @error('tahun')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-5">
                    <label class="block text-xs text-gray-500 mb-1">File Excel (.xlsx)</label>
                    <input type="file" name="file" accept=".xlsx,.xls" required
                           class="w-full text-sm text-gray-600 border border-gray-200 rounded-md px-3 py-2
                                  file:mr-3 file:py-1 file:px-3 file:border-0 file:text-xs
                                  file:bg-gray-100 file:text-gray-600 file:rounded">
                    @error('file')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">Maksimal 10MB. Format sesuai template Kemendagri.</p>
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium
                               py-2 rounded-md transition-colors">
                    Upload & Validasi
                </button>
            </form>
        </div>
    </div>

    {{-- Riwayat Import --}}
    <div class="col-span-1 lg:col-span-2">
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-4 sm:px-5 py-3 border-b border-gray-100">
                <h3 class="text-sm font-medium text-gray-700">Riwayat Import</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[640px]">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">File</th>
                            <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500">Periode</th>
                            <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Valid</th>
                            <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Invalid</th>
                            <th class="text-center px-4 py-2.5 text-xs font-medium text-gray-500">Imported</th>
                            <th class="text-right px-4 py-2.5 text-xs font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $r)
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="px-4 py-3 text-xs text-gray-700 max-w-[160px] sm:max-w-[200px] truncate">
                                    {{ $r->nama_file }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                    {{ $r->periode?->label ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs text-green-600 font-medium">{{ $r->total_valid }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs {{ $r->total_invalid > 0 ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                                        {{ $r->total_invalid }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs text-blue-600 font-medium">{{ $r->total_imported }}</span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if($r->total_valid > 0 && $r->total_imported < $r->total_valid)
                                        <a href="{{ route('admin.import.review', ['namaFile' => $r->nama_file]) }}"
                                           class="text-xs text-blue-600 hover:underline">Review</a>
                                    @else
                                        <span class="text-xs text-gray-300">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400">
                                    Belum ada riwayat import.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection