<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceLog;
use App\Models\SmokeLog;
use App\Models\SmokeDevice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    /**
     * ============================================================
     *  📡 API 1: DASHBOARD STATS
     *  ============================================================
     *  🔗 URL: GET /api/dashboard/stats
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Response: Total, Up, Warning, Down, ESP Status
     *  📌 Sesuai dengan stat-card di dashboard
     * ============================================================
     */
    public function stats()
    {
        try {
            // Service Stats - HANYA YANG TIDAK DIARSIP
            $total = Service::where('is_archived', 0)->count();
            $up = Service::where('last_status', 'UP')->where('is_archived', 0)->count();
            $warning = Service::where('last_status', 'WARNING')->where('is_archived', 0)->count();
            $down = Service::where('last_status', 'DOWN')->where('is_archived', 0)->count();

            // ESP Status - AMBIL DEVICE TERBARU
            $smokeDevices = SmokeDevice::all();
            $onlineCount = 0;
            $lastSmokeValue = 0;
            $lastSmokeStatus = 'NORMAL';
            $lastSeenAt = null;
            $deviceName = 'ESP32-Smoke';

            // Cari device yang terakhir update
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

            $espStatus = $onlineCount > 0 ? 'ONLINE' : 'OFFLINE';

            return response()->json([
                'success' => true,
                'data' => [
                    'services' => [
                        'total' => $total,
                        'up' => $up,
                        'warning' => $warning,
                        'down' => $down,
                    ],
                    'esp' => [
                        'device_name' => $deviceName,
                        'status' => $espStatus,
                        'is_online' => $onlineCount > 0,
                        'online_count' => $onlineCount,
                        'last_smoke_value' => $lastSmokeValue,
                        'last_smoke_status' => $lastSmokeStatus,
                        'last_seen_at' => $lastSeenAt ? Carbon::parse($lastSeenAt)->diffForHumans() : null,
                        'last_seen_raw' => $lastSeenAt,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API 2: DASHBOARD UPTIME
     *  ============================================================
     *  🔗 URL: GET /api/dashboard/uptime
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Response: Uptime 24 jam, 7 hari, 30 hari
     *  📌 Sesuai dengan uptime-card di dashboard
     * ============================================================
     */
    public function uptime()
    {
        try {
            // HANYA ambil log dari service yang aktif (tidak diarsip)
            $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();

            // Uptime 24 Jam
            $logs24h = ServiceLog::whereIn('service_id', $activeServiceIds)
                ->where('created_at', '>=', now()->subHours(24))
                ->get();
            $uptime24h = $this->calculateUptimeFromLogs($logs24h);

            // Uptime 7 Hari
            $logs7d = ServiceLog::whereIn('service_id', $activeServiceIds)
                ->where('created_at', '>=', now()->subDays(7))
                ->get();
            $uptime7d = $this->calculateUptimeFromLogs($logs7d);

            // Uptime 30 Hari
            $logs30d = ServiceLog::whereIn('service_id', $activeServiceIds)
                ->where('created_at', '>=', now()->subDays(30))
                ->get();
            $uptime30d = $this->calculateUptimeFromLogs($logs30d);

            // Uptime Keseluruhan (semua data dari service aktif)
            $allLogs = ServiceLog::whereIn('service_id', $activeServiceIds)->get();
            $uptimeOverall = $this->calculateUptimeFromLogs($allLogs);

            // Status berdasarkan uptime 30 hari
            $status = 'excellent';
            $statusText = 'Excellent';
            $statusColor = '#22c55e'; // green
            if ($uptime30d < 70) {
                $status = 'poor';
                $statusText = 'Needs Attention';
                $statusColor = '#ef4444'; // red
            } elseif ($uptime30d < 90) {
                $status = 'good';
                $statusText = 'Good';
                $statusColor = '#eab308'; // yellow
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'uptime_24h' => round($uptime24h, 2),
                    'uptime_7d' => round($uptime7d, 2),
                    'uptime_30d' => round($uptime30d, 2),
                    'uptime_overall' => round($uptimeOverall, 2),
                    'status' => $status,
                    'status_text' => $statusText,
                    'status_color' => $statusColor,
                    'last_updated' => now()->toDateTimeString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data uptime: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API 3: UPTIME CHART (7 HARI)
     *  ============================================================
     *  🔗 URL: GET /api/dashboard/uptime-chart
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Response: Labels dan data uptime 7 hari
     *  📌 Sesuai dengan chart uptime di dashboard
     * ============================================================
     */
    public function uptimeChart()
    {
        try {
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            
            // HANYA ambil log dari service yang aktif
            $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();
            
            $logs = ServiceLog::whereIn('service_id', $activeServiceIds)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->orderBy('created_at', 'asc')
                ->get();

            $groupedLogs = $logs->groupBy(function($log) {
                return $log->created_at->format('Y-m-d');
            });

            $labels = [];
            $data = [];

            $current = Carbon::now()->subDays(6)->startOfDay();
            $end = Carbon::now()->endOfDay();

            while ($current <= $end) {
                $key = $current->format('Y-m-d');
                $labels[] = $current->format('d/m/Y');
                
                if (isset($groupedLogs[$key])) {
                    $dayLogs = $groupedLogs[$key];
                    $totalChecks = $dayLogs->count();
                    
                    $totalWeight = 0;
                    foreach ($dayLogs as $log) {
                        if ($log->status === 'UP') {
                            $totalWeight += 100;
                        } elseif ($log->status === 'WARNING') {
                            $totalWeight += 70;
                        } elseif ($log->status === 'DOWN') {
                            $totalWeight += 0;
                        }
                    }
                    
                    $data[] = $totalChecks > 0 ? round($totalWeight / $totalChecks, 2) : 0;
                } else {
                    $data[] = 0;
                }
                
                $current->addDay();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'values' => $data,
                    'period' => '7 Hari Terakhir',
                    'unit' => '%'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data chart uptime: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API 4: SMOKE CHART (7 HARI) - PERBAIKAN
     *  ============================================================
     *  🔗 URL: GET /api/dashboard/smoke-chart
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Response: Labels dan data smoke 7 hari
     *  📌 Sesuai dengan chart smoke di dashboard
     * ============================================================
     */
    public function smokeChart()
    {
        try {
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            
            $smokeLogs = SmokeLog::where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->orderBy('created_at', 'asc')
                ->get();

            $groupedLogs = $smokeLogs->groupBy(function($log) {
                return $log->created_at->format('Y-m-d');
            });

            $labels = [];
            $data = [];

            $current = Carbon::now()->subDays(6)->startOfDay();
            $end = Carbon::now()->endOfDay();

            while ($current <= $end) {
                $key = $current->format('Y-m-d');
                $labels[] = $current->format('d/m/Y');
                
                // CEK: Apakah ada log untuk tanggal ini?
                if (isset($groupedLogs[$key])) {
                    // ADA DATA: ambil nilai smoke terakhir di hari tersebut
                    $lastLogOfDay = $groupedLogs[$key]->last();
                    $data[] = round($lastLogOfDay->smoke_value, 2);
                } else {
                    // TIDAK ADA DATA: set ke null (tidak menampilkan titik)
                    $data[] = null;
                }
                
                $current->addDay();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'values' => $data,
                    'period' => '7 Hari Terakhir',
                    'unit' => 'ppm',
                    'has_null' => true // Flag untuk frontend menangani null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data chart smoke: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API 5: ESP STATUS REAL-TIME - PERBAIKAN
     *  ============================================================
     *  🔗 URL: GET /api/dashboard/esp-status
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Response: Status ESP, PPM, Smoke Status
     *  📌 Sesuai dengan ESP card di dashboard
     * ============================================================
     */
    public function espStatus()
    {
        try {
            $smokeDevices = SmokeDevice::all();
            
            $onlineCount = 0;
            $lastSmokeValue = 0;
            $lastSmokeStatus = 'NORMAL';
            $lastSeenAt = null;
            $deviceName = 'ESP32-Smoke';
            $totalDevices = $smokeDevices->count();
            
            // Cari device yang terakhir update
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

            $isOnline = $onlineCount > 0;

            // Tentukan status class dan icon
            $statusClass = $isOnline ? 'online' : 'offline';
            $statusIcon = $isOnline ? '🟢' : '🔴';
            $statusLabel = $isOnline ? 'ONLINE' : 'OFFLINE';

            // Tentukan warna berdasarkan status smoke
            $smokeColor = '#22c55e'; // hijau untuk NORMAL
            if ($lastSmokeStatus === 'WARNING') {
                $smokeColor = '#eab308'; // kuning
            } elseif ($lastSmokeStatus === 'DANGER') {
                $smokeColor = '#ef4444'; // merah
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'device_name' => $deviceName,
                    'status' => $statusLabel,
                    'status_class' => $statusClass,
                    'status_icon' => $statusIcon,
                    'is_online' => $isOnline,
                    'online_count' => $onlineCount,
                    'total_devices' => $totalDevices,
                    'last_smoke_value' => $lastSmokeValue,
                    'last_smoke_status' => $lastSmokeStatus,
                    'smoke_color' => $smokeColor,
                    'last_seen_at' => $lastSeenAt ? Carbon::parse($lastSeenAt)->diffForHumans() : null,
                    'last_seen_raw' => $lastSeenAt,
                    'last_updated' => now()->toDateTimeString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status ESP: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API 6: DASHBOARD ALL DATA (Comprehensive)
     *  ============================================================
     *  🔗 URL: GET /api/dashboard/all
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Response: Semua data dashboard dalam satu request
     *  📌 Untuk mengurangi jumlah request API
     * ============================================================
     */
    public function all()
    {
        try {
            // Get stats
            $stats = $this->getStatsData();
            
            // Get uptime
            $uptime = $this->getUptimeData();
            
            // Get uptime chart
            $uptimeChart = $this->getUptimeChartData();
            
            // Get smoke chart
            $smokeChart = $this->getSmokeChartData();
            
            // Get ESP status
            $espStatus = $this->getEspStatusData();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'uptime' => $uptime,
                    'uptime_chart' => $uptimeChart,
                    'smoke_chart' => $smokeChart,
                    'esp_status' => $espStatus,
                    'last_updated' => now()->toDateTimeString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     *  📡 API 7: SMOKE HISTORY (UNTUK DETAIL)
     *  ============================================================
     *  🔗 URL: GET /api/dashboard/smoke-history
     *  🔑 Butuh Auth: Sanctum Token
     *  📦 Response: History data smoke dengan pagination
     * ============================================================
     */
    public function smokeHistory(Request $request)
    {
        try {
            $perPage = $request->input('perPage', 20);
            $days = $request->input('days', 7);
            
            $startDate = Carbon::now()->subDays($days)->startOfDay();
            
            $logs = SmokeLog::where('created_at', '>=', $startDate)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'logs' => $logs->items(),
                    'pagination' => [
                        'current_page' => $logs->currentPage(),
                        'per_page' => $logs->perPage(),
                        'total' => $logs->total(),
                        'last_page' => $logs->lastPage(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil history smoke: ' . $e->getMessage()
            ], 500);
        }
    }

    // ================================================================
    // 🔧 PRIVATE HELPER METHODS
    // ================================================================

    /**
     * Hitung uptime dari collection logs dengan bobot
     * UP = 100, WARNING = 70, DOWN = 0
     */
    private function calculateUptimeFromLogs($logs)
    {
        $total = $logs->count();
        
        if ($total === 0) {
            return 0;
        }

        $totalWeight = 0;
        foreach ($logs as $log) {
            if ($log->status === 'UP') {
                $totalWeight += 100;
            } elseif ($log->status === 'WARNING') {
                $totalWeight += 70;
            } elseif ($log->status === 'DOWN') {
                $totalWeight += 0;
            }
        }
        
        return round($totalWeight / $total, 2);
    }

    /**
     * Get stats data
     */
    private function getStatsData()
    {
        $total = Service::where('is_archived', 0)->count();
        $up = Service::where('last_status', 'UP')->where('is_archived', 0)->count();
        $warning = Service::where('last_status', 'WARNING')->where('is_archived', 0)->count();
        $down = Service::where('last_status', 'DOWN')->where('is_archived', 0)->count();

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

        return [
            'services' => [
                'total' => $total,
                'up' => $up,
                'warning' => $warning,
                'down' => $down,
            ],
            'esp' => [
                'device_name' => $deviceName,
                'status' => $onlineCount > 0 ? 'ONLINE' : 'OFFLINE',
                'is_online' => $onlineCount > 0,
                'online_count' => $onlineCount,
                'last_smoke_value' => $lastSmokeValue,
                'last_smoke_status' => $lastSmokeStatus,
                'last_seen_at' => $lastSeenAt ? Carbon::parse($lastSeenAt)->diffForHumans() : null,
            ]
        ];
    }

    /**
     * Get uptime data
     */
    private function getUptimeData()
    {
        $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();

        $logs24h = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '>=', now()->subHours(24))
            ->get();
        $uptime24h = $this->calculateUptimeFromLogs($logs24h);

        $logs7d = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->get();
        $uptime7d = $this->calculateUptimeFromLogs($logs7d);

        $logs30d = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();
        $uptime30d = $this->calculateUptimeFromLogs($logs30d);

        $allLogs = ServiceLog::whereIn('service_id', $activeServiceIds)->get();
        $uptimeOverall = $this->calculateUptimeFromLogs($allLogs);

        $status = 'excellent';
        $statusText = 'Excellent';
        $statusColor = '#22c55e';
        if ($uptime30d < 70) {
            $status = 'poor';
            $statusText = 'Needs Attention';
            $statusColor = '#ef4444';
        } elseif ($uptime30d < 90) {
            $status = 'good';
            $statusText = 'Good';
            $statusColor = '#eab308';
        }

        return [
            'uptime_24h' => round($uptime24h, 2),
            'uptime_7d' => round($uptime7d, 2),
            'uptime_30d' => round($uptime30d, 2),
            'uptime_overall' => round($uptimeOverall, 2),
            'status' => $status,
            'status_text' => $statusText,
            'status_color' => $statusColor,
        ];
    }

    /**
     * Get uptime chart data
     */
    private function getUptimeChartData()
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();
        
        $logs = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc')
            ->get();

        $groupedLogs = $logs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        $labels = [];
        $data = [];

        $current = Carbon::now()->subDays(6)->startOfDay();
        $end = Carbon::now()->endOfDay();

        while ($current <= $end) {
            $key = $current->format('Y-m-d');
            $labels[] = $current->format('d/m/Y');
            
            if (isset($groupedLogs[$key])) {
                $dayLogs = $groupedLogs[$key];
                $totalChecks = $dayLogs->count();
                
                $totalWeight = 0;
                foreach ($dayLogs as $log) {
                    if ($log->status === 'UP') {
                        $totalWeight += 100;
                    } elseif ($log->status === 'WARNING') {
                        $totalWeight += 70;
                    } elseif ($log->status === 'DOWN') {
                        $totalWeight += 0;
                    }
                }
                
                $data[] = $totalChecks > 0 ? round($totalWeight / $totalChecks, 2) : 0;
            } else {
                $data[] = 0;
            }
            
            $current->addDay();
        }

        return [
            'labels' => $labels,
            'values' => $data,
            'period' => '7 Hari Terakhir',
            'unit' => '%'
        ];
    }

    /**
     * Get smoke chart data - PERBAIKAN
     */
    private function getSmokeChartData()
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        $smokeLogs = SmokeLog::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc')
            ->get();

        $groupedLogs = $smokeLogs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        $labels = [];
        $data = [];

        $current = Carbon::now()->subDays(6)->startOfDay();
        $end = Carbon::now()->endOfDay();

        while ($current <= $end) {
            $key = $current->format('Y-m-d');
            $labels[] = $current->format('d/m/Y');
            
            if (isset($groupedLogs[$key])) {
                // Ambil nilai terakhir di hari tersebut
                $lastLogOfDay = $groupedLogs[$key]->last();
                $data[] = round($lastLogOfDay->smoke_value, 2);
            } else {
                // Tidak ada data: set null
                $data[] = null;
            }
            
            $current->addDay();
        }

        return [
            'labels' => $labels,
            'values' => $data,
            'period' => '7 Hari Terakhir',
            'unit' => 'ppm',
            'has_null' => true
        ];
    }

    /**
     * Get ESP status data - PERBAIKAN
     */
    private function getEspStatusData()
    {
        $smokeDevices = SmokeDevice::all();
        
        $onlineCount = 0;
        $lastSmokeValue = 0;
        $lastSmokeStatus = 'NORMAL';
        $lastSeenAt = null;
        $deviceName = 'ESP32-Smoke';
        $totalDevices = $smokeDevices->count();
        
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

        $isOnline = $onlineCount > 0;

        return [
            'device_name' => $deviceName,
            'status' => $isOnline ? 'ONLINE' : 'OFFLINE',
            'status_class' => $isOnline ? 'online' : 'offline',
            'status_icon' => $isOnline ? '🟢' : '🔴',
            'is_online' => $isOnline,
            'online_count' => $onlineCount,
            'total_devices' => $totalDevices,
            'last_smoke_value' => $lastSmokeValue,
            'last_smoke_status' => $lastSmokeStatus,
            'last_seen_at' => $lastSeenAt ? Carbon::parse($lastSeenAt)->diffForHumans() : null,
            'last_seen_raw' => $lastSeenAt,
        ];
    }
}