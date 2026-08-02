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

    private function pingHost($host)
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $command = $isWindows 
            ? "ping -n 1 -w 3000 " . escapeshellarg($host) . " 2>&1"
            : "ping -c 1 -W 3 " . escapeshellarg($host) . " 2>&1";
        
        exec($command, $output, $resultCode);
        return $resultCode === 0;
    }

    /**
     * ============================================================
     * 🔍 CHECK HTTP
     * ============================================================
     */
    private function checkHttp(Service $service)
    {
        $oldStatus = $service->last_status ?? 'UNKNOWN';
        $code = null;
        $time = 0;
        $start = microtime(true);

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

            // 🔥 CEK REDIRECT PERMANEN (301, 308)
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

            $analysis = $this->analyzeResponseByCode($code, $response->body(), $time);
            Log::info("Analysis {$service->name}: " . json_encode($analysis));

            if ($analysis['status'] === 'UP') {
                $service->update(['consecutive_failures' => 0]);
            }

        } catch (ConnectionException $e) {
            $time = round(microtime(true) - $start, 2);
            $code = 'TIMEOUT';
            
            Log::error("Connection timeout {$service->name}: " . $e->getMessage());
            
            if ($time <= self::TIMEOUT_FAST) {
                // ⚡ TIMEOUT CEPAT (≤ 5 detik) → LANGSUNG DOWN + KIRIM WA
                Log::info("⚡ TIMEOUT CEPAT ({$time}s) untuk {$service->name} - LANGSUNG DOWN");
                $service->update(['consecutive_failures' => 0]);
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                                 'CONNECTION_TIMEOUT_FAST', 
                                 "Koneksi timeout cepat ({$time}s) - Server tidak merespon", 
                                 'Server kemungkinan mati, periksa segera');
            } else {
                // 🐌 TIMEOUT LAMBAT (> 5 detik) → pakai consecutive failures
                Log::info("🐌 TIMEOUT LAMBAT ({$time}s) untuk {$service->name} - pakai consecutive");
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

        $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time, 
                         $analysis['reason'], $analysis['detail'], $analysis['action']);
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
     * 📡 CHECK PING
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

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $command = $isWindows 
            ? "ping -n 2 -w 10000 " . escapeshellarg($host) . " 2>&1"
            : "ping -c 2 -W 10 " . escapeshellarg($host) . " 2>&1";
        
        exec($command, $output, $resultCode);
        $outputString = implode("\n", $output);
        $time = round(microtime(true) - $start, 2);

        Log::info("Ping result for {$host}:", [
            'resultCode' => $resultCode,
            'time' => $time . 's',
            'output' => $outputString
        ]);

        // UNREACHABLE
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

        // TIMEOUT
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
                // ⚡ TIMEOUT CEPAT (≤ 5 detik) → LANGSUNG DOWN + KIRIM WA
                Log::info("⚡ PING TIMEOUT CEPAT ({$time}s) untuk {$host} - LANGSUNG DOWN");
                $service->update(['consecutive_failures' => 0]);
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time, 
                                 'PING_TIMEOUT_FAST', 
                                 "Ping timeout cepat ({$time}s) - Host tidak merespon", 
                                 'Server kemungkinan mati, periksa segera');
            } else {
                // 🐌 TIMEOUT LAMBAT (> 5 detik) → pakai consecutive failures
                Log::info("🐌 PING TIMEOUT LAMBAT ({$time}s) untuk {$host} - pakai consecutive");
                $this->handleTimeoutFailure($service, $oldStatus, $time, 
                                           'PING_TIMEOUT_SLOW', 
                                           "Ping timeout lambat ({$time}s) - Host lambat merespon", 
                                           'Periksa performa server dan koneksi jaringan');
            }
            return;
        }

        // PING OK
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
     * 🔥 HANDLE TIMEOUT FAILURE - UNTUK TIMEOUT > 5 DETIK SAJA
     * 🔥 TIMEOUT_SLOW_1: DIABAIKAN (TETAP STATUS LAMA)
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
            // ✅ TIMEOUT KE-2+ → DOWN + KIRIM WA + BUAT LOG
            Log::info("🚨 TIMEOUT LAMBAT TERUS MENERUS! Kirim WA untuk {$service->name}");
            
            $this->sendWhatsappAlert($service, 'DOWN', 'TIMEOUT_SLOW', $time, $reason, $detail, $action);
            $service->update(['last_wa_sent_at' => now()]);
            
            // 🔥 BUAT LOG (via saveResult)
            $this->saveResult($service, $oldStatus, 'DOWN', 'TIMEOUT_SLOW', $time, $reason, $detail, $action);
        } else {
            // ❌ TIMEOUT KE-1 → DIABAIKAN (TETAP STATUS LAMA)
            Log::info("⏳ Timeout lambat pertama - DIABAIKAN, status tetap {$oldStatus}");
            
            // 🔥 STATUS TETAP SAMA (TIDAK BERUBAH KE WARNING)
            // HANYA UPDATE consecutive_failures (sudah di update di atas)
            // TIDAK UPDATE last_status, TIDAK BUAT LOG, TIDAK KIRIM WA
            
            // 🔥 UPDATE INTERVAL LOGIC TANPA PERUBAHAN STATUS
            $this->handleIntervalLogic($service, $oldStatus, $oldStatus, 'TIMEOUT_SLOW_1', $time, 
                                       $reason . ' (ke-1 - diabaikan)', 
                                       $detail . ' - Timeout sesaat, diabaikan', 
                                       'Timeout akan diabaikan sampai terjadi 2x berturut-turut', false);
        }
    }

    /**
     * ============================================================
     * 📊 ANALISIS RESPONSE
     * ============================================================
     */
    private function analyzeResponseByCode($code, $body, $time)
    {
        if (empty($body) || trim($body) === '') {
            Log::warning("Response kosong: code={$code}, service body empty");
            
            if ($code >= 200 && $code < 300) {
                return [
                    'status' => 'WARNING',
                    'reason' => 'EMPTY_RESPONSE',
                    'detail' => 'Halaman merespon tapi konten kosong',
                    'action' => 'Periksa apakah halaman memang kosong atau ada error'
                ];
            }
            
            return [
                'status' => 'DOWN',
                'reason' => 'EMPTY_RESPONSE_ERROR',
                'detail' => "Server error ({$code}) dengan response kosong",
                'action' => 'Cek log server, periksa error di aplikasi'
            ];
        }

        $errorKeywords = ['fatal error', 'parse error', 'syntax error', 'exception', 'stack trace', 'database error'];
        $bodyLower = strtolower($body);
        
        foreach ($errorKeywords as $keyword) {
            if (str_contains($bodyLower, $keyword)) {
                Log::warning("Konten mengandung error: '{$keyword}'");
                return [
                    'status' => 'WARNING',
                    'reason' => 'ERROR_IN_CONTENT',
                    'detail' => "Konten error: '{$keyword}' - Service masih berjalan tapi ada error",
                    'action' => 'Periksa log server dan perbaiki error aplikasi'
                ];
            }
        }

        return $this->analyzeResponse($code, $time);
    }

    /**
     * ============================================================
     * 📊 ANALISIS RESPONSE - DIPERBAIKI UNTUK 404
     * ============================================================
     */
    private function analyzeResponse($code, $time)
    {
        // 2xx SUCCESS
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

        // 3xx REDIRECT (302, 303, 307 - redirect sementara)
        if (in_array($code, [302, 303, 307])) {
            return [
                'status' => 'UP',
                'reason' => 'REDIRECT_' . $code,
                'detail' => "Redirect sementara - Pengguna masih bisa akses",
                'action' => 'Redirect sementara, tidak perlu tindakan'
            ];
        }

        // 4xx CLIENT ERROR
        if ($code >= 400 && $code < 500) {
            // 🔥 404 NOT FOUND - DOWN
            if ($code == 404) {
                return [
                    'status' => 'DOWN',
                    'reason' => 'HTTP_404',
                    'detail' => '404 Not Found - Halaman tidak ditemukan',
                    'action' => 'Periksa URL endpoint, kemungkinan sudah berubah'
                ];
            }
            
            // 401, 403, 405, 429 - UP
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

        // 5xx SERVER ERROR
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
     * 💾 SAVE RESULT - TIMEOUT_SLOW_1 DIABAIKAN
     * ============================================================
     */
    private function saveResult($service, $oldStatus, $status, $code, $time, $reason, $detail, $action)
    {
        // 🔥 JIKA TIMEOUT_SLOW_1, DIABAIKAN (TIDAK BUAT LOG)
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

        // 🔥 SIMPAN LOG HANYA JIKA STATUS BERUBAH ATAU FIRST CHECK
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
            // UPDATE LOG TERAKHIR
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
        // 🔥 JIKA TIMEOUT_SLOW_1, JANGAN KIRIM WA
        if ($code === 'TIMEOUT_SLOW_1') {
            Log::info("⏭️ TIMEOUT_SLOW_1 - SKIP WA untuk {$service->name}");
            
            $service->update([
                'last_interval_status' => $oldStatus, // Tetap pakai status lama
                'last_interval_checked_at' => now(),
                'last_interval_value' => $service->wa_interval_minutes ?? 0,
                'interval_wa_sent_in_this_cycle' => 0,
            ]);
            return;
        }
        
        $interval = $service->wa_interval_minutes ?? 0;
        
        Log::info("🔍 INTERVAL CHECK: {$service->name} | Interval: {$interval} menit | Status: {$status} | Old: {$oldStatus}");

        if ($isFirstCheck && ($status === 'DOWN' || $status === 'WARNING')) {
            Log::info("🚨 FIRST CHECK: Service baru dengan status {$status} - LANGSUNG KIRIM WA");
            $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
            $service->update(['last_wa_sent_at' => now()]);
            
            $service->update([
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 1,
            ]);
            return;
        }
        
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
            return;
        }
        
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
        $statusText = $oldStatus == 'DOWN' ? 'DOWN' : 'WARNING';

        $message = "🟢 SERVICE NORMAL KEMBALI" . $newline . $newline;
        $message .= "Nama   : " . $service->name . $newline;
        $message .= "Target : " . $service->target . $newline;
        $message .= $newline;
        $message .= "Status : 🟢 UP (sebelumnya " . $statusText . ")" . $newline;
        $message .= "Kode   : " . $code . $newline;
        $message .= "Waktu  : " . $time . " detik" . $newline;
        
        if (!empty($detail) && $detail != '-') {
            $message .= $newline . "Detail:" . $newline;
            $message .= $detail . $newline;
        }
        
        $message .= $newline . "✅ Service telah kembali normal dan dapat diakses." . $newline;
        $message .= $newline . "🕐 " . now()->format('d-m-Y H:i:s') . " WIB";

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            Log::info($result ? "✅ WA RESTORED ke: {$contact->phone} - {$service->name}" : "❌ Gagal WA RESTORED ke: {$contact->phone}");
        }
    }

    /**
     * ============================================================
     * ⚠️ KIRIM WHATSAPP (DOWN / WARNING)
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

        if ($status == 'DOWN') {
            $judul = "🔴 SERVICE DOWN";
            $statusIcon = "🔴";
            $statusText = "DOWN";
        } else {
            $judul = "🟡 SERVICE WARNING";
            $statusIcon = "🟡";
            $statusText = "WARNING";
        }

        $message = $judul . $newline . $newline;
        $message .= "Nama   : " . $service->name . $newline;
        $message .= "Target : " . $service->target . $newline;
        $message .= $newline;
        $message .= "Status : " . $statusIcon . " " . $statusText . $newline;
        $message .= "Kode   : " . $code . $newline;
        $message .= "Waktu  : " . $time . " detik" . $newline;
        
        if (!empty($detail) && $detail != '-') {
            $message .= $newline . "Detail:" . $newline;
            $message .= $detail . $newline;
        }
        
        if (!empty($action) && $action != '-' && $action != 'Service dalam kondisi baik, tidak perlu tindakan') {
            $message .= $newline . "Tindakan:" . $newline;
            $message .= $action . $newline;
        }
        
        $message .= $newline . "🕐 " . now()->format('d-m-Y H:i:s') . " WIB";

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