<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceLog;
use App\Models\SmokeLog;
use App\Models\SmokeDevice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);

        // Statistik Service
        $total = Service::where('is_archived', 0)->count();
        $up = Service::where('last_status', 'UP')->where('is_archived', 0)->count();
        $warning = Service::where('last_status', 'WARNING')->where('is_archived', 0)->count();
        $down = Service::where('last_status', 'DOWN')->where('is_archived', 0)->count();

        // Data Service
        $services = Service::where('is_archived', 0)->orderBy('id', 'desc')->get();
        $latestServices = Service::where('is_archived', 0)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);

        // Donut Chart - 7 Hari Terakhir
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();

        $logs = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        $totalUp = $logs->where('status', 'UP')->count();
        $totalWarning = $logs->where('status', 'WARNING')->count();
        $totalDown = $logs->where('status', 'DOWN')->count();
        $totalChanges = $logs->count();

        $upPercent7 = $totalChanges > 0 ? round(($totalUp / $totalChanges) * 100, 1) : 0;
        $warningPercent7 = $totalChanges > 0 ? round(($totalWarning / $totalChanges) * 100, 1) : 0;
        $downPercent7 = $totalChanges > 0 ? round(($totalDown / $totalChanges) * 100, 1) : 0;

        $totalServices = Service::where('is_archived', 0)->count();

        // Uptime Chart - 7 Hari Terakhir
        $logsForChart = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc')
            ->get();

        $groupedLogs = $logsForChart->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        $chartLabels = [];
        $uptimeData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $key = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d/m/Y');

            if (isset($groupedLogs[$key])) {
                $dayLogs = $groupedLogs[$key];
                $lastLog = $dayLogs->sortByDesc('created_at')->first();

                if ($lastLog->status === 'UP') {
                    $uptimeData[] = 100;
                } elseif ($lastLog->status === 'WARNING') {
                    $uptimeData[] = 70;
                } else {
                    $uptimeData[] = 0;
                }
            } else {
                $uptimeData[] = 0;
            }
        }

        // Uptime Rate Keseluruhan
        $allLogs = ServiceLog::whereIn('service_id', $activeServiceIds)->get();
        $totalAllLogs = $allLogs->count();

        if ($totalAllLogs > 0) {
            $totalWeightAll = 0;
            foreach ($allLogs as $log) {
                if ($log->status === 'UP') {
                    $totalWeightAll += 100;
                } elseif ($log->status === 'WARNING') {
                    $totalWeightAll += 70;
                }
            }
            $uptimeOverall = round($totalWeightAll / $totalAllLogs, 2);
        } else {
            $uptimeOverall = 0;
        }

        // Smoke Chart - 7 Hari Terakhir
        $smokeStartDate = Carbon::now()->subDays(6)->startOfDay();
        $smokeEndDate = Carbon::now()->endOfDay();

        $smokeLogs = SmokeLog::where('created_at', '>=', $smokeStartDate)
            ->where('created_at', '<=', $smokeEndDate)
            ->orderBy('smoke_value', 'desc')
            ->get();

        $groupedSmokeLogs = $smokeLogs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        $smokeLabels = [];
        $smokeData = [];
        $smokeStatuses = [];
        $smokeTimestamps = [];
        $smokeMaxValues = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $smokeLabels[] = $date->format('d/m/Y');

            if (isset($groupedSmokeLogs[$dateKey])) {
                $dayLogs = $groupedSmokeLogs[$dateKey];
                $maxSmokeValue = $dayLogs->max('smoke_value');
                $logWithMaxValue = $dayLogs->where('smoke_value', $maxSmokeValue)->first();

                $smokeData[] = round($maxSmokeValue, 2);
                $smokeStatuses[$dateKey] = $logWithMaxValue->status ?? 'NORMAL';
                $smokeTimestamps[$dateKey] = $logWithMaxValue->created_at->format('H:i:s');
                $smokeMaxValues[$dateKey] = $maxSmokeValue;
            } else {
                $smokeData[] = 0;
                $smokeStatuses[$dateKey] = 'NO_DATA';
                $smokeTimestamps[$dateKey] = null;
                $smokeMaxValues[$dateKey] = 0;
            }
        }

        // ESP Status
        $smokeDevices = SmokeDevice::all();

        $onlineCount = 0;
        $lastSmokeValue = 0;
        $lastSmokeStatus = 'NORMAL';
        $lastSeenAt = null;
        $deviceName = 'ESP32-Smoke';

        $latestDevice = $smokeDevices->sortByDesc('last_seen_at')->first();

        if ($latestDevice) {
            $isOnline = false;
            if ($latestDevice->last_seen_at) {
                $lastSeen = Carbon::parse($latestDevice->last_seen_at);
                $isOnline = $lastSeen->diffInMinutes(now()) < 2;
            }

            if ($isOnline) {
                $onlineCount = 1;
            }

            $lastSmokeValue = $latestDevice->smoke_value ?? 0;
            $lastSmokeStatus = $latestDevice->status ?? 'NORMAL';
            $lastSeenAt = $latestDevice->last_seen_at;
            $deviceName = $latestDevice->name ?? 'ESP32-Smoke';
        }

        if ($smokeDevices->isEmpty()) {
            $onlineCount = 0;
            $lastSmokeValue = 0;
            $lastSmokeStatus = 'NORMAL';
            $lastSeenAt = null;
            $deviceName = 'ESP32-Smoke';
        }

        $espStatus = $onlineCount > 0 ? 'ONLINE' : 'OFFLINE';
        $espStatusClass = $onlineCount > 0 ? 'online' : 'offline';
        $espStatusLabel = $onlineCount > 0 ? '🟢 ONLINE' : '🔴 OFFLINE';

        // Informasi Tambahan
        $today = Carbon::now()->format('Y-m-d');
        $todayMaxValue = isset($smokeMaxValues[$today]) ? $smokeMaxValues[$today] : 0;
        $todayStatus = isset($smokeStatuses[$today]) ? $smokeStatuses[$today] : 'NO_DATA';

        $totalArchived = Service::where('is_archived', 1)->count();
        $hasData = $total > 0;

        // SSL Expiry Data
        $sslServices = Service::where('is_archived', 0)
            ->whereNotNull('ssl_status')
            ->where('ssl_status', '!=', 'N/A')
            ->whereNotNull('ssl_days_remaining')
            ->get()
            ->sortBy('ssl_days_remaining');

        $sslStats = [
            'total' => $sslServices->count(),
            'valid' => $sslServices->where('ssl_status', 'VALID')->count(),
            'warning' => $sslServices->where('ssl_status', 'WARNING')->count(),
            'critical' => $sslServices->where('ssl_status', 'CRITICAL')->count(),
            'expired' => $sslServices->where('ssl_status', 'EXPIRED')->count(),
            'expiring_soon' => $sslServices->filter(function($service) {
                return $service->ssl_days_remaining <= 7 && $service->ssl_days_remaining > 0;
            })->count(),
        ];

        $soonestExpiry = $sslServices->first();
        $soonestExpiryData = null;
        if ($soonestExpiry) {
            $soonestExpiryData = [
                'name' => $soonestExpiry->name,
                'days' => $soonestExpiry->ssl_days_remaining,
                'expiry' => $soonestExpiry->ssl_expiry_date ? Carbon::parse($soonestExpiry->ssl_expiry_date)->format('d/m/Y') : '-',
                'status' => $soonestExpiry->ssl_status,
            ];
        }

        return view('dashboard', compact(
            'total',
            'up',
            'warning',
            'down',
            'totalArchived',
            'services',
            'latestServices',
            'chartLabels',
            'uptimeData',
            'uptimeOverall',
            'smokeLabels',
            'smokeData',
            'smokeStatuses',
            'smokeTimestamps',
            'smokeMaxValues',
            'onlineCount',
            'espStatus',
            'espStatusClass',
            'espStatusLabel',
            'lastSmokeValue',
            'lastSmokeStatus',
            'lastSeenAt',
            'deviceName',
            'hasData',
            'todayMaxValue',
            'todayStatus',
            'totalUp',
            'totalWarning',
            'totalDown',
            'totalChanges',
            'totalServices',
            'upPercent7',
            'warningPercent7',
            'downPercent7',
            'sslServices',
            'sslStats',
            'soonestExpiryData'
        ));
    }

    /**
     * Mendapatkan status SSL semua service (AJAX)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSSLStatus(Request $request)
    {
        try {
            $services = Service::where('is_archived', 0)
                ->select('id', 'name', 'target', 'ssl_status', 'ssl_days_remaining', 'ssl_expiry_date', 'ssl_issuer')
                ->whereNotNull('ssl_status')
                ->where('ssl_status', '!=', 'N/A')
                ->get()
                ->map(function($service) {
                    $days = $service->ssl_days_remaining ?? 0;
                    $status = $service->ssl_status ?? 'NA';

                    $badgeClass = 'na';
                    $icon = '🔓';
                    if ($status === 'VALID') {
                        $badgeClass = 'valid';
                        $icon = '🟢';
                    } elseif ($status === 'WARNING') {
                        $badgeClass = 'warning';
                        $icon = '🟡';
                    } elseif ($status === 'CRITICAL') {
                        $badgeClass = 'critical';
                        $icon = '🔴';
                    } elseif ($status === 'EXPIRED') {
                        $badgeClass = 'expired';
                        $icon = '🔴';
                    }

                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'target' => $service->target,
                        'ssl_status' => $status,
                        'ssl_days_remaining' => $days,
                        'ssl_expiry_date' => $service->ssl_expiry_date ? Carbon::parse($service->ssl_expiry_date)->format('d/m/Y') : '-',
                        'ssl_issuer' => $service->ssl_issuer ?? '-',
                        'badge_class' => $badgeClass,
                        'icon' => $icon,
                        'display_days' => $days > 0 ? $days . ' hari' : ($days == 0 ? 'Hari ini' : '⚠️ EXPIRED'),
                    ];
                })
                ->sortBy('ssl_days_remaining')
                ->values();

            return response()->json([
                'success' => true,
                'data' => $services,
                'stats' => [
                    'total' => $services->count(),
                    'valid' => $services->where('ssl_status', 'VALID')->count(),
                    'warning' => $services->where('ssl_status', 'WARNING')->count(),
                    'critical' => $services->where('ssl_status', 'CRITICAL')->count(),
                    'expired' => $services->where('ssl_status', 'EXPIRED')->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data SSL: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan statistik SSL (AJAX)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSSLStatistics()
    {
        try {
            $services = Service::where('is_archived', 0)
                ->whereNotNull('ssl_status')
                ->where('ssl_status', '!=', 'N/A')
                ->get();

            $stats = [
                'total' => $services->count(),
                'valid' => $services->where('ssl_status', 'VALID')->count(),
                'warning' => $services->where('ssl_status', 'WARNING')->count(),
                'critical' => $services->where('ssl_status', 'CRITICAL')->count(),
                'expired' => $services->where('ssl_status', 'EXPIRED')->count(),
                'expiring_soon' => $services->filter(function($service) {
                    return $service->ssl_days_remaining <= 7 && $service->ssl_days_remaining > 0;
                })->count(),
                'expiring_this_month' => $services->filter(function($service) {
                    return $service->ssl_days_remaining <= 30 && $service->ssl_days_remaining > 0;
                })->count(),
            ];

            $soonest = $services->sortBy('ssl_days_remaining')->take(5)->map(function($service) {
                return [
                    'name' => $service->name,
                    'days' => $service->ssl_days_remaining ?? 0,
                    'status' => $service->ssl_status ?? 'NA',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $stats,
                'soonest' => $soonest,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik SSL: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh SSL certificate untuk satu service
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshSSL($id)
    {
        try {
            $service = Service::findOrFail($id);

            if ($service->is_archived) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service sedang diarsipkan'
                ], 400);
            }

            $monitorService = app(\App\Services\ServiceMonitorService::class);
            $sslInfo = $monitorService->getSSLInfo($service);

            if ($sslInfo) {
                $service->update([
                    'ssl_status' => $sslInfo['status'] ?? 'UNKNOWN',
                    'ssl_expiry_date' => $sslInfo['valid_to'] ?? null,
                    'ssl_days_remaining' => $sslInfo['days_remaining'] ?? null,
                    'ssl_issuer' => $sslInfo['issuer'] ?? null,
                    'ssl_subject' => $sslInfo['subject'] ?? null,
                    'ssl_message' => $sslInfo['message'] ?? null,
                    'ssl_is_expired' => $sslInfo['is_down'] ?? false,
                    'ssl_checked_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'SSL berhasil di-refresh untuk ' . $service->name,
                    'data' => [
                        'status' => $service->ssl_status,
                        'days_remaining' => $service->ssl_days_remaining,
                        'expiry_date' => $service->ssl_expiry_date ? Carbon::parse($service->ssl_expiry_date)->format('d/m/Y') : '-',
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Service ini bukan HTTPS atau SSL tidak tersedia'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal refresh SSL: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh semua SSL certificate
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshAllSSL()
    {
        try {
            $services = Service::where('is_archived', 0)->get();
            $updated = 0;
            $monitorService = app(\App\Services\ServiceMonitorService::class);

            foreach ($services as $service) {
                $sslInfo = $monitorService->getSSLInfo($service);

                if ($sslInfo) {
                    $service->update([
                        'ssl_status' => $sslInfo['status'] ?? 'UNKNOWN',
                        'ssl_expiry_date' => $sslInfo['valid_to'] ?? null,
                        'ssl_days_remaining' => $sslInfo['days_remaining'] ?? null,
                        'ssl_issuer' => $sslInfo['issuer'] ?? null,
                        'ssl_subject' => $sslInfo['subject'] ?? null,
                        'ssl_message' => $sslInfo['message'] ?? null,
                        'ssl_is_expired' => $sslInfo['is_down'] ?? false,
                        'ssl_checked_at' => now(),
                    ]);
                    $updated++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil refresh SSL untuk {$updated} service",
                'updated' => $updated
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal refresh semua SSL: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan alert SSL yang akan expired dalam 7 hari atau sudah expired
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSSLAlert()
    {
        try {
            $criticalServices = Service::where('is_archived', 0)
                ->whereNotNull('ssl_status')
                ->where('ssl_status', '!=', 'N/A')
                ->where(function($query) {
                    $query->where('ssl_days_remaining', '<=', 7)
                        ->orWhere('ssl_status', 'EXPIRED');
                })
                ->orderBy('ssl_days_remaining', 'asc')
                ->get()
                ->map(function($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'days' => $service->ssl_days_remaining ?? 0,
                        'status' => $service->ssl_status ?? 'UNKNOWN',
                        'expiry' => $service->ssl_expiry_date ? Carbon::parse($service->ssl_expiry_date)->format('d/m/Y') : '-',
                        'is_expired' => $service->ssl_status === 'EXPIRED',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $criticalServices,
                'count' => $criticalServices->count(),
                'has_alert' => $criticalServices->count() > 0,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil alert SSL: ' . $e->getMessage()
            ], 500);
        }
    }
}