<div class="bg-white border border-gray-200 rounded-xl p-4 md:p-5 shadow-sm">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:flex-wrap lg:items-end gap-4">

        {{-- Tahun --}}
        <div class="w-full lg:w-32">
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Tahun</label>
            
            <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full">
                <button @click="open = !open" type="button" 
                        class="inline-flex items-center justify-between w-full text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg pl-3 pr-4 py-2 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                    <span>{{ $tahun }}</span>
                    <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" 
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute left-0 z-50 w-full lg:w-32 mt-2 max-h-60 overflow-y-auto origin-top-left bg-white rounded-xl shadow-xl border border-gray-100 focus:outline-none py-1"
                    style="display: none;">
                    
                    @foreach($tahunList as $t)
                        <button type="button" @click="$wire.set('tahun', {{ $t }}); open = false" 
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between {{ $tahun == $t ? 'bg-blue-50/60 font-semibold text-blue-900' : '' }}">
                            <span>{{ $t }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Triwulan --}}
        <div class="w-full lg:w-64">
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Periode</label>
            <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full">
                <button @click="open = !open" type="button"
                        class="inline-flex items-center justify-between w-full text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg pl-3 pr-4 py-2 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-900">
                    <span>
                        @if(empty($triwulanDipilih))
                            Semua triwulan
                        @elseif(count($triwulanDipilih) === 1)
                            @php $tw = $triwulanDipilih[0]; @endphp
                            TW {{ ['I','II','III','IV'][$tw-1] }}
                        @else
                            {{ count($triwulanDipilih) }} triwulan dipilih
                        @endif
                    </span>
                    <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
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
                    class="absolute left-0 z-50 w-full mt-2 origin-top-left bg-white rounded-xl shadow-xl border border-gray-100 py-1"
                    style="display:none">

                    {{-- Semua triwulan --}}
                    <button type="button"
                            wire:click="$set('triwulanDipilih', [])"
                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 flex items-center gap-2
                                {{ empty($triwulanDipilih) ? 'font-semibold text-blue-900 bg-blue-50/60' : 'text-gray-700' }}">
                        <span class="w-4 h-4 rounded border flex items-center justify-center flex-shrink-0
                                    {{ empty($triwulanDipilih) ? 'bg-blue-900 border-blue-900' : 'border-gray-300' }}">
                            @if(empty($triwulanDipilih))
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                        </span>
                        Semua triwulan
                    </button>

                    <div class="border-t border-gray-100 my-1"></div>

                    @foreach([1 => 'TW I (Jan-Mar)', 2 => 'TW II (Apr-Jun)', 3 => 'TW III (Jul-Sep)', 4 => 'TW IV (Okt-Des)'] as $tw => $label)
                        @php $dipilih = in_array($tw, $triwulanDipilih); @endphp
                        <button type="button"
                                wire:click="
                                    @if($dipilih)
                                        $set('triwulanDipilih', {{ json_encode(array_values(array_diff($triwulanDipilih, [$tw]))) }})
                                    @else
                                        $set('triwulanDipilih', {{ json_encode(array_values(array_merge($triwulanDipilih, [$tw]))) }})
                                    @endif
                                "
                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 flex items-center gap-2
                                    {{ $dipilih ? 'font-semibold text-blue-900 bg-blue-50/60' : 'text-gray-700' }}">
                            <span class="w-4 h-4 rounded border flex items-center justify-center flex-shrink-0
                                        {{ $dipilih ? 'bg-blue-900 border-blue-900' : 'border-gray-300' }}">
                                @if($dipilih)
                                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                            </span>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="hidden lg:block w-px h-9 bg-gray-200 self-end mb-1"></div>

        {{-- Provinsi --}}
        <div class="w-full lg:flex-1 lg:min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Provinsi</label>
            
            <div x-data="{ 
                    open: false, 
                    search: '',
                    provinsiId: @entangle('provinsiId'),
                    provinsis: {{ json_encode($provinsiList) }}
                }" 
                @click.outside="open = false; search = ''" 
                class="relative w-full">
                
                <button @click="open = !open" type="button" 
                        class="inline-flex items-center justify-between w-full text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg pl-3 pr-4 py-2 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                    <span class="truncate">
                        @if($provinsiId)
                            {{ collect($provinsiList)->firstWhere('id', $provinsiId)['nama'] ?? 'Nasional' }}
                        @else
                            Nasional
                        @endif
                    </span>
                    <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" 
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute left-0 z-50 w-full lg:min-w-[240px] mt-2 origin-top-left bg-white rounded-xl shadow-xl border border-gray-100 focus:outline-none overflow-hidden"
                    style="display: none;">
                    
                    <div class="p-2 border-b border-gray-100 bg-gray-50/50">
                        <input type="text" 
                            x-model="search" 
                            placeholder="Cari provinsi..." 
                            class="w-full px-3 py-1.5 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-blue-900 bg-white"
                            @click.stop>
                    </div>

                    <div class="max-h-64 overflow-y-auto py-1">
                        <button type="button" 
                                x-show="search === ''"
                                @click="$wire.set('provinsiId', ''); open = false; search = ''" 
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between {{ $provinsiId == '' ? 'bg-blue-50/60 font-semibold text-blue-900' : '' }}">
                            <span>Nasional</span>
                        </button>

                        <template x-for="p in provinsis" :key="p.id">
                            <button type="button" 
                                    x-show="p.nama.toLowerCase().includes(search.toLowerCase())"
                                    @click="$wire.set('provinsiId', p.id); open = false; search = ''" 
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between"
                                    :class="p.id == provinsiId ? 'bg-blue-50/60 font-semibold text-blue-900' : ''">
                                <span x-text="p.nama"></span>
                            </button>
                        </template>

                        <div x-show="provinsis.filter(p => p.nama.toLowerCase().includes(search.toLowerCase())).length === 0" 
                            class="px-4 py-3 text-xs text-center text-gray-400">
                            Tidak ditemukan nama provinsi yang cocok
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kabupaten (kondisional) --}}
        @if(!empty($kabupatenList))
            <div class="w-full lg:flex-1 lg:min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Kabupaten/Kota</label>
                
                <div x-data="{ 
                        open: false, 
                        search: '',
                        kabupatenId: @entangle('kabupatenId'),
                        kabupatens: {{ json_encode($kabupatenList) }}
                    }" 
                    @click.outside="open = false; search = ''" 
                    class="relative w-full">
                    
                    <button @click="open = !open" type="button" 
                            class="inline-flex items-center justify-between w-full text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg pl-3 pr-4 py-2 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                        <span class="truncate">
                            @if($kabupatenId)
                                {{ collect($kabupatenList)->firstWhere('id', $kabupatenId)['nama'] ?? 'Semua kabupaten' }}
                            @else
                                Semua kabupaten
                            @endif
                        </span>
                        <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute left-0 z-50 w-full lg:min-w-[240px] mt-2 origin-top-left bg-white rounded-xl shadow-xl border border-gray-100 focus:outline-none overflow-hidden"
                        style="display: none;">
                        
                        <div class="p-2 border-b border-gray-100 bg-gray-50/50">
                            <input type="text" 
                                x-model="search" 
                                placeholder="Cari kabupaten/kota..." 
                                class="w-full px-3 py-1.5 text-xs border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-900 focus:border-blue-900 bg-white"
                                @click.stop>
                        </div>

                        <div class="max-h-60 overflow-y-auto py-1">
                            <button type="button" 
                                    x-show="search === ''"
                                    @click="$wire.set('kabupatenId', ''); open = false; search = ''" 
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between {{ $kabupatenId == '' ? 'bg-blue-50/60 font-semibold text-blue-900' : '' }}">
                                <span>Semua kabupaten</span>
                            </button>

                            <template x-for="k in kabupatens" :key="k.id">
                                <button type="button" 
                                        x-show="k.nama.toLowerCase().includes(search.toLowerCase())"
                                        @click="$wire.set('kabupatenId', k.id); open = false; search = ''" 
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between"
                                        :class="k.id == kabupatenId ? 'bg-blue-50/60 font-semibold text-blue-900' : ''">
                                    <span x-text="k.nama"></span>
                                </button>
                            </template>

                            <div x-show="kabupatens.filter(k => k.nama.toLowerCase().includes(search.toLowerCase())).length === 0" 
                                class="px-4 py-3 text-xs text-center text-gray-400">
                                Tidak ditemukan nama yang cocok
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="hidden lg:block w-px h-9 bg-gray-200 self-end mb-1"></div>

        {{-- Jenis Bansos --}}
        <div class="w-full lg:w-40">
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Jenis Bansos</label>
            
            <div x-data="{ open: false }" @click.outside="open = false" class="relative w-full">
                <button @click="open = !open" type="button" 
                        class="inline-flex items-center justify-between w-full text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg pl-3 pr-4 py-2 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:border-blue-900">
                    <span>
                        @if($jenisBansosId)
                            {{ collect($jenisBansosList)->firstWhere('id', $jenisBansosId)['kode'] ?? 'Pilih Bansos' }}
                        @else
                            Pilih Bansos
                        @endif
                    </span>
                    <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" 
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute left-0 z-50 w-full lg:w-40 mt-2 max-h-60 overflow-y-auto origin-top-left bg-white rounded-xl shadow-xl border border-gray-100 focus:outline-none py-1"
                    style="display: none;">
                    
                    @foreach($jenisBansosList as $j)
                        <button type="button" @click="$wire.set('jenisBansosId', '{{ $j['id'] }}'); open = false" 
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between {{ $jenisBansosId == $j['id'] ? 'bg-blue-50/60 font-semibold text-blue-900' : '' }}">
                            <span>{{ $j['kode'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Reset Button --}}
        <div class="w-full sm:col-span-2 lg:w-auto lg:ml-auto">
            <button type="button" 
                    wire:click="resetFilter"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center gap-1.5 w-full lg:w-auto text-xs font-semibold uppercase tracking-wider text-gray-400 bg-white border border-gray-300 rounded-lg px-4 py-2.5 shadow-sm transition-all duration-200 ease-in-out hover:text-red-600 hover:border-red-200 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer">
                
                <svg wire:loading wire:target="resetFilter" class="animate-spin w-3.5 h-3.5 text-red-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

                <span wire:loading.remove wire:target="resetFilter">Reset</span>
                <span wire:loading wire:target="resetFilter" class="text-red-600">Memuat...</span>
            </button>
        </div>

    </div>
</div>