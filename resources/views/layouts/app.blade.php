<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('storage/logo-kemenkopm.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard')  Penyaluran Bantuan Sosial</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
    <style>
        :root {
            --navy:       #1e3a5f;
            --navy-soft:  #2a4f80;
            --burgundy:   #8b1a2f;
            --bg:         #f7f7f5;
            --surface:    #ffffff;
            --border:     #e8e8e6;
            --text-main:  #1a1a1a;
            --text-muted: #787774;
            --text-faint: #b0aea9;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--bg);
            color: var(--text-main);
            font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            background: #fbfaf9;
            border-right: 1px solid var(--border);
            width: 240px;
            flex-shrink: 0;
            display: flex;          
            flex-direction: column; 
            height: 100vh;
        }

        .sidebar-logo {
            height: 48px;
            display: flex;
            align-items: center;
            padding: 0 14px;
            border-bottom: 1px solid var(--border);
            gap: 10px;
        }

        .sidebar-logo-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            /* background: var(--navy); */
        }

        .sidebar-logo-text {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
            line-height: 1.1;
        }

        /* Nav section label */
        .nav-section {
            padding: 16px 14px 4px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-faint);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        /* Nav item */
        .nav-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            margin: 1px 6px;
            border-radius: 6px;
            font-size: 13.5px;
            color: var(--text-muted);
            text-decoration: none;
            transition: background 0.1s, color 0.1s;
            cursor: pointer;
        }
        .nav-item:hover {
            background: rgba(0,0,0,0.04);
            color: var(--text-main);
        }
        .nav-item.active {
            background: rgba(30,58,95,0.08);
            color: var(--navy);
            font-weight: 600;
        }
        .nav-item.active svg {
            color: var(--navy);
        }

        /* Sidebar divider */
        .nav-divider {
            height: 1px;
            background: var(--border);
            margin: 8px 14px;
        }

        /* User area */
        .sidebar-user {
            padding: 10px 14px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: var(--navy);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            height: 48px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 10;
            gap: 12px;
        }

        .topbar-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
        }

        /* Export button */
        .btn-export {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-muted);
            background: var(--surface);
            border: 1px solid var(--border);
            text-decoration: none;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .btn-export:hover {
            background: #f0f0ee;
            border-color: #d0d0ce;
            color: var(--text-main);
        }

        /* Hamburger */
        .btn-hamburger {
            display: none;
            padding: 6px;
            border-radius: 6px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: background 0.1s;
        }
        .btn-hamburger:hover { background: rgba(0,0,0,0.05); }

        @media (max-width: 1023px) {
            .btn-hamburger { display: flex; align-items: center; }
        }

        /* ===== MAIN ===== */
        .main-content {
            flex: 1;
            overflow-y: auto;
            background: var(--bg);
        }

        /* ===== FLASH ===== */
        .flash {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            border: 1px solid;
        }
        .flash-sukses  { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
        .flash-warning { background: #fefce8; border-color: #fde047; color: #a16207; }
        .flash-error   { background: #fef2f2; border-color: #fecaca; color: #dc2626; }

        /* ===== MOBILE SIDEBAR ===== */
        .sidebar-transition { transition: transform 0.25s ease; }

        @media (max-width: 1023px) {
            .sidebar {
                position: fixed;
                inset-y: 0;
                left: 0;
                z-index: 40;
                transform: translateX(-100%);
                height: 100vh;        
                overflow-y: auto;     
                display: flex;        
                flex-direction: column; 
            }
            .sidebar.open { transform: translateX(0); }
        }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.3);
            z-index: 30;
        }
        .sidebar-backdrop.open { display: block; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d0d0ce; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #b0b0ae; }
    </style>
</head>
<body>

<div style="display:flex; height:100vh; overflow:hidden">

    {{-- Backdrop Mobile --}}
    <div id="sidebar-backdrop" class="sidebar-backdrop" onclick="toggleSidebar()"></div>

    {{-- ===== SIDEBAR ===== --}}
    <aside id="sidebar" class="sidebar sidebar-transition">

        {{-- Logo --}}
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <img src="{{ asset('storage/logo-kemenkopm.png') }}"
                     alt="Logo"
                     style="width:100%; height:100px; object-fit:contain;"
                     onerror="this.style.display='none'">
            </div>
            <span class="sidebar-logo-text">Pemberdayaan Masyarakat</span>

            {{-- Close button mobile --}}
            <button onclick="toggleSidebar()"
                    class="btn-hamburger lg:hidden"
                    style="margin-left:auto"
                    id="sidebar-close">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav --}}
        <nav style="flex:1; overflow-y:auto; padding:8px 0">
            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg style="width:15px;height:15px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            @auth
                @if(auth()->user()->isAdmin())
                    <div class="nav-divider"></div>
                    <div class="nav-section">Admin</div>

                    @php
                        $navItems = [
                            ['route' => 'admin.import.*',    'href' => route('admin.import.index'),    'label' => 'Import Data',
                             'icon'  => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
                            ['route' => 'admin.target.*',    'href' => route('admin.target.index'),    'label' => 'Data Target',
                             'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                            ['route' => 'admin.realisasi.*', 'href' => route('admin.realisasi.index'), 'label' => 'Data Realisasi',
                             'icon'  => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        ];
                    @endphp

                    @foreach($navItems as $item)
                        <a href="{{ $item['href'] }}"
                           class="nav-item {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                            <svg style="width:15px;height:15px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                            </svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                @endif
            @endauth
        </nav>

        {{-- User --}}
        <div class="sidebar-user">
            @auth
                <div style="display:flex; align-items:center; gap:8px; min-width:0">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div style="min-width:0">
                        <p style="font-size:12.5px; font-weight:600; color:var(--text-main); white-space:nowrap; overflow:hidden; text-overflow:ellipsis">
                            {{ auth()->user()->name }}
                        </p>
                        <p style="font-size:11px; color:var(--text-faint); text-transform:capitalize">
                            {{ auth()->user()->role }}
                        </p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            style="padding:5px; border-radius:5px; border:none; background:transparent; color:var(--text-faint); cursor:pointer; transition:all 0.15s"
                            title="Keluar"
                            onmouseover="this.style.background='rgba(0,0,0,0.06)'; this.style.color='var(--burgundy)'"
                            onmouseout="this.style.background='transparent'; this.style.color='var(--text-faint)'">
                        <svg style="width:15px;height:15px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-[13px] text-gray-600 font-medium hover:text-gray-900 no-underline">
                    Login Admin 
                    <svg class="w-4 h-4" data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25"></path>
                    </svg>
                </a>
            @endauth
        </div>
    </aside>

    {{-- ===== MAIN ===== --}}
    <main class="main-content">

        {{-- Topbar --}}
        <div class="topbar">
            <button id="hamburger-btn"
                    class="btn-hamburger"
                    onclick="toggleSidebar()">
                <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <span class="topbar-title">@yield('page-title', 'Dashboard')</span>

            <div style="margin-left:auto; display:flex; align-items:center; gap:8px">
                @yield('topbar-actions')
            </div>
        </div>

        {{-- Flash --}}
        <div style="padding:16px 20px 0">
            @if(session('sukses'))
                <div class="flash flash-sukses" style="margin-bottom:12px">{{ session('sukses') }}</div>
            @endif
            @if(session('warning'))
                <div class="flash flash-warning" style="margin-bottom:12px">{{ session('warning') }}</div>
            @endif
            @if(session('error'))
                <div class="flash flash-error" style="margin-bottom:12px">{{ session('error') }}</div>
            @endif
        </div>

        {{-- Content --}}
        <div style="padding:16px 20px 32px">
            @yield('content')
        </div>
    </main>
</div>

<script>
    function toggleSidebar() {
        const sidebar  = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const isOpen   = sidebar.classList.contains('open');

        if (isOpen) {
            sidebar.classList.remove('open');
            backdrop.classList.remove('open');
        } else {
            sidebar.classList.add('open');
            backdrop.classList.add('open');
        }
    }

    // Tutup sidebar saat resize ke desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebar-backdrop').classList.remove('open');
        }
    });
</script>

@livewireScripts
</body>
</html>
