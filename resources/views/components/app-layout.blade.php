{{--
    Component ini dipakai oleh view bawaan Breeze (misal: profile/edit.blade.php)
    yang menggunakan <x-app-layout>.
    Kita redirect ke layout app utama kita agar konsisten.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="flex h-screen overflow-hidden">

    {{-- Sidebar — sama dengan layouts/app.blade.php --}}
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col flex-shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-gray-200">
            <span class="text-sm font-semibold text-gray-800 leading-tight">
                Monitoring<br>Penyaluran Bansos
            </span>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm text-gray-600 hover:bg-gray-50">
                Dashboard
            </a>
        </nav>
        <div class="border-t border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600 truncate">{{ auth()->user()?->name }}</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs text-gray-400 hover:text-red-500">Keluar</button>
                </form>
            </div>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto">
        @isset($header)
            <div class="bg-white border-b border-gray-200 h-16 flex items-center px-6">
                {{ $header }}
            </div>
        @endisset
        <div class="p-6">
            {{ $slot }}
        </div>
    </main>

</div>

@livewireScripts
</body>
</html>