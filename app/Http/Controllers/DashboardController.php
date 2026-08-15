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
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);

        // ============================================================
        // 🔥 STATISTIK SERVICE - HANYA YANG TIDAK DIARSIP
        // ============================================================
        $total = Service::where('is_archived', 0)->count();
        $up = Service::where('last_status', 'UP')->where('is_archived', 0)->count();
        $warning = Service::where('last_status', 'WARNING')->where('is_archived', 0)->count();
        $down = Service::where('last_status', 'DOWN')->where('is_archived', 0)->count();

        // ============================================================
        // 🔥 DATA SERVICE - HANYA YANG TIDAK DIARSIP
        // ============================================================
        $services = Service::where('is_archived', 0)->orderBy('id', 'desc')->get();
        
        $latestServices = Service::where('is_archived', 0)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends(['perPage' => $perPage]);

        // ============================================================
        // 🔥 DONUT CHART - SEMUA PERUBAHAN STATUS (7 HARI TERAKHIR)
        // ============================================================
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();
        
        // Ambil SEMUA log dalam 7 hari terakhir
        $logs = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        // Hitung SEMUA perubahan status
        $totalUp = $logs->where('status', 'UP')->count();
        $totalWarning = $logs->where('status', 'WARNING')->count();
        $totalDown = $logs->where('status', 'DOWN')->count();
        $totalChanges = $logs->count();

        // Total service aktif
        $totalServices = Service::where('is_archived', 0)->count();

        // ============================================================
        // 🔥 GRAFIK UPTIME 7 HARI - STATUS TERAKHIR PER HARI
        // ============================================================
        // Ambil semua log dalam 7 hari terakhir (untuk bar chart)
        $logsForChart = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc')
            ->get();

        // Kelompokkan berdasarkan tanggal
        $groupedLogs = $logsForChart->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        $chartLabels = [];
        $uptimeData = [];
        $statusPerDay = [];

        // Loop 7 hari dari yang paling lama ke yang paling baru
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $key = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d/m/Y');
            
            if (isset($groupedLogs[$key])) {
                $dayLogs = $groupedLogs[$key];
                
                // Ambil status TERAKHIR di hari itu
                $lastLog = $dayLogs->sortByDesc('created_at')->first();
                
                if ($lastLog->status === 'UP') {
                    $uptimeValue = 100;
                    $statusPerDay[$key] = 'UP';
                } elseif ($lastLog->status === 'WARNING') {
                    $uptimeValue = 70;
                    $statusPerDay[$key] = 'WARNING';
                } elseif ($lastLog->status === 'DOWN') {
                    $uptimeValue = 0;
                    $statusPerDay[$key] = 'DOWN';
                } else {
                    $uptimeValue = 0;
                    $statusPerDay[$key] = 'UNKNOWN';
                }
                
                $uptimeData[] = $uptimeValue;
                
            } else {
                $uptimeData[] = 0;
                $statusPerDay[$key] = 'NO_DATA';
            }
        }

        // ============================================================
        // 🔥 UPTIME RATE KESELURUHAN - HANYA DARI SERVICE AKTIF
        // ============================================================
        $allLogs = ServiceLog::whereIn('service_id', $activeServiceIds)->get();
        $totalAllLogs = $allLogs->count();
        
        if ($totalAllLogs > 0) {
            $totalWeightAll = 0;
            foreach ($allLogs as $log) {
                if ($log->status === 'UP') {
                    $totalWeightAll += 100;
                } elseif ($log->status === 'WARNING') {
                    $totalWeightAll += 70;
                } elseif ($log->status === 'DOWN') {
                    $totalWeightAll += 0;
                }
            }
            $uptimeOverall = round($totalWeightAll / $totalAllLogs, 2);
        } else {
            $uptimeOverall = 0;
        }

        // ============================================================
        // 🔥 GRAFIK SMOKE (7 HARI) - MENAMPILKAN NILAI TERTINGGI PER HARI
        // ============================================================
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

        // ============================================================
        // 🔥 ESP STATUS
        // ============================================================
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

        // ============================================================
        // 🔥 TAMBAHKAN DATA UNTUK INFO TAMBAHAN
        // ============================================================
        $today = Carbon::now()->format('Y-m-d');
        $todayMaxValue = isset($smokeMaxValues[$today]) ? $smokeMaxValues[$today] : 0;
        $todayStatus = isset($smokeStatuses[$today]) ? $smokeStatuses[$today] : 'NO_DATA';

        // ============================================================
        // 🔥 TOTAL ARSIP
        // ============================================================
        $totalArchived = Service::where('is_archived', 1)->count();

        // ============================================================
        // 🔥 HAS DATA
        // ============================================================
        $hasData = $total > 0;

        return view(
            'dashboard',
            compact(
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
                // Data baru untuk donut chart
                'totalUp',
                'totalWarning',
                'totalDown',
                'totalChanges',
                'totalServices'
            )
        );
    }
}