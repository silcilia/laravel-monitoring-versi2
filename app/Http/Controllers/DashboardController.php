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
        // 🔥 GRAFIK UPTIME 7 HARI - HANYA LOG DARI SERVICE AKTIF
        // ============================================================
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        // Ambil ID service yang aktif (tidak diarsip)
        $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();
        
        // HANYA ambil log dari service yang aktif
        $logs = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at', 'asc')
            ->get();

        $groupedLogs = $logs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        $chartLabels = [];
        $uptimeData = [];

        $current = Carbon::now()->subDays(6)->startOfDay();
        $end = Carbon::now()->endOfDay();

        while ($current <= $end) {
            $key = $current->format('Y-m-d');
            $chartLabels[] = $current->format('d/m/Y');
            
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
                
                $uptimeData[] = $totalChecks > 0 
                    ? round($totalWeight / $totalChecks, 2) 
                    : 0;
            } else {
                $uptimeData[] = 0;
            }
            
            $current->addDay();
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
        // 🔥 GRAFIK SMOKE (7 HARI) - PERBAIKAN
        // ============================================================
        $smokeStartDate = Carbon::now()->subDays(6)->startOfDay();
        $smokeEndDate = Carbon::now()->endOfDay();
        
        // Ambil semua log smoke dalam 7 hari terakhir
        $smokeLogs = SmokeLog::where('created_at', '>=', $smokeStartDate)
            ->where('created_at', '<=', $smokeEndDate)
            ->orderBy('created_at', 'asc')
            ->get();

        // Kelompokkan berdasarkan tanggal
        $groupedSmokeLogs = $smokeLogs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        $smokeLabels = [];
        $smokeData = [];

        // Loop untuk 7 hari terakhir
        $currentSmoke = Carbon::now()->subDays(6)->startOfDay();
        $endSmoke = Carbon::now()->endOfDay();

        while ($currentSmoke <= $endSmoke) {
            $key = $currentSmoke->format('Y-m-d');
            $smokeLabels[] = $currentSmoke->format('d/m/Y');
            
            // CEK: Apakah ada log untuk tanggal ini?
            if (isset($groupedSmokeLogs[$key])) {
                // ADA DATA: ambil nilai smoke terakhir di hari tersebut
                $lastLogOfDay = $groupedSmokeLogs[$key]->last();
                $smokeData[] = round($lastLogOfDay->smoke_value, 2);
            } else {
                // TIDAK ADA DATA: set ke null untuk tidak menampilkan titik
                $smokeData[] = null;
            }
            
            $currentSmoke->addDay();
        }

        // ============================================================
        // 🔥 ESP STATUS - PERBAIKAN
        // ============================================================
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

        // Jika tidak ada device sama sekali
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
        // 🔥 TAMBAHKAN TOTAL ARSIP UNTUK REFERENSI
        // ============================================================
        $totalArchived = Service::where('is_archived', 1)->count();

        // ============================================================
        // 🔥 DONUT CHART - HANYA SERVICE AKTIF
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
                'onlineCount',
                'espStatus',
                'espStatusClass',
                'espStatusLabel',
                'lastSmokeValue',
                'lastSmokeStatus',
                'lastSeenAt',
                'deviceName',
                'hasData'
            )
        );
    }
}