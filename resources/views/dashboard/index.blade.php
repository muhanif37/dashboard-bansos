@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Monitoring Penyaluran Bansos')

@section('topbar-actions')
    <div x-data x-on:filter-changed.window="
        const p = event.detail;
        const url = new URL('{{ route('export.unduh') }}', window.location.origin);
        if (p.tahun)           url.searchParams.set('tahun', p.tahun);
        if (p.wilayah_id)      url.searchParams.set('wilayah_id', p.wilayah_id);
        if (p.jenis_bansos_id) url.searchParams.set('jenis_bansos_id', p.jenis_bansos_id);
        if (p.triwulan && p.triwulan.length > 0) {
            url.searchParams.set('triwulan', p.triwulan.join(','));
        }
        $refs.linkExport.href = url.toString();
    ">
        <a x-ref="linkExport"
           href="{{ route('export.unduh', ['tahun' => now()->year]) }}"
           class="btn-export"
           title="Unduh Excel">
            <svg style="width:13px;height:13px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span class="hidden sm:inline">Unduh Excel</span>
        </a>
    </div>
@endsection

@section('content')
    @livewire('dashboard.filter-panel', [
        'provinsiList'    => $provinsi,
        'jenisBansosList' => $jenisBansos,
        'tahunList'       => $tahunList,
    ])

    @livewire('dashboard.metric-cards')

    <div class="mt-4">
        @livewire('dashboard.trend-chart')
    </div>

    <div class="mt-4">
        @livewire('dashboard.data-table')
    </div>
@endsection
