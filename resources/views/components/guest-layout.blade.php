<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('storage/logo-kemenkopm.png') }}" type="image/png">
    <title>@yield('title', 'Login Dashboard Monitoring') Penyaluran Bantuan Sosial</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">

        <div class="text-center mb-6">
            <h1 class="text-base font-semibold text-gray-800">Monitoring Penyaluran Bansos</h1>
            <p class="text-xs text-gray-400 mt-1">Masuk untuk mengelola data</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            {{ $slot }}
        </div>

    </div>
</div>

</body>
</html>