<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\TargetBansos;
use App\Models\RealisasiBansos;
use App\Observers\TargetBansosObserver;
use App\Observers\RealisasiBansosObserver;
use App\Services\SummaryService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Daftarkan SummaryService sebagai singleton —
        // satu instance dipakai ulang sepanjang request lifecycle.
        // Ini mencegah instansiasi berulang saat Observer dipanggil
        // berkali-kali dalam satu proses import massal.
        $this->app->singleton(SummaryService::class);
    }

    public function boot(): void
    {
        // Daftarkan observer ke masing-masing model.
        // Laravel akan otomatis inject SummaryService via constructor
        // karena sudah terdaftar sebagai singleton di atas.
        TargetBansos::observe(TargetBansosObserver::class);
        RealisasiBansos::observe(RealisasiBansosObserver::class);
    }
}