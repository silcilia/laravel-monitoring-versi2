<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Console\Commands\CheckSmokeDevices;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============================================================
// 🔥 CEK INTERNET - DENGAN CACHE (30 DETIK)
// ============================================================
function isInternetConnected(): bool
{
    // 🔥 CEK CACHE DULU
    $cacheKey = 'internet_check_status';
    $cached = Cache::get($cacheKey);
    
    if ($cached !== null) {
        return $cached;
    }
    
    // 🔥 CEK INTERNET
    try {
        $targets = [
            'https://www.google.com',
            'https://www.cloudflare.com',
            'https://www.microsoft.com',
            'https://www.amazon.com'
        ];
        
        $context = stream_context_create([
            'http' => ['timeout' => 3],
            'ssl' => ['verify_peer' => false]
        ]);
        
        $isConnected = false;
        foreach ($targets as $target) {
            $response = @file_get_contents($target, false, $context);
            if ($response !== false && strlen($response) > 100) {
                $isConnected = true;
                break;
            }
        }
        
        // 🔥 SIMPAN KE CACHE (30 DETIK)
        Cache::put($cacheKey, $isConnected, 30);
        
        Log::info('🌐 [Internet] ' . ($isConnected ? 'ONLINE ✅' : 'OFFLINE ❌'));
        return $isConnected;
        
    } catch (\Exception $e) {
        Cache::put($cacheKey, false, 30);
        Log::info('⏸️ [Internet] OFFLINE - ' . $e->getMessage());
        return false;
    }
}

// ============================================================
// 🔥 SCHEDULING SEMUA PERINTAH ADA DI SINI
// ============================================================

// ==================== 1. SMOKE/ESP MONITOR ====================
Schedule::command(CheckSmokeDevices::class)
    ->everyMinute()
    ->withoutOverlapping()
    ->name('check-smoke-devices')
    ->runInBackground();

// ==================== 2. SERVICE MONITOR ====================
Schedule::command('monitor:services')
    ->everyMinute()
    ->withoutOverlapping()
    ->name('monitor-services')
    ->runInBackground()
    ->skip(function () {
        $isConnected = isInternetConnected();
        
        if (!$isConnected) {
            Log::info('⏸️ [SCHEDULE] Internet DOWN - monitor:services SKIPPED');
        } else {
            Log::info('🌐 [SCHEDULE] Internet OK - monitor:services RUNNING');
        }
        return !$isConnected;
    });

Log::info('✅ Schedule loaded from console.php at ' . now()->format('Y-m-d H:i:s'));