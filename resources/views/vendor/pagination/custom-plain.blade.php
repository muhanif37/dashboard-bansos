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
                <a href="{{ $paginator->previousPageUrl() }}"
                   style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; font-size:13px; color:#374151; background:#f8fafc; text-decoration:none;"
                   onmouseover="this.style.background='#f1f5f9'"
                   onmouseout="this.style.background='#f8fafc'">‹</a>
            @endif

            @php
                $currentPage = $paginator->currentPage();
                $lastPage    = $paginator->lastPage();
                $elementsArray = [];
                for ($i = 1; $i <= $lastPage; $i++) {
                    if ($i == 1 || $i == $lastPage || abs($i - $currentPage) <= 1) {
                        $elementsArray[$i] = $i;
                    } elseif ($i == 2 || $i == $lastPage - 1) {
                        $elementsArray[$i] = '...';
                    }
                }
                $lastVal = null;
            @endphp

            @foreach ($elementsArray as $page => $display)
                @if ($display === '...')
                    @if ($lastVal !== '...')
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; font-size:13px; color:#9ca3af;">...</span>
                    @endif
                @else
                    @if ($page == $currentPage)
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; font-size:13px; font-weight:600; background:#1e3a5f; color:#fff;">{{ $page }}</span>
                    @else
                        <a href="{{ $paginator->url($page) }}"
                           style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; font-size:13px; color:#374151; background:#f8fafc; text-decoration:none;"
                           onmouseover="this.style.background='#f1f5f9'"
                           onmouseout="this.style.background='#f8fafc'">{{ $page }}</a>
                    @endif
                @endif
                @php $lastVal = $display; @endphp
            @endforeach

            {{-- Tombol Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; font-size:13px; color:#374151; background:#f8fafc; text-decoration:none;"
                   onmouseover="this.style.background='#f1f5f9'"
                   onmouseout="this.style.background='#f8fafc'">›</a>
            @else
                <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; font-size:13px; color:#d1d5db; background:#f8fafc; cursor:not-allowed;">›</span>
            @endif
        </div>
    </nav>
@endif