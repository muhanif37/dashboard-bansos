<div class="bg-white border border-gray-200 rounded-lg" wire:loading.class="opacity-50">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 px-4 py-3 border-b border-gray-100">
        <h3 class="text-sm font-medium text-gray-700">Rincian per Kabupaten/Kota</h3>
        <div class="flex items-center gap-3">
            <div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
                <button @click="open = !open" type="button"
                        class="inline-flex items-center justify-between w-20 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md pl-2.5 pr-2 py-1 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-900">
                    <span>{{ $perHalaman }}</span>
                    <svg class="w-3.5 h-3.5 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute left-0 z-50 w-20 mt-1 origin-top-left bg-white rounded-lg shadow-xl border border-gray-100 py-1"
                     style="display:none">
                    @foreach([25, 50, 100] as $n)
                        <button type="button" @click="$wire.set('perHalaman', {{ $n }}); open = false"
                                class="w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50
                                       {{ $perHalaman == $n ? 'bg-blue-50/60 font-semibold text-blue-900' : '' }}">
                            {{ $n }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <div class="overflow-y-auto" style="max-height:480px">
            <table class="w-full text-sm" style="min-width: {{ 300 + count($periodeAda) * 420 }}px">
                <thead class="sticky top-0 z-10">

                    {{-- Baris 1: header grup --}}
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 sticky left-0 z-20 bg-gray-50 border-r border-gray-200"
                            rowspan="2">Kabupaten/Kota</th>
                        <th class="text-left px-4 py-2.5 text-xs font-medium text-gray-500 bg-gray-50"
                            rowspan="2">Jenis</th>

                        @foreach($periodeAda as $periode)
                            <th colspan="5"
                                class="text-center px-4 py-2 text-xs font-semibold bg-gray-50 border-l border-gray-200"
                                style="color:#1e3a5f">
                                {{ $periode->label }}
                            </th>
                        @endforeach

                        @if(count($periodeAda) > 1)
                            <th class="text-center px-4 py-2 text-xs font-medium text-gray-500 bg-gray-50 border-l border-gray-200"
                                rowspan="2">Tren</th>
                        @endif
                    </tr>

                    {{-- Baris 2: sub-header per periode --}}
                    <tr class="bg-gray-50 border-b border-gray-200">
                        @foreach($periodeAda as $periode)
                            <th class="text-right px-3 py-2 text-xs font-medium text-gray-400 bg-gray-50 border-l border-gray-200 whitespace-nowrap">
                                Alokasi (KPM)
                            </th>
                            <th class="text-right px-3 py-2 text-xs font-medium text-gray-400 bg-gray-50 whitespace-nowrap">
                                Alokasi (Rp)
                            </th>
                            <th class="text-right px-3 py-2 text-xs font-medium text-gray-400 bg-gray-50 whitespace-nowrap">
                                Realisasi (KPM)
                            </th>
                            <th class="text-right px-3 py-2 text-xs font-medium text-gray-400 bg-gray-50 whitespace-nowrap">
                                Realisasi (Rp)
                            </th>
                            <th class="text-right px-3 py-2 text-xs font-medium text-gray-400 bg-gray-50 whitespace-nowrap">
                                % 
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse($data as $row)
                        @php
                            $isP88 = $row['wilayah']?->punyaFlag('prioritas_88', now()->year);
                        @endphp
                        <tr class="border-b border-gray-50 hover:bg-gray-50 group">

                            {{-- Kabupaten/Kota --}}
                            <td class="px-4 py-3 sticky left-0 z-[5] bg-white group-hover:bg-gray-50 border-r border-gray-200">
                                <div class="flex items-center gap-2">
                                    @if($isP88)
                                        <span class="inline-block w-2 h-2 rounded-full bg-orange-400 flex-shrink-0"
                                              title="88 Kab/Kota Prioritas"></span>
                                    @endif
                                    <div>
                                        <p class="text-xs font-medium" style="color:#1e3a5f">
                                            {{ $row['wilayah']?->nama }}
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            {{ $row['wilayah']?->parent?->nama }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Jenis --}}
                            <td class="px-4 py-3">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-md"
                                      style="background:#eff6ff; color:#1e3a5f">
                                    {{ $row['jenisBansos']?->kode }}
                                </span>
                            </td>

                            {{-- Data per periode --}}
                            @foreach($periodeAda as $periode)
                                @php $p = $row['periodes'][$periode->id] ?? null; @endphp

                                @if($p)
                                    @php
                                        $pct      = $p['pct_kpm'];
                                        $pctColor = $pct >= 90 ? '#16a34a' : ($pct >= 70 ? '#ca8a04' : '#8b1a2f');
                                        $barColor = $pctColor;
                                    @endphp
                                    <td class="px-3 py-3 text-right text-xs tabular-nums font-medium border-l border-gray-100"
                                        style="color:#374151">
                                        {{ number_format($p['target_kpm'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 text-right text-xs tabular-nums text-gray-500">
                                        {{ number_format($p['target_nominal'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 text-right text-xs tabular-nums font-medium"
                                        style="color:#1e3a5f">
                                        {{ number_format($p['realisasi_kpm'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3 text-right text-xs tabular-nums text-gray-500">
                                        {{ number_format($p['realisasi_nominal'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex items-center gap-1.5">
                                            <div class="flex-1 h-1.5 rounded-full overflow-hidden" style="background:#f1f5f9; min-width:32px">
                                                <div class="h-full rounded-full"
                                                     style="width:{{ min($pct, 100) }}%; background:{{ $barColor }}">
                                                </div>
                                            </div>
                                            <span class="text-xs font-semibold tabular-nums min-w-[38px] text-right"
                                                  style="color:{{ $pctColor }}">
                                                {{ number_format($pct, 1) }}%
                                            </span>
                                        </div>
                                    </td>
                                @else
                                    {{-- Tidak ada data untuk periode ini --}}
                                    <td colspan="5" class="px-3 py-3 text-center text-xs text-gray-300 border-l border-gray-100">
                                        —
                                    </td>
                                @endif
                            @endforeach

                            {{-- Tren --}}
                            @if(count($periodeAda) > 1)
                                <td class="px-4 py-3 text-center border-l border-gray-100">
                                    @if($row['tren'] === 1)
                                        <span class="text-green-600 font-bold text-sm" title="Meningkat">↑</span>
                                    @elseif($row['tren'] === -1)
                                        <span class="font-bold text-sm" style="color:#8b1a2f" title="Menurun">↓</span>
                                    @elseif($row['tren'] === 0)
                                        <span class="text-gray-400 text-sm" title="Stagnan">→</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                            @endif

                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 2 + count($periodeAda) * 5 + (count($periodeAda) > 1 ? 1 : 0) }}"
                                class="px-4 py-12 text-center text-sm text-gray-400">
                                Tidak ada data untuk filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($data->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $data->onEachSide(1)->links('vendor.pagination.custom') }}
        </div>
    @endif
</div>