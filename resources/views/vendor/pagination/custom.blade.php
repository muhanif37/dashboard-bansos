@if ($paginator->hasPages())
    <nav style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <p style="font-size:12px; color:#9ca3af; margin:0;">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        </p>

        <div style="display:flex; align-items:center; gap:4px;">
            {{-- Tombol Previous --}}
            @if ($paginator->onFirstPage())
                <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; font-size:13px; color:#d1d5db; background:#f8fafc; cursor:not-allowed;">‹</span>
            @else
                <button type="button" wire:click="gotoPage({{ $paginator->currentPage() - 1 }})"
                        style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; font-size:13px; color:#374151; background:#f8fafc; border:none; cursor:pointer;"
                        onmouseover="this.style.background='#f1f5f9'"
                        onmouseout="this.style.background='#f8fafc'">‹</button>
            @endif

            {{-- MODIFIKASI DIMULAI DISINI --}}
            @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
                $onEachSide = 1; // Menampilkan 1 halaman di kiri & kanan halaman aktif
                
                $elementsArray = [];
                for ($i = 1; $i <= $lastPage; $i++) {
                    // Selalu tampilkan halaman pertama, terakhir, dan halaman di sekitar halaman aktif
                    if ($i == 1 || $i == $lastPage || abs($i - $currentPage) <= $onEachSide) {
                        $elementsArray[$i] = $i;
                    } elseif ($i == 2 || $i == $lastPage - 1) {
                        // Tandai untuk memunculkan titik-titik (...)
                        $elementsArray[$i] = '...';
                    }
                }
                
                // Hapus duplikasi titik-titik berturut-turut
                $lastVal = null;
            @endphp

            @foreach ($elementsArray as $page => $display)
                @if ($display === '...')
                    @if ($lastVal !== '...')
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; font-size:13px; color:#9ca3af;">...</span>
                    @endif
                @else
                    @if ($page == $currentPage)
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; font-size:13px; font-weight:600; background:#1e3a5f; color:#ffffff;">{{ $page }}</span>
                    @else
                        <button type="button" wire:click="gotoPage({{ $page }})"
                                style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; font-size:13px; color:#374151; background:#f8fafc; border:none; cursor:pointer;"
                                onmouseover="this.style.background='#f1f5f9'"
                                onmouseout="this.style.background='#f8fafc'">{{ $page }}</button>
                    @endif
                @endif
                @php $lastVal = $display; @endphp
            @endforeach
            {{-- MODIFIKASI SELESAI DISINI --}}

            {{-- Tombol Next --}}
            @if ($paginator->hasMorePages())
                <button type="button" wire:click="gotoPage({{ $paginator->currentPage() + 1 }})"
                        style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; font-size:13px; color:#374151; background:#f8fafc; border:none; cursor:pointer;"
                        onmouseover="this.style.background='#f1f5f9'"
                        onmouseout="this.style.background='#f8fafc'">›</button>
            @else
                <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; font-size:13px; color:#d1d5db; background:#f8fafc; cursor:not-allowed;">›</span>
            @endif
        </div>
    </nav>
@endif
