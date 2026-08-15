<?php

namespace App\Http\Controllers;

use App\Models\SmokeDevice;
use App\Models\SmokeLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SmokeController extends Controller
{
    private const WARNING_THRESHOLD = 300;
    private const DANGER_THRESHOLD  = 500;

    public function index(Request $request)
    {
        $devices = SmokeDevice::all();

        foreach ($devices as $device) {
            $isOnline = $device->last_seen_at && Carbon::parse($device->last_seen_at)->diffInMinutes(now()) < 2;
            $device->device_status = $isOnline ? 'ONLINE' : 'OFFLINE';
            $device->save();
        }

        $totalDevice = SmokeDevice::count();
        $online = SmokeDevice::where('device_status', 'ONLINE')->count();
        $offline = SmokeDevice::where('device_status', 'OFFLINE')->count();
        
        $normal = 0;
        $warning = 0;
        $danger = 0;

        foreach ($devices as $device) {
            $adc = $device->smoke_value ?? 0;
            
            if ($adc >= self::DANGER_THRESHOLD) {
                $danger++;
                $device->status = 'DANGER';
                $device->status_label = '🔴 DANGER';
                $device->status_class = 'danger';
                $device->status_icon = '🔥';
            } elseif ($adc >= self::WARNING_THRESHOLD) {
                $warning++;
                $device->status = 'WARNING';
                $device->status_label = '🟡 WARNING';
                $device->status_class = 'warning';
                $device->status_icon = '⚠️';
            } else {
                $normal++;
                $device->status = 'NORMAL';
                $device->status_label = '🟢 NORMAL';
                $device->status_class = 'normal';
                $device->status_icon = '✅';
            }
        }

        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        $logsInRange = SmokeLog::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();
        
        $chartLogs = $logsInRange->groupBy(function($log) {
            return Carbon::parse($log->created_at)->format('Y-m-d');
        })->map(function($logs) {
            return $logs->last();
        })->values();
        
        if ($chartLogs->isEmpty()) {
            $chartLogs = collect();
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dummyLog = new SmokeLog();
                $dummyLog->created_at = $date;
                $dummyLog->smoke_value = 0;
                $dummyLog->status = 'NORMAL';
                $dummyLog->is_filled = true;
                $chartLogs->push($dummyLog);
            }
        } else {
            $filledLogs = collect();
            $lastValue = 0;
            $lastStatus = 'NORMAL';
            
            $allDates = [];
            $current = clone $startDate;
            while ($current <= $endDate) {
                $dateKey = $current->format('Y-m-d');
                $allDates[$dateKey] = null;
                $current->addDay();
            }
            
            foreach ($chartLogs as $log) {
                $dateKey = Carbon::parse($log->created_at)->format('Y-m-d');
                $allDates[$dateKey] = $log;
            }
            
            foreach ($allDates as $dateKey => $log) {
                if ($log === null) {
                    $dummyLog = new SmokeLog();
                    $dummyLog->created_at = Carbon::parse($dateKey)->setTime(0, 0, 0);
                    $dummyLog->smoke_value = $lastValue;
                    $dummyLog->status = $lastStatus;
                    $dummyLog->is_filled = true;
                    $filledLogs->push($dummyLog);
                } else {
                    $filledLogs->push($log);
                    $lastValue = $log->smoke_value;
                    $lastStatus = $log->status;
                }
            }
            
            $chartLogs = $filledLogs;
        }

        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'desc');
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);
        
        $allowedSorts = ['id', 'smoke_value', 'status', 'created_at', 'updated_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }
        
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? strtolower($direction) : 'desc';

        $filterStatus = $request->input('status', '');

        // Ambil semua log sesuai filter status
        $allLogs = SmokeLog::with('device')
            ->whereIn('status', ['NORMAL', 'WARNING', 'DANGER']);
        
        if (!empty($filterStatus) && in_array($filterStatus, ['NORMAL', 'WARNING', 'DANGER'])) {
            $allLogs->where('status', $filterStatus);
        }
        
        // Tampilkan semua log tanpa filter perubahan
        $filteredLogs = $allLogs->orderBy($sort, $direction)->get();
        
        $totalFiltered = $filteredLogs->count();
        
        // Hitung statistik dari semua log yang ditampilkan
        $statsNormal = 0;
        $statsWarning = 0;
        $statsDanger = 0;
        
        foreach ($filteredLogs as $log) {
            if ($log->status == 'NORMAL') {
                $statsNormal++;
            } elseif ($log->status == 'WARNING') {
                $statsWarning++;
            } elseif ($log->status == 'DANGER') {
                $statsDanger++;
            }
        }
        
        $statsTotal = $statsNormal + $statsWarning + $statsDanger;
        
        $offset = ($page - 1) * $perPage;
        $paginatedLogs = $filteredLogs->slice($offset, $perPage)->values();
        
        $smokeLogs = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedLogs,
            $totalFiltered,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $onlineCount = $online;

        return view('smoke', compact(
            'devices',
            'totalDevice',
            'online',
            'offline',
            'normal',
            'warning',
            'danger',
            'onlineCount',
            'chartLogs',
            'smokeLogs',
            'perPage',
            'sort',
            'direction',
            'filterStatus',
            'statsNormal',
            'statsWarning',
            'statsDanger',
            'statsTotal'
        ));
    }
}