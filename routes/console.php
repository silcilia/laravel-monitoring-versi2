<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Console\Commands\CheckSmokeDevices;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// ============================================================
// CEK KONEKSI INTERNET
// ============================================================

function isInternetConnected(): bool
{
    $targets = [
        'https://www.google.com',
        'https://www.cloudflare.com',
    ];

    foreach ($targets as $target) {
        try {
            $response = Http::timeout(3)
                ->connectTimeout(2)
                ->get($target);

            // Jika berhasil mendapat respons, internet dianggap aktif
            if ($response->successful()) {

                Log::info(
                    "🌐 [INTERNET] ONLINE - {$target} dapat diakses"
                );

                return true;
            }

        } catch (\Throwable $e) {

            Log::warning(
                "⚠️ [INTERNET] Gagal mengakses {$target}"
            );
        }
    }

    Log::warning(
        '🔴 [INTERNET] OFFLINE - Semua target tidak dapat diakses'
    );

    return false;
}


// ============================================================
// 1. SMOKE / ESP MONITOR
// ============================================================

Schedule::command(CheckSmokeDevices::class)
    ->everyMinute()
    ->name('check-smoke-devices');


// ============================================================
// 2. SERVICE MONITOR
// ============================================================

Schedule::command('monitor:services')
    ->everyMinute()
    ->name('monitor-services')
    ->skip(function () {

        $internetConnected = isInternetConnected();

        if (!$internetConnected) {

            Log::warning(
                '⏸️ [SCHEDULE] INTERNET OFFLINE - MONITORING SERVICE DILEWATI'
            );

            return true;
        }

        Log::info(
            '🚀 [SCHEDULE] INTERNET ONLINE - MONITORING SERVICE DIJALANKAN'
        );

        return false;
    });