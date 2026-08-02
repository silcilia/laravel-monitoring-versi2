<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use App\Services\ServiceMonitorService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CheckServices extends Command
{
    protected $signature = 'monitor:services';
    protected $description = 'Monitoring all services';

    // 🔥 KONSTANTA UNTUK CEK INTERNET
    private const INTERNET_CHECK_TIMEOUT = 3; // 3 detik

    public function handle(ServiceMonitorService $monitor)
    {
        // ============================================================
        // 🔥🔥🔥 CEK INTERNET TERLEBIH DAHULU
        // ============================================================
        if (!$this->isInternetConnected()) {
            Log::warning('🌐 Internet TIDAK Terhubung - SKIP monitoring semua service');
            $this->warn('❌ Internet tidak terhubung, monitoring dilewati.');
            
            // 🔥 TIDAK ADA PERUBAHAN STATUS!
            // 🔥 TIDAK ADA WA TERKIRIM!
            return Command::SUCCESS; // SKIP semua monitoring
        }

        Log::info('🌐 Internet terhubung, mulai monitoring service...');
        $this->info('🔍 Memulai monitoring services...');

        // ============================================================
        // 🔥 AMBIL SERVICE YANG TIDAK DIARSIP
        // ============================================================
        $services = Service::where('is_archived', 0)->get();

        if ($services->isEmpty()) {
            $this->warn('⚠️ Tidak ada service aktif yang terdaftar');
            Log::info('⚠️ Tidak ada service aktif yang terdaftar');
            return Command::SUCCESS;
        }

        $this->info('📡 Total service: ' . $services->count());
        Log::info('📡 Memulai monitoring ' . $services->count() . ' service');

        $successCount = 0;
        $failCount = 0;

        foreach ($services as $service) {
            try {
                $monitor->check($service);
                $successCount++;
                $this->line("✅ {$service->name} checked");
            } catch (\Exception $e) {
                $failCount++;
                $this->error("❌ {$service->name} - error: " . $e->getMessage());
                Log::error("❌ Gagal monitor {$service->name}: " . $e->getMessage());
            }
        }

        $this->info("✅ Monitoring services selesai: {$successCount} berhasil, {$failCount} gagal");
        Log::info("✅ Monitoring services selesai: {$successCount} berhasil, {$failCount} gagal");
        
        return Command::SUCCESS;
    }

    /**
     * 🔥🔥🔥 CEK KONEKSI INTERNET
     */
    private function isInternetConnected(): bool
    {
        // 🔥 CEK CACHE DULU (agar tidak terlalu sering cek)
        $cacheKey = 'internet_connection_status';
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        Log::info('🔍 Mengecek koneksi internet...');

        // 🔥 METHOD 1: HTTP (paling cepat)
        $httpTargets = [
            'https://www.google.com',
            'https://www.cloudflare.com',
            'https://www.google.co.id',
        ];

        foreach ($httpTargets as $target) {
            try {
                $response = Http::timeout(self::INTERNET_CHECK_TIMEOUT)
                    ->connectTimeout(self::INTERNET_CHECK_TIMEOUT)
                    ->get($target);

                if ($response->successful()) {
                    Log::info("✅ Internet ONLINE (HTTP: {$target})");
                    Cache::put($cacheKey, true, 60); // Cache 60 detik
                    return true;
                }
            } catch (\Exception $e) {
                // Gagal, coba target lain
            }
        }

        // 🔥 METHOD 2: PING (fallback)
        $pingTargets = ['8.8.8.8', '1.1.1.1', '8.8.4.4'];
        foreach ($pingTargets as $target) {
            if ($this->pingHost($target)) {
                Log::info("✅ Internet ONLINE (PING: {$target})");
                Cache::put($cacheKey, true, 60);
                return true;
            }
        }

        // 🔥 METHOD 3: DNS (fallback terakhir)
        $dnsTargets = ['google.com', 'cloudflare.com'];
        foreach ($dnsTargets as $target) {
            if (checkdnsrr($target, 'A')) {
                Log::info("✅ Internet ONLINE (DNS: {$target})");
                Cache::put($cacheKey, true, 60);
                return true;
            }
        }

        Log::warning('❌ Internet OFFLINE - Semua metode gagal');
        Cache::put($cacheKey, false, 30); // Cache 30 detik
        return false;
    }

    /**
     * 🔥 PING HOST (untuk fallback)
     */
    private function pingHost($host): bool
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $timeout = self::INTERNET_CHECK_TIMEOUT;

        $command = $isWindows
            ? "ping -n 1 -w " . ($timeout * 1000) . " " . escapeshellarg($host) . " 2>&1"
            : "ping -c 1 -W " . $timeout . " " . escapeshellarg($host) . " 2>&1";

        exec($command, $output, $resultCode);
        return $resultCode === 0;
    }
}