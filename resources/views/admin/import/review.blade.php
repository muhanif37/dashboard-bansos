@extends('layouts.app')
@section('title', 'Review Import')
@section('page-title', 'Review Data Import')

@section('content')
<div class="space-y-4">

    {{-- Ringkasan --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 flex items-center gap-6">
        <div>
            <p class="text-xs text-gray-500">File</p>
            <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $namaFile }}</p>
        </div>
        <div class="w-px h-10 bg-gray-200"></div>
        <div>
            <p class="text-xs text-gray-500">Baris Valid</p>
            <p class="text-lg font-semibold text-green-600">{{ $valid->count() }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Baris Invalid</p>
            <p class="text-lg font-semibold text-red-500">{{ $invalid->count() }}</p>
        </div>
        <div class="ml-auto flex gap-3">
            {{-- Batalkan --}}
            <form action="{{ route('admin.import.batalkan', ['namaFile' => $namaFile]) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        onclick="return confirm('Batalkan import ini? Semua data staging akan dihapus.')"
                        class="text-sm text-red-500 border border-red-200 px-4 py-1.5 rounded-md hover:bg-red-50">
                    Batalkan
                </button>
            </form>

            {{-- Proses import --}}
            @if($valid->count() > 0)
                <form action="{{ route('admin.import.proses') }}" method="POST">
                    @csrf
                    <input type="hidden" name="nama_file" value="{{ $namaFile }}">
                    <button type="submit"
                            onclick="return confirm('Proses {{ $valid->count() }} baris valid ke database produksi?')"
                            class="text-sm text-white bg-blue-600 hover:bg-blue-700 px-4 py-1.5 rounded-md">
                        Proses {{ $valid->count() }} Baris Valid
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Baris Invalid (tampil duluan supaya admin bisa perhatikan) --}}
    @if($invalid->count() > 0)
        <div class="bg-white border border-red-200 rounded-lg">
            <div class="px-4 py-3 border-b border-red-100 bg-red-50 rounded-t-lg">
                <h3 class="text-sm font-medium text-red-700">
                    Baris Tidak Valid ({{ $invalid->count() }}) — tidak akan diproses
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-4 py-2 text-xs text-gray-500">Kode Kemendagri</th>
                            <th class="text-left px-4 py-2 text-xs text-gray-500">Kabupaten</th>
                            <th class="text-left px-4 py-2 text-xs text-gray-500">Jenis</th>
                            <th class="text-left px-4 py-2 text-xs text-gray-500">Alasan Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invalid as $baris)
                            <tr class="border-b border-gray-50">
                                <td class="px-4 py-2.5 text-xs text-gray-600">{{ $baris->raw_data['kode'] ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-600">{{ $baris->raw_data['kabupaten'] ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-600">{{ $baris->jenisBansos?->kode }}</td>
                                <td class="px-4 py-2.5 text-xs text-red-500">
                                    {{ implode(', ', $baris->error_log ?? []) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Preview baris valid --}}
    @if($valid->count() > 0)
        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="px-4 py-3 border-b border-gray-100">
                <h3 class="text-sm font-medium text-gray-700">
                    Preview Baris Valid ({{ $valid->count() }})
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-4 py-2 text-xs text-gray-500">Kode Kemendagri</th>
                            <th class="text-left px-4 py-2 text-xs text-gray-500">Kabupaten</th>
                            <th class="text-left px-4 py-2 text-xs text-gray-500">Jenis</th>
                            <th class="text-left px-4 py-2 text-xs text-gray-500">Tipe</th>
                            <th class="text-right px-4 py-2 text-xs text-gray-500">KPM</th>
                            <th class="text-right px-4 py-2 text-xs text-gray-500">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($valid->take(50) as $baris)
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="px-4 py-2.5 text-xs text-gray-600">{{ $baris->raw_data['kode'] ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-600">{{ $baris->raw_data['kabupaten'] ?? '-' }}</td>
                                <td class="px-4 py-2.5 text-xs">
                                    <span class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-600">
                                        {{ $baris->jenisBansos?->kode }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-600 capitalize">
                                    {{ $baris->raw_data['jenis_data'] ?? '-' }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-right text-gray-600 tabular-nums">
                                    {{ number_format($baris->raw_data['jumlah_kpm'] ?? 0) }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-right text-gray-600 tabular-nums">
                                    {{ number_format($baris->raw_data['nominal'] ?? 0) }}
                                </td>
                            </tr>
                        @endforeach
                        @if($valid->count() > 50)
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-center text-xs text-gray-400">
                                    ... dan {{ $valid->count() - 50 }} baris lainnya
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
@endsection
