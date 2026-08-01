<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CheckSmokeDevices::class,
        \App\Console\Commands\MonitorServices::class, // Pastikan command ini ada
    ];

    protected function schedule(Schedule $schedule): void
    {
        // ============================================================
        // 🔥 SEMUA SCHEDULING SUDAH DIPINDAHKAN KE routes/console.php
        // ============================================================
        // KOSONGKAN ATAU KOMENTAR SEMUA SCHEDULING DI SINI
        // 
        // Contoh:
        // $schedule->command('monitor:services')->everyMinute();  // ← HAPUS!
        // $schedule->command(CheckSmokeDevices::class)->everyMinute(); // ← HAPUS!
        
        // ============================================================
        // ✅ TIDAK ADA SCHEDULING DI SINI - SEMUA DI console.php
        // ============================================================
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}