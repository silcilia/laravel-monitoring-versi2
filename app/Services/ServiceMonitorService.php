<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ServiceLog;
use App\Models\Contact;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ServiceMonitorService
{
    private $networkAlertSent = false;

    // 🔥 KONSTANTA TIMEOUT
    private const TIMEOUT_FAST = 5;      // 5 detik - langsung DOWN
    private const TIMEOUT_SLOW = 10;     // 10 detik - pakai consecutive

    // 🔥 KONSTANTA SSL
    private const SSL_WARNING_DAYS = 30;     // Peringatan ssl 30 hari
    private const SSL_CRITICAL_DAYS = 7;     // peringatan ssl 7 hari

    /**
     * ============================================================
     * 🔍 CHECK SINGLE SERVICE
     * ============================================================
     */
    public function check(Service $service)
    {
        if ($service->type === 'ping') {
            return $this->checkPing($service);
        }
        return $this->checkHttp($service);
    }

    // ============================================================
    // 📊 PAGESPEED STATUS DETERMINATION
    // ============================================================

    /**
     * Tentukan status berdasarkan PageSpeed thresholds
     * 
     * @param string $metric Nama metrik (response_time, lcp, dll)
     * @param float $value Nilai metrik
     * @return string 'UP' | 'WARNING' | 'DOWN'
     */
    private function determineStatusByPageSpeed(string $metric, float $value): string
    {
        $thresholds = config('pagespeed.thresholds', []);
        
        // Cari threshold untuk metrik
        $metricThreshold = $thresholds[$metric] ?? $thresholds['response_time'] ?? null;
        
        if (!$metricThreshold) {
            // Fallback ke response_time jika metrik tidak ditemukan
            $metricThreshold = [
                'good' => 2000,
                'warning' => 4000,
                'down' => 8000,
            ];
        }
        
        if ($value <= $metricThreshold['good']) {
            return 'UP';
        } elseif ($value <= $metricThreshold['warning']) {
            return 'WARNING';
        } else {
            return 'DOWN';
        }
    }

    /**
     * Dapatkan rekomendasi berdasarkan response time
     */
    private function getRecommendationsByTime($timeMs): array
    {
        if ($timeMs <= 2000) {
            return [
                '✅ Service dalam kondisi optimal',
                '💡 Pertahankan performa dengan monitoring rutin',
            ];
        } elseif ($timeMs <= 2500) {
            return [
                '📌 Pertimbangkan caching (Redis/Memcached)',
                '📌 Optimasi query database yang sering dipanggil',
                '📌 Aktifkan kompresi Gzip/Brotli',
            ];
        } elseif ($timeMs <= 3000) {
            return [
                '⚠️ Implementasi Full Page Cache',
                '⚠️ Optimasi gambar (lazy loading, WebP)',
                '⚠️ Minifikasi CSS/JS',
                '⚠️ Periksa external API calls',
            ];
        } elseif ($timeMs <= 4000) {
            return [
                '🚨 Upgrade spesifikasi server (RAM/CPU)',
                '🚨 Implementasi load balancer jika traffic tinggi',
                '🚨 Refactoring kode aplikasi',
                '🚨 Optimasi koneksi database (connection pool)',
                '🚨 Gunakan CDN untuk konten statis',
            ];
        } elseif ($timeMs <= 6000) {
            return [
                '🚨 Periksa status server (CPU/Memory/Disk)',
                '🚨 Cek log error untuk indikasi masalah',
                '🚨 Restart service jika diperlukan',
                '🚨 Scale up resource segera',
                '🚨 Hubungi tim infrastruktur',
            ];
        } else {
            return [
                '🚨 TINDAKAN DARURAT! Service tidak responsif',
                '🚨 Periksa server dan jaringan segera',
                '🚨 Cek log error dan sistem monitoring',
                '🚨 Restart server/service',
                '🚨 Hubungi tim on-call',
            ];
        }
    }

    /**
     * Analisis response dengan PageSpeed standar (DIPERBAIKI - LEBIH INFORMATIF)
     */
    private function analyzeResponseWithPageSpeed($code, $time): array
    {
        // Konversi ke milidetik untuk threshold PageSpeed
        $timeMs = $time * 1000;
        
        // Gunakan threshold response_time dari PageSpeed
        $status = $this->determineStatusByPageSpeed('response_time', $timeMs);
        
        // Cek base status dari kode HTTP
        $baseStatus = $this->analyzeResponse($code, $time);
        
        // Jika base status DOWN, tetap DOWN (prioritas lebih tinggi)
        if ($baseStatus['status'] === 'DOWN') {
            return $baseStatus;
        }
        
        // Format response time
        $formattedTime = number_format($time, 2) . 's';
        $formattedTimeMs = number_format($timeMs, 0) . 'ms';
        $diffMs = $timeMs - 2000;
        
        // ============================================================
        // TENTUKAN KATEGORI DAN REKOMENDASI
        // ============================================================
        
        if ($timeMs <= 2000) {
            $category = '⚡ SANGAT CEPAT';
            $impact = '✅ Pengalaman pengguna optimal. Service berjalan sangat baik.';
            $recommendations = [
                '✅ Pertahankan performa ini',
                '💡 Lakukan monitoring rutin untuk deteksi dini',
            ];
        } elseif ($timeMs <= 2500) {
            $category = '🟡 CUKUP CEPAT';
            $impact = '🟡 Masih OK, tapi mulai mendekati batas. Perhatikan trend ke depannya.';
            $recommendations = [
                '📌 Pertimbangkan caching (Redis/Memcached)',
                '📌 Optimasi query database yang sering dipanggil',
                '📌 Aktifkan kompresi Gzip/Brotli',
            ];
        } elseif ($timeMs <= 3000) {
            $category = '🟡 LAMBAT';
            $impact = '🟡 Pengguna mulai merasakan lambat. Risiko bounce rate meningkat 15-20%.';
            $recommendations = [
                '⚠️ Implementasi Full Page Cache',
                '⚠️ Optimasi gambar (lazy loading, WebP)',
                '⚠️ Minifikasi CSS/JS',
                '⚠️ Periksa external API calls',
            ];
        } elseif ($timeMs <= 4000) {
            $category = '🟠 SANGAT LAMBAT';
            $impact = '🟠 Pengalaman pengguna buruk. Bounce rate meningkat 30-40%. Konversi menurun.';
            $recommendations = [
                '🚨 Upgrade spesifikasi server (RAM/CPU)',
                '🚨 Implementasi load balancer jika traffic tinggi',
                '🚨 Refactoring kode aplikasi',
                '🚨 Optimasi koneksi database (connection pool)',
                '🚨 Gunakan CDN untuk konten statis',
            ];
        } elseif ($timeMs <= 6000) {
            $category = '🔴 KRITIS';
            $impact = '🔴 Service hampir tidak bisa diakses. Dampak bisnis signifikan.';
            $recommendations = [
                '🚨 Periksa status server (CPU/Memory/Disk)',
                '🚨 Cek log error untuk indikasi masalah',
                '🚨 Restart service jika diperlukan',
                '🚨 Scale up resource segera',
                '🚨 Hubungi tim infrastruktur',
            ];
        } else {
            $category = '🔴 SANGAT KRITIS';
            $impact = '🔴 Service DOWN! Pengguna tidak bisa mengakses sama sekali.';
            $recommendations = [
                '🚨 TINDAKAN DARURAT! Service tidak responsif',
                '🚨 Periksa server dan jaringan segera',
                '🚨 Cek log error dan sistem monitoring',
                '🚨 Restart server/service',
                '🚨 Hubungi tim on-call',
            ];
        }
        
        // ============================================================
        // STATUS WARNING
        // ============================================================
        
        if ($status === 'WARNING') {
            // 🔥 PESAN UTAMA TETAP ADA: "Response time 2.22s melewati threshold PageSpeed (good: ≤2s)"
            $mainMessage = "Response time {$formattedTime} ({$formattedTimeMs}) melewati threshold PageSpeed (good: ≤2s)";
            
            // Tambahan informasi yang informatif
            $detail = $mainMessage . "\n\n";
            $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $detail .= "📊 DETAIL ANALISIS:\n";
            $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $detail .= "⏱️  Response Time: {$formattedTime} ({$formattedTimeMs})\n";
            $detail .= "📈  Kategori: {$category}\n";
            $detail .= "🎯  Threshold: ≤ 2 detik (standar PageSpeed)\n";
            $detail .= "📊  Selisih: +" . number_format($diffMs, 0) . "ms (melewati batas)\n";
            $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $detail .= "💥  Dampak:\n";
            $detail .= "{$impact}\n";
            $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $detail .= "🔧  Rekomendasi Optimasi:\n";
            foreach ($recommendations as $rec) {
                $detail .= "• {$rec}\n";
            }
            $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $detail .= "📌  Status: WARNING - Perlu perbaikan performa";
            
            return [
                'status' => 'WARNING',
                'reason' => 'PAGESPEED_SLOW',
                'detail' => $detail,
                'action' => "Optimasi performa server: " . implode("; ", $recommendations),
                'metrics' => [
                    'response_time_ms' => $timeMs,
                    'response_time_sec' => $time,
                    'category' => $category,
                    'impact' => $impact,
                    'diff_ms' => $diffMs,
                ]
            ];
        }
        
        // ============================================================
        // STATUS DOWN
        // ============================================================
        
        if ($status === 'DOWN') {
            $mainMessage = "🚨 RESPONSE TIME TERLALU LAMBAT! {$formattedTime} ({$formattedTimeMs}) - SERVICE DOWN!";
            
            $detail = $mainMessage . "\n\n";
            $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $detail .= "📊 DETAIL ANALISIS:\n";
            $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $detail .= "⏱️  Response Time: {$formattedTime} ({$formattedTimeMs})\n";
            $detail .= "📈  Kategori: {$category}\n";
            $detail .= "🎯  Threshold: ≤ 2 detik (standar PageSpeed)\n";
            $detail .= "📊  Selisih: +" . number_format($diffMs, 0) . "ms (sangat melewati batas)\n";
            $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $detail .= "💥  Dampak Bisnis:\n";
            $detail .= "{$impact}\n";
            $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $detail .= "🔧  TINDAKAN DARURAT:\n";
            foreach ($recommendations as $rec) {
                $detail .= "• {$rec}\n";
            }
            $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            $detail .= "🚨  Status: DOWN - Segera perbaiki!";
            
            return [
                'status' => 'DOWN',
                'reason' => 'PAGESPEED_DOWN',
                'detail' => $detail,
                'action' => "Segera perbaiki server! " . implode("; ", $recommendations),
                'metrics' => [
                    'response_time_ms' => $timeMs,
                    'response_time_sec' => $time,
                    'category' => $category,
                    'impact' => $impact,
                    'diff_ms' => $diffMs,
                ]
            ];
        }
        
        // ============================================================
        // STATUS UP
        // ============================================================
        
        $mainMessage = "✅ Response time {$formattedTime} ({$formattedTimeMs}) dalam batas aman (good: ≤2s)";
        
        $detail = $mainMessage . "\n\n";
        $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $detail .= "📊 DETAIL ANALISIS:\n";
        $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $detail .= "⏱️  Response Time: {$formattedTime} ({$formattedTimeMs})\n";
        $detail .= "📈  Kategori: {$category}\n";
        $detail .= "🎯  Threshold: ≤ 2 detik (standar PageSpeed)\n";
        $detail .= "📊  Selisih: -" . number_format(abs($diffMs), 0) . "ms (dalam batas aman)\n";
        $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $detail .= "💚  Dampak:\n";
        $detail .= "{$impact}\n";
        $detail .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $detail .= "✅  Status: UP - Service berjalan dengan baik";
        
        return [
            'status' => 'UP',
            'reason' => 'PAGESPEED_GOOD',
            'detail' => $detail,
            'action' => 'Service dalam kondisi baik. Pertahankan performa.',
            'metrics' => [
                'response_time_ms' => $timeMs,
                'response_time_sec' => $time,
                'category' => $category,
                'impact' => $impact,
                'diff_ms' => $diffMs,
            ]
        ];
    }

    /**
     * ============================================================
     * 🌐 CHECK NETWORK CONNECTION
     * ============================================================
     */
    public function checkNetworkConnection()
    {
        $cacheKey = 'network_connection_status';
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            Log::info('🌐 [CACHE] Internet: ' . ($cached ? 'ONLINE' : 'OFFLINE'));
            return $cached;
        }

        Log::info('🔍 [CHECK] Starting internet connection check...');

        $httpTargets = [
            'https://www.google.com',
            'https://www.cloudflare.com',
        ];
        
        foreach ($httpTargets as $target) {
            try {
                $response = Http::timeout(5)->get($target);
                if ($response->successful()) {
                    Log::info("✅ [HTTP] SUCCESS: {$target}");
                    Cache::put($cacheKey, true, 60);
                    return true;
                }
            } catch (\Exception $e) {
                Log::info("❌ [HTTP] FAILED: {$target}");
            }
        }

        $pingTargets = ['8.8.8.8', '1.1.1.1'];
        foreach ($pingTargets as $target) {
            if ($this->pingHost($target)) {
                Log::info("✅ [PING] SUCCESS: {$target}");
                Cache::put($cacheKey, true, 60);
                return true;
            }
        }

        $dnsTargets = ['google.com', 'cloudflare.com'];
        foreach ($dnsTargets as $target) {
            if (checkdnsrr($target, 'A')) {
                Log::info("✅ [DNS] SUCCESS: {$target}");
                Cache::put($cacheKey, true, 60);
                return true;
            }
        }

        Log::info('❌ [CHECK] ALL METHODS FAILED - Internet DOWN');
        Cache::put($cacheKey, false, 60);
        return false;
    }

    /**
     * 🔥🔥🔥 PING HOST - DIPERBAIKI UNTUK WINDOWS
     */
    private function pingHost($host)
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        if ($isWindows) {
            // 🔥 PAKAI FULL PATH PING DI WINDOWS
            $pingPath = 'C:\\Windows\\System32\\ping.exe';
            
            // Cek apakah file exists
            if (!file_exists($pingPath)) {
                // Fallback: coba cari di PATH
                $pingPath = 'ping';
                Log::warning("⚠️ ping.exe tidak ditemukan di C:\\Windows\\System32\\, menggunakan 'ping' dari PATH");
            }
            
            $command = $pingPath . " -n 1 -w 3000 " . escapeshellarg($host) . " 2>&1";
        } else {
            $command = "ping -c 1 -W 3 " . escapeshellarg($host) . " 2>&1";
        }
        
        exec($command, $output, $resultCode);
        return $resultCode === 0;
    }

    /**
     * ============================================================
     * 🔍 CHECK SSL CERTIFICATE
     * ============================================================
     */
    private function checkSSL($service, $host, $port = 443)
    {
        Log::info("🔍 Checking SSL certificate for {$host}:{$port}");

        try {
            // 🔥 CEK APAKAH OPENSSL TERSEDIA DI WINDOWS
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $opensslCmd = 'openssl';
            
            if ($isWindows) {
                // Cek apakah openssl ada di PATH
                $checkOpenssl = shell_exec('where openssl 2>nul');
                if (empty($checkOpenssl)) {
                    Log::warning("⚠️ OpenSSL tidak ditemukan di Windows, skip SSL check untuk {$host}");
                    // Fallback ke PHP stream
                    return $this->checkSSLviaStream($service, $host, $port);
                }
            }

            $command = sprintf(
                'echo | %s s_client -servername %s -connect %s:%d 2>/dev/null | %s x509 -noout -dates 2>/dev/null',
                $opensslCmd,
                escapeshellarg($host),
                escapeshellarg($host),
                $port,
                $opensslCmd
            );
            
            $output = shell_exec($command);
            
            if (!empty($output)) {
                preg_match('/notBefore=(.*)/', $output, $beforeMatch);
                preg_match('/notAfter=(.*)/', $output, $afterMatch);
                
                if (!empty($afterMatch[1])) {
                    $validFrom = Carbon::parse(trim($beforeMatch[1]));
                    $validTo = Carbon::parse(trim($afterMatch[1]));
                    $now = Carbon::now('Asia/Jakarta');
                    $daysRemaining = (int)ceil($now->diffInDays($validTo, false));
                
                    $subjectCommand = sprintf(
                        'echo | %s s_client -servername %s -connect %s:%d 2>/dev/null | %s x509 -noout -subject 2>/dev/null',
                        $opensslCmd,
                        escapeshellarg($host),
                        escapeshellarg($host),
                        $port,
                        $opensslCmd
                    );
                    $subjectOutput = shell_exec($subjectCommand);
                    
                    $issuerCommand = sprintf(
                        'echo | %s s_client -servername %s -connect %s:%d 2>/dev/null | %s x509 -noout -issuer 2>/dev/null',
                        $opensslCmd,
                        escapeshellarg($host),
                        escapeshellarg($host),
                        $port,
                        $opensslCmd
                    );
                    $issuerOutput = shell_exec($issuerCommand);
                    
                    $commonName = 'Unknown';
                    $issuerCN = 'Unknown';
                    $organization = '';
                    
                    if (!empty($subjectOutput)) {
                        preg_match('/CN\s*=\s*([^,\/]+)/', $subjectOutput, $subjectMatch);
                        if (!empty($subjectMatch[1])) {
                            $commonName = trim($subjectMatch[1]);
                        }
                        preg_match('/O\s*=\s*([^,\/]+)/', $subjectOutput, $orgMatch);
                        if (!empty($orgMatch[1])) {
                            $organization = trim($orgMatch[1]);
                        }
                    }
                    
                    if (!empty($issuerOutput)) {
                        preg_match('/CN\s*=\s*([^,\/]+)/', $issuerOutput, $issuerMatch);
                        if (!empty($issuerMatch[1])) {
                            $issuerCN = trim($issuerMatch[1]);
                        }
                    }
                    
                    Log::info("📊 SSL Certificate {$host}:", [
                        'subject' => $commonName,
                        'issuer' => $issuerCN,
                        'valid_from' => $validFrom->format('Y-m-d'),
                        'valid_to' => $validTo->format('Y-m-d'),
                        'days_remaining' => $daysRemaining
                    ]);
                    
                    return $this->processSSLResult($service, $host, $validFrom, $validTo, $daysRemaining, $commonName, $organization, $issuerCN);
                }
            }
            
            // ALTERNATIF: PHP Stream dengan SNI
            Log::info("⚠️ OpenSSL command failed, using PHP stream with SNI for {$host}");
            return $this->checkSSLviaStream($service, $host, $port);
            
        } catch (\Exception $e) {
            Log::error("❌ SSL Check error untuk {$host}:{$port} - " . $e->getMessage());
            
            // Fallback ke PHP stream
            try {
                return $this->checkSSLviaStream($service, $host, $port);
            } catch (\Exception $e2) {
                Log::error("❌ SSL Stream fallback juga gagal: " . $e2->getMessage());
                return null;
            }
        }
    }

    /**
     * 🔥 CHECK SSL VIA PHP STREAM (FALLBACK)
     */
    private function checkSSLviaStream($service, $host, $port = 443)
    {
        try {
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'SNI_enabled' => true,           
                    'peer_name' => $host,            
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ]
            ]);
            
            $client = stream_socket_client(
                "ssl://{$host}:{$port}",
                $errno,
                $errstr,
                10,
                STREAM_CLIENT_CONNECT,
                $context
            );
            
            if (!$client) {
                Log::warning("⚠️ Gagal connect SSL ke {$host}:{$port} - {$errstr}");
                return null;
            }
            
            $params = stream_context_get_params($client);
            $cert = $params['options']['ssl']['peer_certificate'] ?? null;
            
            if (!$cert) {
                fclose($client);
                Log::warning("⚠️ Tidak ada SSL certificate untuk {$host}:{$port}");
                return null;
            }
            
            $certInfo = openssl_x509_parse($cert);
            
            if (!$certInfo) {
                fclose($client);
                Log::warning("⚠️ Gagal parse SSL certificate untuk {$host}:{$port}");
                return null;
            }
            
            fclose($client);
            
            $validFrom = Carbon::createFromTimestamp($certInfo['validFrom_time_t'], 'Asia/Jakarta');
            $validTo = Carbon::createFromTimestamp($certInfo['validTo_time_t'], 'Asia/Jakarta');
            $now = Carbon::now('Asia/Jakarta');
            $daysRemaining = (int)ceil($now->diffInDays($validTo, false));
            
            $subject = $certInfo['subject'] ?? [];
            $issuer = $certInfo['issuer'] ?? [];
            
            $commonName = $subject['CN'] ?? 'Unknown';
            $organization = $subject['O'] ?? '';
            $issuerCN = $issuer['CN'] ?? 'Unknown';
            
            Log::info("📊 SSL Certificate {$host} (via PHP Stream):", [
                'subject' => $commonName,
                'issuer' => $issuerCN,
                'valid_from' => $validFrom->format('Y-m-d'),
                'valid_to' => $validTo->format('Y-m-d'),
                'days_remaining' => $daysRemaining
            ]);
            
            return $this->processSSLResult($service, $host, $validFrom, $validTo, $daysRemaining, $commonName, $organization, $issuerCN);
            
        } catch (\Exception $e) {
            Log::error("❌ SSL Stream error untuk {$host}:{$port} - " . $e->getMessage());
            return null;
        }
    }

    /**
     * ============================================================
     * PROCESS SSL RESULT
     * ============================================================
     */
    private function processSSLResult($service, $host, $validFrom, $validTo, $daysRemaining, $commonName, $organization, $issuerCN)
    {
        // 🔥 REFRESH DATA DARI DATABASE TERBARU
        $service->refresh();
        
        // SSL EXPIRED = DOWN!
        if ($daysRemaining <= 0) {
            $status = 'EXPIRED';
            $isDown = true;
            $message = "🔴 SSL CERTIFICATE EXPIRED! Expired sejak {$validTo->format('d-m-Y')} - SERVICE DOWN!";
            $action = '🚨 SEGERA PERBARUI SSL CERTIFICATE! Service tidak aman dan tidak bisa diakses!';
            
            $sendAlert = is_null($service->ssl_expired_sent_at);
            
            if ($sendAlert) {
                Log::info("🚨 SSL EXPIRED: First time, will send WA");
            } else {
                Log::info("⏭️ SSL EXPIRED WA already sent at {$service->ssl_expired_sent_at}");
            }
            
            $this->saveSSLResult($service, [
                'status' => $status,
                'is_down' => $isDown,
                'subject' => $commonName,
                'issuer' => $issuerCN,
                'valid_from' => $validFrom->format('Y-m-d H:i:s'),
                'valid_to' => $validTo->format('Y-m-d H:i:s'),
                'days_remaining' => $daysRemaining,
                'message' => $message,
                'action' => $action,
                'send_alert' => $sendAlert
            ]);
            
            return [
                'valid' => true,
                'status' => $status,
                'is_down' => $isDown,
                'subject' => $commonName,
                'organization' => $organization,
                'issuer' => $issuerCN,
                'valid_from' => $validFrom->format('Y-m-d H:i:s'),
                'valid_to' => $validTo->format('Y-m-d H:i:s'),
                'days_remaining' => $daysRemaining,
                'message' => $message,
                'action' => $action,
                'send_alert' => $sendAlert
            ];
        }
        
        // CRITICAL (7 hari)
        if ($daysRemaining <= self::SSL_CRITICAL_DAYS) {
            $status = 'CRITICAL';
            $isDown = false;
            $message = "🔴 SSL akan expired dalam {$daysRemaining} hari! (Exp: {$validTo->format('d-m-Y')})";
            $action = '⚠️ SEGERA perpanjang SSL certificate! Tinggal ' . $daysRemaining . ' hari lagi!';
            
            $sendAlert = is_null($service->ssl_critical_sent_at);
            
            if ($sendAlert) {
                Log::info("🔴 SSL CRITICAL: First time, will send WA ({$daysRemaining} days remaining)");
            } else {
                Log::info("⏭️ SSL CRITICAL WA already sent at {$service->ssl_critical_sent_at}");
            }
            
            $this->saveSSLResult($service, [
                'status' => $status,
                'is_down' => $isDown,
                'subject' => $commonName,
                'issuer' => $issuerCN,
                'valid_from' => $validFrom->format('Y-m-d H:i:s'),
                'valid_to' => $validTo->format('Y-m-d H:i:s'),
                'days_remaining' => $daysRemaining,
                'message' => $message,
                'action' => $action,
                'send_alert' => $sendAlert
            ]);
            
            $service->update([
                'last_status' => 'WARNING',
                'last_message' => $message,
                'last_check_at' => now(),
            ]);
            Log::info("🟡 Service status changed to WARNING due to SSL CRITICAL: {$service->name}");
            
            return [
                'valid' => true,
                'status' => $status,
                'is_down' => $isDown,
                'subject' => $commonName,
                'organization' => $organization,
                'issuer' => $issuerCN,
                'valid_from' => $validFrom->format('Y-m-d H:i:s'),
                'valid_to' => $validTo->format('Y-m-d H:i:s'),
                'days_remaining' => $daysRemaining,
                'message' => $message,
                'action' => $action,
                'send_alert' => $sendAlert
            ];
        }
        
        // WARNING (60 hari)
        if ($daysRemaining <= self::SSL_WARNING_DAYS) {
            $status = 'WARNING';
            $isDown = false;
            $message = "🟡 SSL akan expired dalam {$daysRemaining} hari (Exp: {$validTo->format('d-m-Y')})";
            $action = '📌 Rencanakan perpanjangan SSL certificate dalam ' . $daysRemaining . ' hari';
            
            $sendAlert = is_null($service->ssl_warning_sent_at);
            
            if ($sendAlert) {
                Log::info("🟡 SSL WARNING: First time, will send WA ({$daysRemaining} days remaining)");
            } else {
                Log::info("⏭️ SSL WARNING WA already sent at {$service->ssl_warning_sent_at}");
            }
            
            $this->saveSSLResult($service, [
                'status' => $status,
                'is_down' => $isDown,
                'subject' => $commonName,
                'issuer' => $issuerCN,
                'valid_from' => $validFrom->format('Y-m-d H:i:s'),
                'valid_to' => $validTo->format('Y-m-d H:i:s'),
                'days_remaining' => $daysRemaining,
                'message' => $message,
                'action' => $action,
                'send_alert' => $sendAlert
            ]);
            
            $service->update([
                'last_status' => 'WARNING',
                'last_message' => $message,
                'last_check_at' => now(),
            ]);
            Log::info("🟡 Service status changed to WARNING due to SSL: {$service->name}");
            
            return [
                'valid' => true,
                'status' => $status,
                'is_down' => $isDown,
                'subject' => $commonName,
                'organization' => $organization,
                'issuer' => $issuerCN,
                'valid_from' => $validFrom->format('Y-m-d H:i:s'),
                'valid_to' => $validTo->format('Y-m-d H:i:s'),
                'days_remaining' => $daysRemaining,
                'message' => $message,
                'action' => $action,
                'send_alert' => $sendAlert
            ];
        }
        
        // VALID (> 60 hari)
        $status = 'VALID';
        $isDown = false;
        $message = "✅ Certificate valid until {$validTo->format('d-m-Y')} (sisa {$daysRemaining} hari)";
        $action = 'Certificate dalam kondisi baik';
        
        if ($service->ssl_status !== 'VALID') {
            $service->update([
                'ssl_warning_sent_at' => null,
                'ssl_critical_sent_at' => null,
                'ssl_expired_sent_at' => null,
            ]);
            Log::info("🔄 SSL timestamps reset for {$service->name} (certificate valid)");
        }
        
        $this->saveSSLResult($service, [
            'status' => $status,
            'is_down' => $isDown,
            'subject' => $commonName,
            'issuer' => $issuerCN,
            'valid_from' => $validFrom->format('Y-m-d H:i:s'),
            'valid_to' => $validTo->format('Y-m-d H:i:s'),
            'days_remaining' => $daysRemaining,
            'message' => $message,
            'action' => $action,
            'send_alert' => false
        ]);
        
        if ($service->last_status === 'WARNING') {
            $service->update([
                'last_status' => 'UP',
                'last_message' => 'Service normal, SSL valid',
                'last_check_at' => now(),
            ]);
            Log::info("🟢 Service status restored to UP: {$service->name}");
        }
        
        return [
            'valid' => true,
            'status' => $status,
            'is_down' => $isDown,
            'subject' => $commonName,
            'organization' => $organization,
            'issuer' => $issuerCN,
            'valid_from' => $validFrom->format('Y-m-d H:i:s'),
            'valid_to' => $validTo->format('Y-m-d H:i:s'),
            'days_remaining' => $daysRemaining,
            'message' => $message,
            'action' => $action,
            'send_alert' => false
        ];
    }

    /**
     * ============================================================
     * 💾 SAVE SSL RESULT
     * ============================================================
     */
    private function saveSSLResult($service, $sslData)
    {
        Log::info("💾 Save SSL Result for {$service->name}: " . $sslData['status']);
        
        $oldSslStatus = $service->ssl_status;
        
        $service->update([
            'ssl_status' => $sslData['status'],
            'ssl_expiry_date' => $sslData['valid_to'] ?? null,
            'ssl_days_remaining' => $sslData['days_remaining'] ?? null,
            'ssl_issuer' => $sslData['issuer'] ?? null,
            'ssl_subject' => $sslData['subject'] ?? null,
            'ssl_message' => $sslData['message'] ?? null,
            'ssl_is_expired' => $sslData['is_down'] ?? false,
            'ssl_checked_at' => now(),
        ]);
        
        // 🔥 UPDATE SSL TIMESTAMP HANYA 1x PER STATUS
        if ($sslData['status'] === 'WARNING' && $sslData['send_alert'] === true) {
            $service->update(['ssl_warning_sent_at' => now()]);
            Log::info("📝 SSL WARNING timestamp saved: {$service->name}");
        }
        
        if ($sslData['status'] === 'CRITICAL' && $sslData['send_alert'] === true) {
            $service->update(['ssl_critical_sent_at' => now()]);
            Log::info("📝 SSL CRITICAL timestamp saved: {$service->name}");
        }
        
        if ($sslData['status'] === 'EXPIRED' && $sslData['send_alert'] === true) {
            $service->update(['ssl_expired_sent_at' => now()]);
            Log::info("📝 SSL EXPIRED timestamp saved: {$service->name}");
        }
        
        // LOG HANYA JIKA STATUS SSL BERUBAH
        if (in_array($sslData['status'], ['WARNING', 'CRITICAL', 'EXPIRED']) && 
            $oldSslStatus !== $sslData['status']) {
            ServiceLog::create([
                'service_id' => $service->id,
                'status' => $sslData['is_down'] ? 'DOWN' : 'WARNING',
                'response_code' => 'SSL_' . $sslData['status'],
                'response_time' => 0,
                'message' => $sslData['message'] ?? 'SSL Certificate check',
                'action' => $sslData['action'] ?? 'Periksa SSL certificate',
                'checked_at' => now(),
                'is_status_change' => true,
                'previous_status' => $service->last_status ?? 'UNKNOWN',
            ]);
            Log::info("📝 SSL LOG BARU: {$service->name} SSL Status: {$oldSslStatus} → {$sslData['status']}");
        }
    }

    /**
     * ============================================================
     * 📨 KIRIM WA SSL ALERT
     * ============================================================
     */
    private function sendSSLAlert($service, $sslResult)
    {
        $contacts = Contact::where('is_active', true)->get();
        if ($contacts->isEmpty()) {
            Log::warning('Tidak ada kontak aktif untuk SSL Alert');
            return;
        }

        $newline = "\n";
        
        if ($sslResult['is_down']) {
            $judul = "🔴🚨 SSL CERTIFICATE EXPIRED! SERVICE DOWN!";
            $icon = "🔴";
            $statusText = "DOWN - EXPIRED";
            $urgency = "🚨 SEGERA PERBAIKI!";
        } elseif ($sslResult['status'] === 'CRITICAL') {
            $judul = "🔴⚠️ SSL CERTIFICATE CRITICAL!";
            $icon = "🔴";
            $statusText = "CRITICAL - Segera Perbarui!";
            $urgency = "⚠️ SEGERA PERPANJANG! Tinggal " . $sslResult['days_remaining'] . " hari lagi!";
        } else {
            $judul = "🟡 SSL CERTIFICATE WARNING";
            $icon = "🟡";
            $statusText = "WARNING - Akan Expired";
            $urgency = "📌 Rencanakan perpanjangan SSL";
        }

        $message = $judul . $newline . $newline;
        $message .= "Nama    : " . $service->name . $newline;
        $message .= "Domain  : " . $service->target . $newline;
        $message .= $newline;
        $message .= "Status  : " . $icon . " " . $statusText . $newline;
        $message .= "Issuer  : " . ($sslResult['issuer'] ?? 'Unknown') . $newline;
        $message .= "Subject : " . ($sslResult['subject'] ?? 'Unknown') . $newline;
        $message .= $newline;
        $message .= "Sisa Hari : " . $sslResult['days_remaining'] . " hari" . $newline;
        $message .= "Valid From: " . ($sslResult['valid_from'] ?? 'Unknown') . $newline;
        $message .= "Expired   : " . ($sslResult['valid_to'] ?? 'Unknown') . $newline;
        $message .= $newline;
        $message .= $urgency . $newline;
        $message .= $newline;
        $message .= "Detail:" . $newline;
        $message .= $sslResult['message'] . $newline;
        $message .= $newline;
        $message .= "Tindakan:" . $newline;
        $message .= $sslResult['action'] . $newline;
        $message .= $newline;
        $message .= "🕐 " . now()->format('d-m-Y H:i:s') . " WIB";

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            Log::info($result ? "✅ SSL WA ke: {$contact->phone}" : "❌ Gagal SSL WA ke: {$contact->phone}");
        }
    }

    /**
     * ============================================================
     * 🔍 CHECK HTTP (DIMODIFIKASI DENGAN PAGESPEED)
     * ============================================================
     */
    private function checkHttp(Service $service)
    {
        $oldStatus = $service->last_status ?? 'UNKNOWN';
        $code = null;
        $time = 0;
        $start = microtime(true);
        $analysis = null;

        // CEK SSL UNTUK HTTPS
        $url = $this->normalizeUrl($service->target);
        $sslResult = null;
        $parsedUrl = parse_url($url);
        
        if ($parsedUrl && ($parsedUrl['scheme'] ?? '') === 'https') {
            $host = $parsedUrl['host'] ?? '';
            $port = $parsedUrl['port'] ?? 443;
            
            if (!empty($host)) {
                $sslResult = $this->checkSSL($service, $host, $port);
                
                // 🔥🔥🔥 SSL EXPIRED
                if ($sslResult && $sslResult['is_down'] === true) {
                    Log::info("🚨 SSL EXPIRED DETECTED! Setting service to DOWN");
                    
                    $this->saveResult(
                        $service, 
                        $oldStatus, 
                        'DOWN', 
                        'SSL_EXPIRED', 
                        0, 
                        'SSL_EXPIRED',
                        $sslResult['message'],
                        $sslResult['action']
                    );
                    
                    // 🔥 PAKAI LANGSUNG $sslResult['send_alert'] + INTERVAL
                    $this->handleSSLInterval($service, $sslResult);
                    
                    return;
                }
                
                // 🔥🔥🔥 SSL WARNING/CRITICAL - DENGAN INTERVAL!
                if ($sslResult && ($sslResult['status'] === 'WARNING' || $sslResult['status'] === 'CRITICAL')) {
                    Log::info("🟡 SSL " . $sslResult['status'] . " detected!");
                    
                    // UPDATE STATUS SERVICE JADI WARNING
                    $service->update([
                        'last_status' => 'WARNING',
                        'last_message' => $sslResult['message'],
                        'last_check_at' => now(),
                    ]);
                    Log::info("✅ Service status updated to WARNING: {$service->name}");
                    
                    // 🔥🔥🔥 CEK SSL TIMESTAMP + INTERVAL
                    $this->handleSSLInterval($service, $sslResult);
                    
                    return;
                }
            }
        }

        try {
            $url = $this->normalizeUrl($service->target);
            $start = microtime(true);

            $response = Http::timeout(30)
                ->connectTimeout(20)
                ->withoutRedirecting()
                ->get($url);

            $time = round(microtime(true) - $start, 2);
            $code = $response->status();

            Log::info("HTTP Response {$service->name}: code={$code}, time={$time}s");

            if (in_array($code, [301, 308])) {
                $location = $response->header('Location');
                Log::info("🔀 REDIRECT PERMANEN {$code} ke: {$location}");
                
                $redirectResult = $this->checkRedirectTarget($location, $service);
                
                if ($redirectResult['status'] === 'UP') {
                    $analysis = [
                        'status' => 'UP',
                        'reason' => 'REDIRECT_' . $code,
                        'detail' => "Redirect permanen ke: {$location} - Target redirect UP",
                        'action' => "Update URL endpoint ke: {$location}"
                    ];
                } else {
                    $analysis = [
                        'status' => 'DOWN',
                        'reason' => 'REDIRECT_' . $code . '_FAILED',
                        'detail' => "Redirect permanen ke: {$location} - Target redirect {$redirectResult['status']}",
                        'action' => "Periksa target redirect: {$location} - {$redirectResult['message']}"
                    ];
                }
                
                $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time, 
                                 $analysis['reason'], $analysis['detail'], $analysis['action']);
                return;
            }

            // 🔥🔥🔥 MODIFIKASI: Gunakan PageSpeed untuk analisis
            $analysis = $this->analyzeResponseWithPageSpeed($code, $time);
            
            // Tambahan: cek konten error jika status UP
            if ($analysis['status'] === 'UP') {
                $body = $response->body();
                if (!empty($body)) {
                    $errorKeywords = ['fatal error', 'parse error', 'syntax error', 'exception', 'stack trace', 'database error'];
                    $bodyLower = strtolower($body);
                    
                    foreach ($errorKeywords as $keyword) {
                        if (str_contains($bodyLower, $keyword)) {
                            $analysis = [
                                'status' => 'WARNING',
                                'reason' => 'PAGESPEED_CONTENT_ERROR',
                                'detail' => "Response cepat tapi konten error: '{$keyword}'",
                                'action' => 'Periksa log server dan perbaiki error aplikasi',
                            ];
                            break;
                        }
                    }
                }
                
                // Cek response kosong
                if (empty($body) || trim($body) === '') {
                    $analysis = [
                        'status' => 'WARNING',
                        'reason' => 'PAGESPEED_EMPTY_RESPONSE',
                        'detail' => 'Response cepat tapi konten kosong',
                        'action' => 'Periksa apakah halaman memang kosong atau ada error',
                    ];
                }
            }
            
            Log::info("Analysis {$service->name}: " . json_encode($analysis));

            if ($analysis['status'] === 'UP') {
                $service->update(['consecutive_failures' => 0]);
            }

        } catch (ConnectionException $e) {
            $time = round(microtime(true) - $start, 2);
            $code = 'TIMEOUT';
            
            Log::error("Connection timeout {$service->name}: " . $e->getMessage());
            
            if ($time <= self::TIMEOUT_FAST) {
                Log::info("⚡ TIMEOUT CEPAT ({$time}s) untuk {$service->name} - LANGSUNG DOWN");
                $service->update(['consecutive_failures' => 0]);
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                                 'CONNECTION_TIMEOUT_FAST', 
                                 "Koneksi timeout cepat ({$time}s) - Server tidak merespon", 
                                 'Server kemungkinan mati, periksa segera');
            } else {
                Log::info(" TIMEOUT LAMBAT ({$time}s) untuk {$service->name} - pakai consecutive");
                $this->handleTimeoutFailure($service, $oldStatus, $time, 
                                           'CONNECTION_TIMEOUT_SLOW', 
                                           "Koneksi timeout lambat ({$time}s) - Server lambat merespon", 
                                           'Periksa performa server dan koneksi jaringan');
            }
            return;
            
        } catch (\Exception $e) {
            $time = 0;
            $code = 'ERROR';
            $analysis = $this->analyzeException($e->getMessage());
            Log::error("HTTP Error {$service->name}: " . $e->getMessage());
            
            $service->update(['consecutive_failures' => 0]);
            $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time, 
                             $analysis['reason'], $analysis['detail'], $analysis['action']);
            return;
        }

        if ($analysis !== null) {
            $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time, 
                             $analysis['reason'], $analysis['detail'], $analysis['action']);
        }
    }

    /**
     * ============================================================
     * 🔄 HANDLE SSL INTERVAL 
     * ============================================================
     */
    private function handleSSLInterval($service, $sslResult)
    {
        $interval = $service->wa_interval_minutes ?? 0;
        $status = $sslResult['status'];
        
        Log::info("🔍 SSL INTERVAL CHECK: {$service->name} | Interval: {$interval} menit | Status: {$status}");
        
        // CEK APAKAH WA PERLU DIKIRIM (TIMESTAMP SSL + INTERVAL)
        $shouldSendWa = false;
        
        if ($sslResult['send_alert'] === true) {
            $lastIntervalCheck = $service->last_interval_checked_at;
            $lastIntervalStatus = $service->last_interval_status;
            
            if ($interval == 0) {
                // Interval 0 → langsung kirim
                $shouldSendWa = true;
                Log::info("📨 Interval 0, langsung kirim SSL WA");
            } elseif (empty($lastIntervalCheck) || $lastIntervalStatus !== $status) {
                // Belum pernah check atau status berubah → kirim
                $shouldSendWa = true;
                Log::info("📨 SSL status baru/berubah ({$lastIntervalStatus} → {$status}), kirim WA");
            } else {
                $lastCheck = Carbon::parse($lastIntervalCheck);
                $minutesSinceLastCheck = $lastCheck->diffInRealMinutes(now());
                
                if ($minutesSinceLastCheck >= $interval) {
                    // Interval tercapai tapi status sama → TIDAK kirim
                    Log::info("⏭️ Interval tercapai ({$minutesSinceLastCheck}/{$interval} menit) tapi SSL status sama, TIDAK kirim WA");
                } else {
                    Log::info("⏳ Interval belum tercapai ({$minutesSinceLastCheck}/{$interval} menit) - TIDAK KIRIM WA");
                }
            }
        } else {
            Log::info("⏭️ SSL WA sudah pernah dikirim (timestamp SSL ada), skip");
        }
        
        // KIRIM ATAU TIDAK
        if ($shouldSendWa) {
            $this->sendSSLAlert($service, $sslResult);
            Log::info("📨 SSL {$status} WA sent for {$service->name}");
            
            $service->update([
                'last_wa_sent_at' => now(),
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 1,
            ]);
        } else {
            //  interval tracking tanpa kirim WA
            $service->update([
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 0,
            ]);
        }
        
        $service->refresh();
        Log::info("✅ Final SSL status: last_status={$service->last_status}, ssl_status={$service->ssl_status}");
    }

    /**
     * ============================================================
     * 🔀 CEK TARGET REDIRECT
     * ============================================================
     */
    private function checkRedirectTarget($url, $service)
    {
        if (empty($url) || $url === '-' || $url === '') {
            Log::warning("⚠️ Redirect target kosong untuk {$service->name}");
            return [
                'status' => 'DOWN',
                'message' => "Redirect target kosong (tidak ada Location header)"
            ];
        }

        try {
            Log::info("🔍 Cek target redirect: {$url}");
            
            $response = Http::timeout(30)
                ->connectTimeout(20)
                ->withoutRedirecting()
                ->get($url);
            
            $code = $response->status();
            
            Log::info("📊 Target redirect response: code={$code}");
            
            if ($code >= 200 && $code < 400) {
                return [
                    'status' => 'UP',
                    'message' => "Target redirect merespon dengan code {$code}"
                ];
            } elseif ($code == 404) {
                return [
                    'status' => 'DOWN',
                    'message' => "Target redirect 404 Not Found"
                ];
            } elseif ($code >= 400 && $code < 500) {
                return [
                    'status' => 'DOWN',
                    'message' => "Target redirect Client Error {$code}"
                ];
            } elseif ($code >= 500 && $code < 600) {
                return [
                    'status' => 'DOWN',
                    'message' => "Target redirect Server Error {$code}"
                ];
            } else {
                return [
                    'status' => 'DOWN',
                    'message' => "Target redirect unknown code {$code}"
                ];
            }
            
        } catch (ConnectionException $e) {
            return [
                'status' => 'DOWN',
                'message' => "Target redirect timeout: " . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'DOWN',
                'message' => "Target redirect error: " . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================================
     * 📡 CHECK PING - DIPERBAIKI UNTUK WINDOWS
     * ============================================================
     */
    private function checkPing(Service $service)
    {
        $oldStatus = $service->last_status ?? 'UNKNOWN';
        $code = 'N/A';
        $time = 0;
        $start = microtime(true);

        $target = $service->target;
        $parts = explode(':', $target);
        $host = $parts[0];
        $port = isset($parts[1]) ? (int)$parts[1] : null;

        if ($port) {
            if ($port < 1 || $port > 65535) {
                $time = round(microtime(true) - $start, 2);
                $code = 'INVALID_PORT';
                $service->update(['consecutive_failures' => 0]);
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                                 'INVALID_PORT', "Port {$port} tidak valid", 'Periksa format port (1-65535)');
                return;
            }

            $connection = @fsockopen($host, $port, $errno, $errstr, 10);
            $time = round(microtime(true) - $start, 2);

            if ($connection) {
                fclose($connection);
                $code = 'PORT_OPEN';
                $service->update(['consecutive_failures' => 0]);
                $this->saveResult($service, $oldStatus, 'UP', $code, $time, 
                                 'PORT_OK', "Host {$host} merespon port {$port}", 'Port terbuka, service berjalan normal');
            } else {
                $code = 'PORT_CLOSED';
                $service->update(['consecutive_failures' => 0]);
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                                 'PORT_CLOSED', "Port {$port} tidak merespon", 'Periksa firewall dan pastikan service berjalan di port tersebut');
            }
            return;
        }

        if (!filter_var($host, FILTER_VALIDATE_IP)) {
            if (!checkdnsrr($host, 'A') && !checkdnsrr($host, 'AAAA')) {
                $time = round(microtime(true) - $start, 2);
                $code = 'DNS_ERROR';
                $service->update(['consecutive_failures' => 0]);
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                                 'DNS_ERROR', "Hostname {$host} tidak dapat di-resolve", 'Periksa konfigurasi DNS server');
                return;
            }
        }

        // 🔥🔥🔥 PERBAIKAN UNTUK WINDOWS - PAKAI FULL PATH
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        
        if ($isWindows) {
            // 🔥 PAKAI FULL PATH PING DI WINDOWS
            $pingPath = 'C:\\Windows\\System32\\ping.exe';
            
            // Cek apakah file exists
            if (!file_exists($pingPath)) {
                // Fallback: coba cari di PATH
                $pingPath = 'ping';
                Log::warning("⚠️ ping.exe tidak ditemukan di C:\\Windows\\System32\\, menggunakan 'ping' dari PATH");
            }
            
            $command = $pingPath . " -n 2 -w 10000 " . escapeshellarg($host) . " 2>&1";
        } else {
            $command = "ping -c 2 -W 10 " . escapeshellarg($host) . " 2>&1";
        }
        
        exec($command, $output, $resultCode);
        $outputString = implode("\n", $output);
        $time = round(microtime(true) - $start, 2);

        Log::info("Ping result for {$host}:", [
            'resultCode' => $resultCode,
            'time' => $time . 's',
            'output' => $outputString
        ]);

        if (strpos($outputString, 'Destination host unreachable') !== false ||
            strpos($outputString, 'Host unreachable') !== false ||
            strpos($outputString, 'unreachable') !== false) {
            $code = 'UNREACHABLE';
            $service->update(['consecutive_failures' => 0]);
            $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                             'HOST_UNREACHABLE', 'Host tidak dapat dijangkau', 
                             'Periksa koneksi jaringan, firewall, dan routing');
            return;
        }

        if (strpos($outputString, 'Request timed out') !== false ||
            strpos($outputString, 'timeout') !== false ||
            strpos($outputString, 'Timed out') !== false) {
            
            preg_match('/(\d+)\s*received/i', $outputString, $receivedMatches);
            $received = isset($receivedMatches[1]) ? intval($receivedMatches[1]) : 0;
            
            if ($received > 0) {
                $code = 'PING_PARTIAL';
                $service->update(['consecutive_failures' => 0]);
                $this->saveResult($service, $oldStatus, 'WARNING', $code, $time, 
                                 'PING_PARTIAL', "Ping timeout ({$received}/2 berhasil)", 
                                 'Packet loss terdeteksi, periksa kualitas jaringan');
                return;
            }
            
            $code = 'TIMEOUT';
            
            if ($time <= self::TIMEOUT_FAST) {
                Log::info("⚡ PING TIMEOUT CEPAT ({$time}s) untuk {$host} - LANGSUNG DOWN");
                $service->update(['consecutive_failures' => 0]);
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                                 'PING_TIMEOUT_FAST', 
                                 "Ping timeout cepat ({$time}s) - Host tidak merespon", 
                                 'Server kemungkinan mati, periksa segera');
            } else {
                Log::info("🐌 PING TIMEOUT LAMBAT ({$time}s) untuk {$host} - pakai consecutive");
                $this->handleTimeoutFailure($service, $oldStatus, $time, 
                                           'PING_TIMEOUT_SLOW', 
                                           "Ping timeout lambat ({$time}s) - Host lambat merespon", 
                                           'Periksa performa server dan koneksi jaringan');
            }
            return;
        }

        if ($resultCode === 0) {
            preg_match_all('/(?:time[=:]\s*)(\d+\.?\d*)\s*ms/i', $outputString, $matches);
            
            $avgTime = 0;
            if (!empty($matches[1])) {
                $times = array_map('floatval', $matches[1]);
                $avgTime = round(array_sum($times) / count($times) / 1000, 3);
            }
            
            $code = 'PING_OK';
            
            $service->update(['consecutive_failures' => 0]);
            
            if ($avgTime > 10) {
                $this->saveResult($service, $oldStatus, 'WARNING', $code, 
                                 $avgTime > 0 ? $avgTime : $time, 
                                 'PING_OK_SLOW', 
                                 "Host merespon tapi lambat (avg: {$avgTime}s)", 
                                 'Response lambat, optimasi jaringan atau server');
            } else {
                $this->saveResult($service, $oldStatus, 'UP', $code, 
                                 $avgTime > 0 ? $avgTime : $time, 
                                 'PING_OK', 
                                 "Host merespon ping (avg: {$avgTime}s)", 
                                 'Service dalam kondisi baik');
            }
            return;
        }

        $code = 'PING_FAILED';
        $service->update(['consecutive_failures' => 0]);
        $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                         'PING_FAILED', 'Host tidak merespon ping', 
                         'Periksa koneksi jaringan dan konfigurasi firewall');
    }

    /**
     * ============================================================
     * HANDLE TIMEOUT FAILURE
     * ============================================================
     */
    private function handleTimeoutFailure($service, $oldStatus, $time, $reason, $detail, $action)
    {
        $failures = ($service->consecutive_failures ?? 0) + 1;
        $service->update([
            'consecutive_failures' => $failures,
            'last_failure_at' => now()
        ]);
        
        Log::info("⏱️ TIMEOUT LAMBAT #{$failures} untuk {$service->name} (waktu: {$time}s)");
        
        if ($failures >= 2) {
            Log::info("🚨 TIMEOUT LAMBAT TERUS MENERUS! Kirim WA untuk {$service->name}");
            $this->saveResult($service, $oldStatus, 'DOWN', 'TIMEOUT_SLOW', $time, $reason, $detail, $action);
        } else {
            Log::info("⏳ Timeout lambat pertama - DIABAIKAN, status tetap {$oldStatus}");
            $this->handleIntervalLogic($service, $oldStatus, $oldStatus, 'TIMEOUT_SLOW_1', $time, 
                                       $reason . ' (ke-1 - diabaikan)', 
                                       $detail . ' - Timeout sesaat, diabaikan', 
                                       'Timeout akan diabaikan sampai terjadi 2x berturut-turut', false);
        }
    }

    /**
     * ============================================================
     * 📊 ANALISIS RESPONSE (TETAP)
     * ============================================================
     */
    private function analyzeResponse($code, $time)
    {
        if ($code >= 200 && $code < 300) {
            if ($time > 8) {
                return [
                    'status' => 'WARNING',
                    'reason' => 'SLOW_RESPONSE',
                    'detail' => "Response lambat ({$time}s)",
                    'action' => 'Optimasi performa server'
                ];
            }
            return [
                'status' => 'UP',
                'reason' => 'HTTP_' . $code,
                'detail' => 'Service berjalan normal',
                'action' => 'Service dalam kondisi baik'
            ];
        }

        if (in_array($code, [302, 303, 307])) {
            return [
                'status' => 'UP',
                'reason' => 'REDIRECT_' . $code,
                'detail' => "Redirect sementara - Pengguna masih bisa akses",
                'action' => 'Redirect sementara, tidak perlu tindakan'
            ];
        }

        if ($code >= 400 && $code < 500) {
            if ($code == 404) {
                return [
                    'status' => 'DOWN',
                    'reason' => 'HTTP_404',
                    'detail' => '404 Not Found - Halaman tidak ditemukan',
                    'action' => 'Periksa URL endpoint, kemungkinan sudah berubah'
                ];
            }
            
            $upCodes = [401, 403, 405, 429];
            if (in_array($code, $upCodes)) {
                return [
                    'status' => 'UP',
                    'reason' => 'HTTP_' . $code,
                    'detail' => $this->getClientErrorDetail($code),
                    'action' => $this->getClientErrorAction($code)
                ];
            }
            
            return [
                'status' => 'WARNING',
                'reason' => 'HTTP_' . $code,
                'detail' => "Client Error {$code} - Perlu perbaikan",
                'action' => 'Periksa request yang dikirim ke server'
            ];
        }

        if ($code >= 500 && $code < 600) {
            return [
                'status' => 'DOWN',
                'reason' => 'HTTP_' . $code,
                'detail' => $this->getServerErrorDetail($code),
                'action' => $this->getServerErrorAction($code)
            ];
        }

        return [
            'status' => 'DOWN',
            'reason' => 'HTTP_UNKNOWN',
            'detail' => "HTTP {$code} - Kode tidak dikenal",
            'action' => 'Periksa dokumentasi API'
        ];
    }

    private function getClientErrorDetail($code)
    {
        $details = [
            401 => 'Unauthorized - Perlu login',
            403 => 'Forbidden - Perlu izin akses',
            405 => 'Method HTTP tidak diizinkan',
            429 => 'Too Many Requests - Rate limit'
        ];
        return $details[$code] ?? "Client Error {$code}";
    }

    private function getClientErrorAction($code)
    {
        $actions = [
            401 => 'Pastikan kredensial login benar',
            403 => 'Periksa izin akses pengguna',
            405 => 'Ganti method HTTP yang digunakan',
            429 => 'Kurangi frekuensi request'
        ];
        return $actions[$code] ?? 'Periksa request ke server';
    }

    private function getServerErrorDetail($code)
    {
        $errors = [
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout'
        ];
        return ($errors[$code] ?? "Server Error {$code}") . ' - Pengguna tidak bisa akses';
    }

    private function getServerErrorAction($code)
    {
        $actions = [
            500 => 'Cek log server, periksa kode aplikasi yang error',
            502 => 'Periksa proxy / load balancer',
            503 => 'Cek maintenance server, atau scale up resource',
            504 => 'Optimasi response time server'
        ];
        return $actions[$code] ?? 'Periksa log server dan konfigurasi';
    }

    private function analyzeException($message)
    {
        $msg = strtolower($message);

        if (str_contains($msg, 'connection timeout') || str_contains($msg, 'timed out')) {
            return ['status' => 'DOWN', 'reason' => 'CONNECTION_TIMEOUT', 'detail' => 'Koneksi timeout', 'action' => 'Cek firewall, pastikan server menyala'];
        }

        if (str_contains($msg, 'connection refused')) {
            return ['status' => 'DOWN', 'reason' => 'CONNECTION_REFUSED', 'detail' => 'Koneksi ditolak', 'action' => 'Server mati atau firewall blocking'];
        }

        if (str_contains($msg, 'could not resolve') || str_contains($msg, 'dns')) {
            return ['status' => 'DOWN', 'reason' => 'DNS_ERROR', 'detail' => 'DNS tidak ditemukan', 'action' => 'Periksa konfigurasi DNS / domain'];
        }

        if (str_contains($msg, 'ssl') || str_contains($msg, 'certificate')) {
            return ['status' => 'WARNING', 'reason' => 'SSL_ERROR', 'detail' => 'SSL Error', 'action' => 'Periksa sertifikat SSL, mungkin sudah expired'];
        }

        return [
            'status' => 'DOWN',
            'reason' => 'UNKNOWN_ERROR',
            'detail' => 'Error tidak dikenal: ' . $message,
            'action' => 'Periksa service secara manual dan cek log error'
        ];
    }

    /**
     * ============================================================
     * 💾 SAVE RESULT
     * ============================================================
     */
    private function saveResult($service, $oldStatus, $status, $code, $time, $reason, $detail, $action)
    {
        if ($code === 'TIMEOUT_SLOW_1') {
            Log::info("⏭️ TIMEOUT_SLOW_1 - DIABAIKAN, status tetap {$oldStatus}");
            return;
        }

        if ($code === null || $code === '') {
            $code = 'N/A';
        }

        $statusChanged = ($oldStatus !== $status);
        $isFirstCheck = empty($oldStatus) || $oldStatus === 'UNKNOWN';
        
        $service->update([
            'last_status' => $status,
            'last_code' => $code,
            'last_response_time' => $time,
            'last_message' => $detail,
            'last_check_at' => now(),
        ]);

        if ($statusChanged || $isFirstCheck) {
            ServiceLog::create([
                'service_id' => $service->id,
                'status' => $status,
                'response_code' => $code,
                'response_time' => $time,
                'message' => $detail,
                'action' => $action,
                'checked_at' => now(),
                'is_status_change' => $statusChanged,
                'previous_status' => $oldStatus,
            ]);
            
            Log::info("📝 LOG BARU: {$service->name} {$oldStatus} → {$status}, Code: {$code}");
        } else {
            $lastLog = ServiceLog::where('service_id', $service->id)
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($lastLog) {
                $lastLog->update([
                    'response_time' => $time,
                    'response_code' => $code,
                    'message' => $detail,
                    'action' => $action,
                    'checked_at' => now(),
                ]);
                Log::info("🔄 LOG DIUPDATE: {$service->name} status tetap {$status}, Code: {$code}");
            }
        }

        $this->handleIntervalLogic($service, $oldStatus, $status, $code, $time, $reason, $detail, $action, $isFirstCheck);
    }

    /**
     * ============================================================
     * 🔄 HANDLE INTERVAL LOGIC
     * ============================================================
     */
    private function handleIntervalLogic($service, $oldStatus, $status, $code, $time, $reason, $detail, $action, $isFirstCheck = false)
    {
        if ($code === 'TIMEOUT_SLOW_1') {
            Log::info("⏭️ TIMEOUT_SLOW_1 - SKIP WA untuk {$service->name}");
            return;
        }
        
        $interval = $service->wa_interval_minutes ?? 0;
        
        Log::info("🔍 INTERVAL CHECK: {$service->name} | Interval: {$interval} menit | Status: {$status} | Old: {$oldStatus}");

        // FIRST CHECK: DOWN/WARNING → LANGSUNG KIRIM
        if ($isFirstCheck && ($status === 'DOWN' || $status === 'WARNING')) {
            Log::info("🚨 FIRST CHECK: Service baru dengan status {$status} - LANGSUNG KIRIM WA");
            $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
            $service->update([
                'last_wa_sent_at' => now(),
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 1,
            ]);
            return;
        }
        
        // FIRST CHECK: UP → TIDAK KIRIM
        if ($isFirstCheck && $status === 'UP') {
            Log::info("⏭️ FIRST CHECK: Service baru dengan status UP - TIDAK KIRIM WA");
            $service->update([
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 0,
                'last_wa_sent_at' => now(),
            ]);
            return;
        }

        // INTERVAL = 0 → KIRIM LANGSUNG
        if ($interval == 0) {
            Log::info("⏭️ Interval 0 - Kirim WA langsung saat status berubah");
            
            if ($oldStatus !== $status) {
                if ($status === 'UP') {
                    $this->sendRestoredAlert($service, $oldStatus, $code, $time, $detail);
                } else {
                    $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
                }
                $service->update(['last_wa_sent_at' => now()]);
                Log::info("✅ WA terkirim (interval 0): {$service->name} {$oldStatus} → {$status}");
            }
            
            $service->update([
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => ($status !== 'UP' ? 1 : 0),
            ]);
            return;
        }

        // INTERVAL > 0
        $lastIntervalCheck = $service->last_interval_checked_at;
        $lastIntervalStatus = $service->last_interval_status;
        $lastIntervalValue = $service->last_interval_value ?? 0;
        
        if ($lastIntervalValue != $interval) {
            Log::info("🔄 INTERVAL BERUBAH: {$lastIntervalValue} → {$interval} menit - RESET TIMER");
            $service->update([
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 0,
            ]);
            return;
        }
        
        if (empty($lastIntervalCheck) || empty($lastIntervalStatus)) {
            Log::info("🔄 INTERVAL INIT: {$service->name} | Status awal: {$status}");
            $service->update([
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 0,
            ]);
            return;
        }

        $lastCheck = Carbon::parse($lastIntervalCheck);
        $minutesSinceLastCheck = $lastCheck->diffInRealMinutes(now());
        
        Log::info("⏱️ TIMER: {$minutesSinceLastCheck}/{$interval} menit | Status awal: {$lastIntervalStatus} | Status skrg: {$status}");
        
        if ($minutesSinceLastCheck < $interval) {
            Log::info("⏳ Interval belum tercapai ({$minutesSinceLastCheck}/{$interval} menit) - TIDAK KIRIM WA");
            return;
        }

        Log::info("🎯 INTERVAL REACHED! {$service->name} | Awal: {$lastIntervalStatus} | Akhir: {$status}");
        
        if ($status !== $lastIntervalStatus) {
            Log::info("✅ STATUS BERUBAH: {$lastIntervalStatus} → {$status} (KIRIM WA)");
            
            if ($status === 'UP') {
                $this->sendRestoredAlert($service, $lastIntervalStatus, $code, $time, $detail);
            } else {
                $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
            }
            
            $service->update([
                'last_wa_sent_at' => now(),
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 1,
            ]);
            Log::info("✅ WA terkirim: {$service->name} {$lastIntervalStatus} → {$status}");
        } else {
            Log::info("⏭️ Status tetap {$status} - TIDAK KIRIM WA");
            
            $service->update([
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 0,
            ]);
            Log::info("⏱️ Timer direset untuk interval berikutnya");
        }
    }

    /**
     * ============================================================
     * 🟢 KIRIM WA SERVICE NORMAL KEMBALI (RESTORED)
     * ============================================================
     */
    private function sendRestoredAlert($service, $oldStatus, $code, $time, $detail)
    {
        $contacts = Contact::where('is_active', true)->get();
        if ($contacts->isEmpty()) {
            Log::warning('Tidak ada kontak aktif untuk kirim WA restored alert');
            return;
        }

        $newline = "\n";
        $line = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . $newline;
        
        $formattedTime = number_format($time, 2) . ' detik';
        $timeMs = $time * 1000;
        $formattedTimeMs = number_format($timeMs, 0) . ' ms';
        
        $statusText = $oldStatus == 'DOWN' ? 'DOWN' : 'WARNING';

        $message = "🟢✅ SERVICE NORMAL KEMBALI - SELAMAT!" . $newline;
        $message .= $line;
        $message .= "📌 INFORMASI SERVICE" . $newline;
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . $newline;
        $message .= "Nama    : " . $service->name . $newline;
        $message .= "Target  : " . $service->target . $newline;
        $message .= "Tipe    : " . strtoupper($service->type ?? 'HTTP') . $newline;
        $message .= $line;
        $message .= "📊 STATUS PEMULIHAN" . $newline;
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . $newline;
        $message .= "Status  : 🟢 UP (sebelumnya " . $statusText . ")" . $newline;
        $message .= "Kode    : " . $code . $newline;
        $message .= "Waktu   : " . $formattedTime . " (" . $formattedTimeMs . ")" . $newline;
        
        if ($timeMs <= 2000) {
            $message .= "Kategori: ⚡ Optimal (≤ 2 detik)" . $newline;
        } elseif ($timeMs <= 3000) {
            $message .= "Kategori: 🟡 Cukup cepat (2-3 detik)" . $newline;
        } elseif ($timeMs <= 4000) {
            $message .= "Kategori: 🟠 Lambat (3-4 detik) - perlu optimasi" . $newline;
        } else {
            $message .= "Kategori: 🔴 Sangat lambat (>4 detik) - perlu perbaikan" . $newline;
        }
        
        $message .= $line;
        $message .= "✅ Kondisi Service" . $newline;
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . $newline;
        $message .= "✅ Service telah kembali normal" . $newline;
        $message .= "✅ Dapat diakses oleh pengguna" . $newline;
        
        // Tambahkan saran jika masih lambat
        if ($timeMs > 2000) {
            $message .= $line;
            $message .= "⚠️ SARAN PERBAIKAN" . $newline;
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . $newline;
            $recs = $this->getRecommendationsByTime($timeMs);
            foreach (array_slice($recs, 0, 3) as $rec) {
                $message .= "• " . $rec . $newline;
            }
        }
        
        $message .= $line;
        $message .= "🕐 " . now()->setTimezone('Asia/Jakarta')->format('d-m-Y H:i:s') . " WIB" . $newline;
        $message .= $line;
        $message .= "📱 Service siap digunakan kembali!" . $newline;

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            Log::info($result ? "✅ WA RESTORED ke: {$contact->phone} - {$service->name}" : "❌ Gagal WA RESTORED ke: {$contact->phone}");
        }
    }

    /**
     * ============================================================
     * ⚠️ KIRIM WHATSAPP (DOWN / WARNING) - DIPERBAIKI LEBIH INFORMATIF
     * ============================================================
     */
    private function sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action)
    {
        $contacts = Contact::where('is_active', true)->get();
        if ($contacts->isEmpty()) {
            Log::warning('Tidak ada kontak aktif');
            return;
        }

        $newline = "\n";
        $line = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . $newline;

        // Format response time
        $formattedTime = number_format($time, 2) . ' detik';
        $timeMs = $time * 1000;
        $formattedTimeMs = number_format($timeMs, 0) . ' ms';

        if ($status == 'DOWN') {
            $judul = "🔴🚨 SERVICE DOWN - SEGERA PERBAIKI!";
            $statusIcon = "🔴";
            $statusText = "DOWN";
            $urgency = "🚨 URGENT!";
        } else {
            $judul = "🟡⚠️ SERVICE WARNING - PERLU OPTIMASI!";
            $statusIcon = "🟡";
            $statusText = "WARNING";
            $urgency = "⚠️ PERHATIAN!";
        }

        // Build pesan yang informatif
        $message = $judul . $newline;
        $message .= $line;
        $message .= "📌 INFORMASI SERVICE" . $newline;
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . $newline;
        $message .= "Nama    : " . $service->name . $newline;
        $message .= "Target  : " . $service->target . $newline;
        $message .= "Tipe    : " . strtoupper($service->type ?? 'HTTP') . $newline;
        $message .= $line;
        $message .= "📊 STATUS MONITORING" . $newline;
        $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . $newline;
        $message .= "Status  : " . $statusIcon . " " . $statusText . $newline;
        $message .= "Kode    : " . $code . $newline;
        $message .= "Waktu   : " . $formattedTime . " (" . $formattedTimeMs . ")" . $newline;
        $message .= "Threshold: ≤ 2 detik (standar PageSpeed)" . $newline;
        
        // Hitung selisih
        if ($timeMs > 2000) {
            $diff = $timeMs - 2000;
            $message .= "Selisih : +" . number_format($diff, 0) . " ms (melewati batas)" . $newline;
        } else {
            $diff = 2000 - $timeMs;
            $message .= "Selisih : -" . number_format($diff, 0) . " ms (dalam batas aman)" . $newline;
        }
        
        $message .= $line;
        
        // Dampak
        if ($status == 'DOWN') {
            $message .= "💥 DAMPAK BISNIS" . $newline;
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . $newline;
            $message .= "❌ Service tidak dapat diakses oleh pengguna" . $newline;
            $message .= "❌ Potensi kehilangan pendapatan" . $newline;
            $message .= "❌ Reputasi perusahaan terpengaruh" . $newline;
            $message .= "❌ SEO dan peringkat Google menurun" . $newline;
            $message .= $line;
            $message .= "🔧 TINDAKAN DARURAT" . $newline;
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . $newline;
        } else {
            $message .= "💥 DAMPAK" . $newline;
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . $newline;
            
            if ($timeMs <= 2500) {
                $message .= "🟡 Pengalaman pengguna mulai terganggu" . $newline;
                $message .= "🟡 Risiko bounce rate meningkat 10-15%" . $newline;
            } elseif ($timeMs <= 3000) {
                $message .= "🟠 Pengguna mulai tidak sabar" . $newline;
                $message .= "🟠 Risiko bounce rate meningkat 20-30%" . $newline;
                $message .= "🟠 Konversi menurun 10-15%" . $newline;
            } elseif ($timeMs <= 4000) {
                $message .= "🔴 Pengalaman pengguna sangat buruk" . $newline;
                $message .= "🔴 Risiko bounce rate meningkat 40-50%" . $newline;
                $message .= "🔴 Konversi menurun 20-30%" . $newline;
                $message .= "🔴 SEO terpengaruh signifikan" . $newline;
            } else {
                $message .= "🔴 SERVICE SANGAT LAMBAT!" . $newline;
                $message .= "🔴 Risiko bounce rate meningkat >60%" . $newline;
                $message .= "🔴 Konversi menurun >40%" . $newline;
                $message .= "🔴 SEO sangat terpengaruh" . $newline;
                $message .= "🔴 Hampir tidak bisa diakses" . $newline;
            }
            $message .= $line;
            $message .= "🔧 REKOMENDASI OPTIMASI" . $newline;
            $message .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" . $newline;
        }
        
        // Tambahkan rekomendasi spesifik
        $recommendations = $this->getRecommendationsByTime($timeMs);
        foreach ($recommendations as $rec) {
            $message .= "• " . $rec . $newline;
        }
        
        $message .= $line;
        
        // Detail tambahan dari system (ambil bagian penting)
        if (!empty($detail) && $detail != '-') {
            // Ambil hanya kalimat pertama dari detail
            $firstLine = strtok($detail, "\n");
            if ($firstLine) {
                $message .= "📝 " . $firstLine . $newline;
            }
        }
        
        $message .= $line;
        $message .= "🕐 " . now()->setTimezone('Asia/Jakarta')->format('d-m-Y H:i:s') . " WIB" . $newline;
        $message .= $line;
        $message .= "📱 " . $urgency . " Segera tindak lanjuti!" . $newline;

        // Kirim ke semua kontak
        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            Log::info($result ? "✅ WA ke: {$contact->phone} - {$status}" : "❌ Gagal WA ke: {$contact->phone}");
        }
    }

    /**
     * ============================================================
     * 🔧 HELPER METHODS
     * ============================================================
     */
    private function normalizeUrl($url)
    {
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            return 'https://' . $url;
        }
        return $url;
    }

    private function handleNetworkStatus($isNetworkConnected)
    {
        if (!$isNetworkConnected && !$this->networkAlertSent) {
            Log::info('🌐 Network: DISCONNECTED');
            $this->networkAlertSent = true;
        }
        if ($isNetworkConnected && $this->networkAlertSent) {
            Log::info('🌐 Network: RESTORED');
            $this->networkAlertSent = false;
        }
    }

    private function getStatusGroup($code)
    {
        if ($code === 'N/A' || $code === 'PING' || $code === 'PORT_OPEN') return 'CONNECTION';
        if ($code >= 200 && $code < 300) return 'SUCCESS';
        if ($code >= 300 && $code < 400) return 'REDIRECTION';
        if ($code >= 400 && $code < 500) return 'CLIENT_ERROR';
        if ($code >= 500 && $code < 600) return 'SERVER_ERROR';
        return 'UNKNOWN';
    }
}