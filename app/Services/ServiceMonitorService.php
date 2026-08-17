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

    private const TIMEOUT_FAST = 5;
    private const TIMEOUT_SLOW = 10;
    private const SSL_WARNING_DAYS = 30;
    private const SSL_CRITICAL_DAYS = 7;

    /**
     * Check single service
     */
    public function check(Service $service)
    {
        if ($service->type === 'ping') {
            return $this->checkPing($service);
        }
        return $this->checkHttp($service);
    }

    /**
     * Get SSL information for a service
     */
    public function getSSLInfo(Service $service)
    {
        $url = $this->normalizeUrl($service->target);
        $parsedUrl = parse_url($url);

        if (($parsedUrl['scheme'] ?? '') !== 'https') {
            return null;
        }

        $host = $parsedUrl['host'] ?? '';
        $port = $parsedUrl['port'] ?? 443;

        if (empty($host)) {
            return null;
        }

        return $this->checkSSL($service, $host, $port);
    }

    /**
     * Get SSL details for display
     */
    public function getSSLDetails(Service $service)
    {
        $sslInfo = $this->getSSLInfo($service);

        if (!$sslInfo) {
            return [
                'available' => false,
                'message' => 'SSL tidak tersedia untuk service ini (bukan HTTPS)',
                'status' => 'N/A'
            ];
        }

        $statusMap = [
            'EXPIRED' => ['icon' => '🔴', 'label' => 'EXPIRED'],
            'CRITICAL' => ['icon' => '🔴', 'label' => 'CRITICAL'],
            'WARNING' => ['icon' => '🟡', 'label' => 'WARNING'],
            'VALID' => ['icon' => '🟢', 'label' => 'VALID']
        ];

        $status = $sslInfo['status'] ?? 'UNKNOWN';
        $statusData = $statusMap[$status] ?? ['icon' => '❓', 'label' => 'UNKNOWN'];

        return [
            'available' => true,
            'status' => $status,
            'status_icon' => $statusData['icon'],
            'status_label' => $statusData['label'],
            'subject' => $sslInfo['subject'] ?? 'Unknown',
            'issuer' => $sslInfo['issuer'] ?? 'Unknown',
            'organization' => $sslInfo['organization'] ?? '',
            'valid_from' => $sslInfo['valid_from'] ?? 'Unknown',
            'valid_to' => $sslInfo['valid_to'] ?? 'Unknown',
            'days_remaining' => $sslInfo['days_remaining'] ?? 0,
            'is_down' => $sslInfo['is_down'] ?? false,
            'message' => $sslInfo['message'] ?? 'Tidak ada informasi',
            'action' => $sslInfo['action'] ?? 'Periksa SSL certificate',
        ];
    }

    /**
     * Format SSL info for display
     */
    public function formatSSLInfo(Service $service)
    {
        $details = $this->getSSLDetails($service);

        if (!$details['available']) {
            return "🔓 SSL: Tidak tersedia (non-HTTPS)";
        }

        $output = "";
        $output .= "🔒 SSL INFORMATION\n";
        $output .= "Status  : " . $details['status_icon'] . " " . $details['status_label'] . "\n";
        $output .= "Issuer  : " . $details['issuer'] . "\n";
        $output .= "Subject : " . $details['subject'] . "\n";

        if (!empty($details['organization'])) {
            $output .= "Org     : " . $details['organization'] . "\n";
        }

        $output .= "Valid   : " . $details['valid_from'] . " → " . $details['valid_to'] . "\n";
        $output .= "Sisa    : " . $details['days_remaining'] . " hari\n";

        if ($details['is_down']) {
            $output .= "⚠️ SSL EXPIRED! Service DOWN!\n";
        } elseif ($details['days_remaining'] <= 7) {
            $output .= "⚠️ SSL akan expired dalam " . $details['days_remaining'] . " hari!\n";
        }

        return $output;
    }

    /**
     * Send SSL status report via WhatsApp
     */
    public function sendSSLReport($phone = null)
    {
        if ($phone) {
            $contacts = Contact::where('phone', $phone)->where('is_active', true)->get();
        } else {
            $contacts = Contact::where('is_active', true)->get();
        }

        if ($contacts->isEmpty()) {
            Log::warning('Tidak ada kontak aktif untuk SSL Report');
            return false;
        }

        $services = Service::where('type', 'http')->get();

        $message = "📋 LAPORAN SSL\n";
        $message .= "═══════════════════════\n";

        $valid = 0;
        $warning = 0;
        $critical = 0;
        $expired = 0;

        foreach ($services as $service) {
            $ssl = $this->getSSLDetails($service);
            $status = $ssl['available'] ? $ssl['status'] : 'N/A';

            if ($status === 'VALID') $valid++;
            elseif ($status === 'WARNING') $warning++;
            elseif ($status === 'CRITICAL') $critical++;
            elseif ($status === 'EXPIRED') $expired++;

            $days = $ssl['available'] ? $ssl['days_remaining'] : '-';
            $icon = $ssl['available'] ? $ssl['status_icon'] : '🔓';
            $message .= $icon . " " . $service->name . " : " . $status . " (" . $days . " hr)\n";
        }

        $message .= "═══════════════════════\n";
        $message .= "✅ Valid: " . $valid . " | ⚠️ Warning: " . $warning . "\n";
        $message .= "🔴 Critical: " . $critical . " | 🔴 Expired: " . $expired . "\n";
        $message .= "═══════════════════════\n";
        $message .= "🕐 " . now()->setTimezone('Asia/Jakarta')->format('d-m-Y H:i:s') . " WIB";

        $success = false;
        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            if ($result) $success = true;
            Log::info($result ? "✅ SSL Report WA ke: {$contact->phone}" : "❌ Gagal SSL Report WA ke: {$contact->phone}");
        }

        return $success;
    }

    // ============================================================
    // PAGESPEED STATUS
    // ============================================================

    private function determineStatusByPageSpeed(string $metric, float $value): string
    {
        $thresholds = config('pagespeed.thresholds', []);

        $metricThreshold = $thresholds[$metric] ?? $thresholds['response_time'] ?? null;

        if (!$metricThreshold) {
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

    private function getShortRecommendation($timeMs): string
    {
        if ($timeMs <= 2000) return '✅ Performa optimal';
        if ($timeMs <= 2500) return '📌 Cukup cepat, pertimbangkan caching';
        if ($timeMs <= 3000) return '⚠️ Mulai lambat, optimasi gambar & cache';
        if ($timeMs <= 4000) return '🚨 Sangat lambat, upgrade server';
        if ($timeMs <= 6000) return '🔴 KRITIS! Cek server & scale up';
        return '🔴 DARURAT! Service tidak responsif!';
    }

    private function analyzeResponseWithPageSpeed($code, $time): array
    {
        $timeMs = $time * 1000;
        $status = $this->determineStatusByPageSpeed('response_time', $timeMs);
        $baseStatus = $this->analyzeResponse($code, $time);

        if ($baseStatus['status'] === 'DOWN') {
            return $baseStatus;
        }

        $formattedTime = number_format($time, 2) . 's';
        $shortRec = $this->getShortRecommendation($timeMs);

        if ($status === 'WARNING') {
            return [
                'status' => 'WARNING',
                'reason' => 'PAGESPEED_SLOW',
                'detail' => "Response {$formattedTime} melewati threshold",
                'action' => $shortRec,
                'metrics' => [
                    'response_time_ms' => $timeMs,
                    'response_time_sec' => $time,
                ]
            ];
        }

        if ($status === 'DOWN') {
            return [
                'status' => 'DOWN',
                'reason' => 'PAGESPEED_DOWN',
                'detail' => "Response {$formattedTime} - SERVICE DOWN!",
                'action' => '🔴 DARURAT! ' . $shortRec,
                'metrics' => [
                    'response_time_ms' => $timeMs,
                    'response_time_sec' => $time,
                ]
            ];
        }

        return [
            'status' => 'UP',
            'reason' => 'PAGESPEED_GOOD',
            'detail' => "Response {$formattedTime} dalam batas aman",
            'action' => '✅ Service dalam kondisi baik',
            'metrics' => [
                'response_time_ms' => $timeMs,
                'response_time_sec' => $time,
            ]
        ];
    }

    // ============================================================
    // NETWORK CHECK
    // ============================================================

    public function checkNetworkConnection()
    {
        $cacheKey = 'network_connection_status';
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $httpTargets = ['https://www.google.com', 'https://www.cloudflare.com'];

        foreach ($httpTargets as $target) {
            try {
                $response = Http::timeout(5)->get($target);
                if ($response->successful()) {
                    Cache::put($cacheKey, true, 60);
                    return true;
                }
            } catch (\Exception $e) {
                // Skip
            }
        }

        $pingTargets = ['8.8.8.8', '1.1.1.1'];
        foreach ($pingTargets as $target) {
            if ($this->pingHost($target)) {
                Cache::put($cacheKey, true, 60);
                return true;
            }
        }

        $dnsTargets = ['google.com', 'cloudflare.com'];
        foreach ($dnsTargets as $target) {
            if (checkdnsrr($target, 'A')) {
                Cache::put($cacheKey, true, 60);
                return true;
            }
        }

        Cache::put($cacheKey, false, 60);
        return false;
    }

    private function pingHost($host)
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($isWindows) {
            $pingPath = 'C:\\Windows\\System32\\ping.exe';
            if (!file_exists($pingPath)) {
                $pingPath = 'ping';
            }
            $command = $pingPath . " -n 1 -w 3000 " . escapeshellarg($host) . " 2>&1";
        } else {
            $command = "ping -c 1 -W 3 " . escapeshellarg($host) . " 2>&1";
        }

        exec($command, $output, $resultCode);
        return $resultCode === 0;
    }

    // ============================================================
    // SSL CHECK
    // ============================================================

    private function checkSSL($service, $host, $port = 443)
    {
        try {
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $opensslCmd = 'openssl';

            if ($isWindows) {
                $checkOpenssl = shell_exec('where openssl 2>nul');
                if (empty($checkOpenssl)) {
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

                    return $this->processSSLResult($service, $host, $validFrom, $validTo, $daysRemaining, $commonName, $organization, $issuerCN);
                }
            }

            return $this->checkSSLviaStream($service, $host, $port);

        } catch (\Exception $e) {
            try {
                return $this->checkSSLviaStream($service, $host, $port);
            } catch (\Exception $e2) {
                return null;
            }
        }
    }

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
                return null;
            }

            $params = stream_context_get_params($client);
            $cert = $params['options']['ssl']['peer_certificate'] ?? null;

            if (!$cert) {
                fclose($client);
                return null;
            }

            $certInfo = openssl_x509_parse($cert);

            if (!$certInfo) {
                fclose($client);
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

            return $this->processSSLResult($service, $host, $validFrom, $validTo, $daysRemaining, $commonName, $organization, $issuerCN);

        } catch (\Exception $e) {
            return null;
        }
    }

    private function processSSLResult($service, $host, $validFrom, $validTo, $daysRemaining, $commonName, $organization, $issuerCN)
    {
        $service->refresh();

        if ($daysRemaining <= 0) {
            $status = 'EXPIRED';
            $isDown = true;
            $message = "🔴 SSL EXPIRED! Exp: {$validTo->format('d-m-Y')}";
            $action = '🚨 SEGERA PERBARUI SSL!';
            $sendAlert = is_null($service->ssl_expired_sent_at);

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

        if ($daysRemaining <= self::SSL_CRITICAL_DAYS) {
            $status = 'CRITICAL';
            $isDown = false;
            $message = "🔴 SSL expired dalam {$daysRemaining} hari! (Exp: {$validTo->format('d-m-Y')})";
            $action = '⚠️ SEGERA perpanjang SSL!';
            $sendAlert = is_null($service->ssl_critical_sent_at);

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

        if ($daysRemaining <= self::SSL_WARNING_DAYS) {
            $status = 'WARNING';
            $isDown = false;
            $message = "🟡 SSL expired dalam {$daysRemaining} hari (Exp: {$validTo->format('d-m-Y')})";
            $action = '📌 Rencanakan perpanjangan SSL';
            $sendAlert = is_null($service->ssl_warning_sent_at);

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

    private function saveSSLResult($service, $sslData)
    {
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

        if ($sslData['status'] === 'WARNING' && $sslData['send_alert'] === true) {
            $service->update(['ssl_warning_sent_at' => now()]);
        }

        if ($sslData['status'] === 'CRITICAL' && $sslData['send_alert'] === true) {
            $service->update(['ssl_critical_sent_at' => now()]);
        }

        if ($sslData['status'] === 'EXPIRED' && $sslData['send_alert'] === true) {
            $service->update(['ssl_expired_sent_at' => now()]);
        }

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
        }
    }

    private function sendSSLAlert($service, $sslResult)
    {
        $contacts = Contact::where('is_active', true)->get();
        if ($contacts->isEmpty()) {
            return;
        }

        if ($sslResult['is_down']) {
            $judul = "🔴 SSL EXPIRED!";
            $icon = "🔴";
            $statusText = "EXPIRED";
            $urgency = "🚨 SEGERA PERBAIKI SSL!";
        } elseif ($sslResult['status'] === 'CRITICAL') {
            $judul = "🔴 SSL CRITICAL!";
            $icon = "🔴";
            $statusText = "CRITICAL";
            $urgency = "⚠️ SEGERA PERPANJANG! (" . $sslResult['days_remaining'] . " hari)";
        } else {
            $judul = "🟡 SSL WARNING";
            $icon = "🟡";
            $statusText = "WARNING";
            $urgency = "📌 Rencanakan perpanjangan SSL";
        }

        $message = $judul . "\n";
        $message .= "═══════════════════════\n";
        $message .= "Nama: " . $service->name . "\n";
        $message .= "Domain: " . $service->target . "\n";
        $message .= "Status: " . $icon . " " . $statusText . "\n";
        $message .= "Sisa: " . $sslResult['days_remaining'] . " hari\n";
        $message .= "Expired: " . ($sslResult['valid_to'] ?? 'Unknown') . "\n";
        $message .= "═══════════════════════\n";
        $message .= $urgency . "\n";
        $message .= "🕐 " . now()->format('d-m-Y H:i:s') . " WIB";

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            Log::info($result ? "✅ SSL WA ke: {$contact->phone}" : "❌ Gagal SSL WA ke: {$contact->phone}");
        }
    }

    // ============================================================
    // CHECK HTTP
    // ============================================================

    private function checkHttp(Service $service)
    {
        $oldStatus = $service->last_status ?? 'UNKNOWN';
        $code = null;
        $time = 0;
        $start = microtime(true);
        $analysis = null;

        $url = $this->normalizeUrl($service->target);
        $sslResult = null;
        $parsedUrl = parse_url($url);

        if ($parsedUrl && ($parsedUrl['scheme'] ?? '') === 'https') {
            $host = $parsedUrl['host'] ?? '';
            $port = $parsedUrl['port'] ?? 443;

            if (!empty($host)) {
                $sslResult = $this->checkSSL($service, $host, $port);

                if ($sslResult && $sslResult['is_down'] === true) {
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
                    $this->handleSSLInterval($service, $sslResult);
                    return;
                }

                if ($sslResult && ($sslResult['status'] === 'WARNING' || $sslResult['status'] === 'CRITICAL')) {
                    $service->update([
                        'last_status' => 'WARNING',
                        'last_message' => $sslResult['message'],
                        'last_check_at' => now(),
                    ]);
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

            if (in_array($code, [301, 308])) {
                $location = $response->header('Location');
                $redirectResult = $this->checkRedirectTarget($location, $service);

                if ($redirectResult['status'] === 'UP') {
                    $analysis = [
                        'status' => 'UP',
                        'reason' => 'REDIRECT_' . $code,
                        'detail' => "Redirect permanen ke: {$location}",
                        'action' => "Update URL endpoint ke: {$location}"
                    ];
                } else {
                    $analysis = [
                        'status' => 'DOWN',
                        'reason' => 'REDIRECT_' . $code . '_FAILED',
                        'detail' => "Redirect ke: {$location} - Target {$redirectResult['status']}",
                        'action' => "Periksa target redirect: {$location}"
                    ];
                }

                $this->saveResult($service, $oldStatus, $analysis['status'], $code, $time,
                    $analysis['reason'], $analysis['detail'], $analysis['action']);
                return;
            }

            $analysis = $this->analyzeResponseWithPageSpeed($code, $time);

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

                if (empty($body) || trim($body) === '') {
                    $analysis = [
                        'status' => 'WARNING',
                        'reason' => 'PAGESPEED_EMPTY_RESPONSE',
                        'detail' => 'Response cepat tapi konten kosong',
                        'action' => 'Periksa apakah halaman memang kosong atau ada error',
                    ];
                }
            }

            if ($analysis['status'] === 'UP') {
                $service->update(['consecutive_failures' => 0]);
            }

        } catch (ConnectionException $e) {
            $time = round(microtime(true) - $start, 2);
            $code = 'TIMEOUT';

            if ($time <= self::TIMEOUT_FAST) {
                $service->update(['consecutive_failures' => 0]);
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time,
                    'CONNECTION_TIMEOUT_FAST',
                    "Koneksi timeout cepat ({$time}s)",
                    'Server kemungkinan mati, periksa segera');
            } else {
                $this->handleTimeoutFailure($service, $oldStatus, $time,
                    'CONNECTION_TIMEOUT_SLOW',
                    "Koneksi timeout lambat ({$time}s)",
                    'Periksa performa server dan koneksi jaringan');
            }
            return;

        } catch (\Exception $e) {
            $time = 0;
            $code = 'ERROR';
            $analysis = $this->analyzeException($e->getMessage());

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

    private function handleSSLInterval($service, $sslResult)
    {
        $interval = $service->wa_interval_minutes ?? 0;
        $status = $sslResult['status'];

        $shouldSendWa = false;

        if ($sslResult['send_alert'] === true) {
            $lastIntervalCheck = $service->last_interval_checked_at;
            $lastIntervalStatus = $service->last_interval_status;

            if ($interval == 0) {
                $shouldSendWa = true;
            } elseif (empty($lastIntervalCheck) || $lastIntervalStatus !== $status) {
                $shouldSendWa = true;
            }
        }

        if ($shouldSendWa) {
            $this->sendSSLAlert($service, $sslResult);
            $service->update([
                'last_wa_sent_at' => now(),
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 1,
            ]);
        } else {
            $service->update([
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 0,
            ]);
        }

        $service->refresh();
    }

    private function checkRedirectTarget($url, $service)
    {
        if (empty($url) || $url === '-' || $url === '') {
            return [
                'status' => 'DOWN',
                'message' => "Redirect target kosong"
            ];
        }

        try {
            $response = Http::timeout(30)
                ->connectTimeout(20)
                ->withoutRedirecting()
                ->get($url);

            $code = $response->status();

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
                'message' => "Target redirect timeout"
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'DOWN',
                'message' => "Target redirect error"
            ];
        }
    }

    // ============================================================
    // CHECK PING
    // ============================================================

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
                    'PORT_CLOSED', "Port {$port} tidak merespon", 'Periksa firewall dan pastikan service berjalan');
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

        if ($isWindows) {
            $pingPath = 'C:\\Windows\\System32\\ping.exe';
            if (!file_exists($pingPath)) {
                $pingPath = 'ping';
            }
            $command = $pingPath . " -n 2 -w 10000 " . escapeshellarg($host) . " 2>&1";
        } else {
            $command = "ping -c 2 -W 10 " . escapeshellarg($host) . " 2>&1";
        }

        exec($command, $output, $resultCode);
        $outputString = implode("\n", $output);
        $time = round(microtime(true) - $start, 2);

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
                $service->update(['consecutive_failures' => 0]);
                $this->saveResult($service, $oldStatus, 'DOWN', $code, $time,
                    'PING_TIMEOUT_FAST',
                    "Ping timeout cepat ({$time}s) - Host tidak merespon",
                    'Server kemungkinan mati, periksa segera');
            } else {
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

    // ============================================================
    // HELPERS
    // ============================================================

    private function handleTimeoutFailure($service, $oldStatus, $time, $reason, $detail, $action)
    {
        $failures = ($service->consecutive_failures ?? 0) + 1;
        $service->update([
            'consecutive_failures' => $failures,
            'last_failure_at' => now()
        ]);

        if ($failures >= 2) {
            $this->saveResult($service, $oldStatus, 'DOWN', 'TIMEOUT_SLOW', $time, $reason, $detail, $action);
        } else {
            $this->handleIntervalLogic($service, $oldStatus, $oldStatus, 'TIMEOUT_SLOW_1', $time,
                $reason . ' (ke-1 - diabaikan)',
                $detail . ' - Timeout sesaat, diabaikan',
                'Timeout akan diabaikan sampai terjadi 2x berturut-turut', false);
        }
    }

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
                'detail' => "Redirect sementara",
                'action' => 'Redirect sementara, tidak perlu tindakan'
            ];
        }

        if ($code >= 400 && $code < 500) {
            if ($code == 404) {
                return [
                    'status' => 'DOWN',
                    'reason' => 'HTTP_404',
                    'detail' => '404 Not Found',
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
                'detail' => "Client Error {$code}",
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

    private function saveResult($service, $oldStatus, $status, $code, $time, $reason, $detail, $action)
    {
        if ($code === 'TIMEOUT_SLOW_1') {
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
            }
        }

        $this->handleIntervalLogic($service, $oldStatus, $status, $code, $time, $reason, $detail, $action, $isFirstCheck);
    }

    private function handleIntervalLogic($service, $oldStatus, $status, $code, $time, $reason, $detail, $action, $isFirstCheck = false)
    {
        if ($code === 'TIMEOUT_SLOW_1') {
            return;
        }

        $interval = $service->wa_interval_minutes ?? 0;

        if ($isFirstCheck && ($status === 'DOWN' || $status === 'WARNING')) {
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

        if ($isFirstCheck && $status === 'UP') {
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
            if ($oldStatus !== $status) {
                if ($status === 'UP') {
                    $this->sendRestoredAlert($service, $oldStatus, $code, $time, $detail);
                } else {
                    $this->sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action);
                }
                $service->update(['last_wa_sent_at' => now()]);
            }

            $service->update([
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => ($status !== 'UP' ? 1 : 0),
            ]);
            return;
        }

        $lastIntervalCheck = $service->last_interval_checked_at;
        $lastIntervalStatus = $service->last_interval_status;
        $lastIntervalValue = $service->last_interval_value ?? 0;

        if ($lastIntervalValue != $interval) {
            $service->update([
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 0,
            ]);
            return;
        }

        if (empty($lastIntervalCheck) || empty($lastIntervalStatus)) {
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

        if ($minutesSinceLastCheck < $interval) {
            return;
        }

        if ($status !== $lastIntervalStatus) {
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
        } else {
            $service->update([
                'last_interval_status' => $status,
                'last_interval_checked_at' => now(),
                'last_interval_value' => $interval,
                'interval_wa_sent_in_this_cycle' => 0,
            ]);
        }
    }

    private function sendRestoredAlert($service, $oldStatus, $code, $time, $detail)
    {
        $contacts = Contact::where('is_active', true)->get();
        if ($contacts->isEmpty()) {
            return;
        }

        $formattedTime = number_format($time, 2) . 's';
        $timeMs = $time * 1000;
        $statusText = $oldStatus == 'DOWN' ? 'DOWN' : 'WARNING';

        $message = "✅ SERVICE NORMAL KEMBALI!\n";
        $message .= "═══════════════════════\n";
        $message .= "Nama: " . $service->name . "\n";
        $message .= "Target: " . $service->target . "\n";
        $message .= "Status: 🟢 UP (sebelumnya " . $statusText . ")\n";
        $message .= "Waktu: " . $formattedTime . " (" . number_format($timeMs, 0) . "ms)\n";

        if ($timeMs > 2000) {
            $message .= "📌 " . $this->getShortRecommendation($timeMs) . "\n";
        }

        $message .= "═══════════════════════\n";
        $message .= "🕐 " . now()->setTimezone('Asia/Jakarta')->format('d-m-Y H:i:s') . " WIB";

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            Log::info($result ? "✅ WA RESTORED ke: {$contact->phone} - {$service->name}" : "❌ Gagal WA RESTORED ke: {$contact->phone}");
        }
    }

    private function sendWhatsappAlert($service, $status, $code, $time, $reason, $detail, $action)
    {
        $contacts = Contact::where('is_active', true)->get();
        if ($contacts->isEmpty()) {
            return;
        }

        $formattedTime = number_format($time, 2) . 's';
        $timeMs = $time * 1000;

        if ($status == 'DOWN') {
            $judul = "🔴 SERVICE DOWN!";
            $statusIcon = "🔴";
            $statusText = "DOWN";
            $urgency = "🚨 SEGERA PERBAIKI!";
        } else {
            $judul = "🟡 SERVICE WARNING!";
            $statusIcon = "🟡";
            $statusText = "WARNING";
            $urgency = "⚠️ PERHATIAN!";
        }

        $shortReason = $detail;
        if (strlen($shortReason) > 60) {
            $shortReason = substr($shortReason, 0, 60) . '...';
        }

        $message = $judul . "\n";
        $message .= "═══════════════════════\n";
        $message .= "Nama: " . $service->name . "\n";
        $message .= "Target: " . $service->target . "\n";
        $message .= "Status: " . $statusIcon . " " . $statusText . "\n";
        $message .= "Kode: " . $code . "\n";
        $message .= "Waktu: " . $formattedTime . " (" . number_format($timeMs, 0) . "ms)\n";
        $message .= "═══════════════════════\n";
        $message .= "📌 " . $shortReason . "\n";
        $message .= "🔧 " . $action . "\n";
        $message .= "═══════════════════════\n";
        $message .= $urgency . "\n";
        $message .= "🕐 " . now()->setTimezone('Asia/Jakarta')->format('d-m-Y H:i:s') . " WIB";

        foreach ($contacts as $contact) {
            $result = FonnteService::send($contact->phone, $message);
            Log::info($result ? "✅ WA ke: {$contact->phone} - {$status}" : "❌ Gagal WA ke: {$contact->phone}");
        }
    }

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
            $this->networkAlertSent = true;
        }
        if ($isNetworkConnected && $this->networkAlertSent) {
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