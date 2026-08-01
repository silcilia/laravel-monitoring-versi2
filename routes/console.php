<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\CheckSmokeDevices;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============================================================
// 🔥 SCHEDULING SEMUA PERINTAH ADA DI SINI
// ============================================================

// ==================== 1. SMOKE/ESP MONITOR ====================
// ✅ PAKAI YANG SUDAH TERBUKTI JALAN
Schedule::command(CheckSmokeDevices::class)
    ->everyMinute();

// ==================== 2. SERVICE MONITOR ====================
// ✅ PAKAI YANG SUDAH TERBUKTI JALAN
// ➡️ TANPA withoutOverlapping() dan runInBackground()
Schedule::command('monitor:services')
    ->everyMinute();

// ============================================================
// 📝 LOG SCHEDULE (untuk debugging)
// ============================================================
Log::info('✅ Schedule loaded from console.php at ' . now()->format('Y-m-d H:i:s'));