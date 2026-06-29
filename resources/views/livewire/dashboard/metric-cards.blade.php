<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4" wire:loading.class="opacity-50">

    @forelse($metrics as $m)
        @php
            $icons = [
                'PKH'              => ['path' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M16 3.128a4 4 0 0 1 0 7.744 M22 21v-2a4 4 0 0 0-3-3.87 M 9,7 m -4,0 a 4,4 0 1,0 8,0 a 4,4 0 1,0 -8,0'],
                'SEMBAKO'          => ['path' => 'M16 10a4 4 0 0 1-8 0 M3.103 6.034h17.794 M3.4 5.467a2 2 0 0 0-.4 1.2V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6.667a2 2 0 0 0-.4-1.2l-2-2.667A2 2 0 0 0 17 2H7a2 2 0 0 0-1.6.8z'],
                'STIMULUS_SEMBAKO' => ['path' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                'BLT_KESRA'        => ['path' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z'],
            ];
            $icon = $icons[$m['kode']] ?? $icons['PKH'];

            $statusColor = match($m['status'] ?? 'default') {
                'tercapai'  => ['bar' => '#16a34a', 'text' => '#15803d', 'bg' => '#f0fdf4'],
                'perhatian' => ['bar' => '#ca8a04', 'text' => '#a16207', 'bg' => '#fefce8'],
                default     => ['bar' => '#8b1a2f', 'text' => '#8b1a2f', 'bg' => '#fff1f2'],
            };

            $statusLabel = match($m['status'] ?? 'default') {
                'tercapai'  => 'Tercapai',
                'perhatian' => 'Perlu Perhatian',
                default     => 'Kritis',
            };
        @endphp

        <div wire:key="metric-{{ $m['kode'] }}" class="col-span-1 md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-gray-50/50 border border-gray-100">
            
            <div class="sm:col-span-2 flex items-center justify-between border-b border-gray-200/60 pb-2 mb-0.5">
                <div>
                    <span class="text-sm font-semibold tracking-wide text-blue-900">
                        {{ $m['nama'] ?? $m['kode'] }}
                    </span>
                    {{-- Tambah label periode --}}
                    <span class="ml-2 text-xs font-medium px-2 py-0.5 rounded-full"
                        style="background:#eff6ff; color:#1e3a5f">
                        {{ $m['periode_label'] }}
                    </span>
                </div>
            </div>

            {{-- Target KPM --}}
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider mb-0.5" style="color:#6b7280">
                            Target KPM
                        </p>
                    </div>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#eff6ff">
                        <svg class="w-4 h-4" fill="none" stroke="#1e3a5f" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon['path'] }}"/>
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold tabular-nums" style="color:#1e3a5f">
                    {{ number_format($m['target_kpm'] ?? 0, 0, ',', '.') }}
                </p>
                <p class="text-xs mt-0.5 text-gray-400">KPM</p>

                {{-- Nominal Target --}}
                @if(!empty($m['target_nominal']) && $m['target_nominal'] > 0)
                    <p class="text-xs mt-2 pt-2 border-t border-gray-50 font-medium" style="color:#6b7280">
                        Rp {{ number_format($m['target_nominal'], 0, ',', '.') }}
                    </p>
                @endif
            </div>

            {{-- Realisasi KPM --}}
            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider mb-0.5" style="color:#6b7280">
                            Realisasi KPM
                        </p>
                    </div>
                </div>

                <p class="text-2xl font-bold tabular-nums" style="color:#1e3a5f">
                    {{ number_format($m['realisasi_kpm'] ?? 0, 0, ',', '.') }}
                </p>

                {{-- Progress bar + pct --}}
                <div class="flex items-center gap-2 mt-2">
                    <div class="flex-1 h-2 rounded-full overflow-hidden" style="background:#f1f5f9">
                        <div class="h-full rounded-full transition-all duration-500"
                             style="width:{{ min($m['pct_kpm'] ?? 0, 100) }}%; background:{{ $statusColor['bar'] }}">
                        </div>
                    </div>
                    <span class="text-xs font-bold tabular-nums min-w-[38px] text-right" style="color:{{ $statusColor['text'] }}">
                        {{ $m['pct_kpm'] ?? 0 }}%
                    </span>
                </div>

                {{-- Nominal Realisasi --}}
                @if(!empty($m['realisasi_nominal']) && $m['realisasi_nominal'] > 0)
                    <p class="text-xs mt-2 pt-2 border-t border-gray-50 font-medium" style="color:#6b7280">
                        Rp {{ number_format($m['realisasi_nominal'], 0, ',', '.') }}
                    </p>
                @endif
            </div>

        </div>

    @empty
        <div class="col-span-1 md:col-span-2 text-center py-12 text-sm" style="color:#9ca3af">
            Tidak ada data untuk filter yang dipilih.
        </div>
    @endforelse

</div>