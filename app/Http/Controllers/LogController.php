<?php

namespace App\Http\Controllers;

use App\Models\ServiceLog;
use App\Models\SmokeLog;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    /**
     * Display a listing of the logs.
     * ✅ DIPERBAIKI: HANYA LOG DARI SERVICE AKTIF (TIDAK DIARSIP)
     */
    public function index(Request $request)
    {
        // ============================================================
        // 🔥 AMBIL ID SERVICE YANG AKTIF (TIDAK DIARSIP)
        // ============================================================
        $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();

        // ============================================================
        // 🔥 QUERY LOGS - HANYA DARI SERVICE AKTIF
        // ============================================================
        $query = ServiceLog::with('service')
            ->whereIn('service_id', $activeServiceIds);
        
        // Filter berdasarkan service
        if ($request->has('service_id') && $request->service_id) {
            $query->where('service_id', $request->service_id);
        }
        
        // Filter berdasarkan status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan tanggal
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Filter pencarian
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('message', 'LIKE', "%{$search}%")
                  ->orWhere('status', 'LIKE', "%{$search}%")
                  ->orWhere('response_code', 'LIKE', "%{$search}%")
                  ->orWhereHas('service', function($subq) use ($search) {
                      $subq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }
        
        // ============================================================
        // 🔥 STATISTIK - HANYA DARI SERVICE AKTIF
        // ============================================================
        $statsQuery = clone $query;
        $stats = [
            'total' => $statsQuery->count(),
            'up' => (clone $statsQuery)->where('status', 'UP')->count(),
            'warning' => (clone $statsQuery)->where('status', 'WARNING')->count(),
            'down' => (clone $statsQuery)->where('status', 'DOWN')->count(),
            'unknown' => (clone $statsQuery)->where('status', 'UNKNOWN')->count(),
        ];
        
        // ============================================================
        // 🔥 PAGINATION
        // ============================================================
        $perPage = $request->input('perPage', $request->input('per_page', 10));
        $perPage = (int) $perPage;
        
        if ($perPage < 1) {
            $perPage = 10;
        }
        
        if ($perPage > 100) {
            $perPage = 100;
        }
        
        $logs = $query->latest('created_at')
                     ->paginate($perPage)
                     ->withQueryString();
        
        // ============================================================
        // 🔥 AMBIL SERVICE UNTUK FILTER - HANYA YANG AKTIF
        // ============================================================
        $services = Service::where('is_archived', 0)->orderBy('name')->get();
        
        // ============================================================
        // 🔥 TAMBAHKAN TOTAL ARSIP UNTUK REFERENSI
        // ============================================================
        $totalArchived = Service::where('is_archived', 1)->count();
        
        return view('logs', compact('logs', 'stats', 'services', 'perPage', 'totalArchived'));
    }
    
    /**
     * Get statistics for logs.
     * ✅ DIPERBAIKI: HANYA DARI SERVICE AKTIF
     */
    private function getStats()
    {
        $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();
        
        return [
            'total' => ServiceLog::whereIn('service_id', $activeServiceIds)->count(),
            'up' => ServiceLog::whereIn('service_id', $activeServiceIds)->where('status', 'UP')->count(),
            'warning' => ServiceLog::whereIn('service_id', $activeServiceIds)->where('status', 'WARNING')->count(),
            'down' => ServiceLog::whereIn('service_id', $activeServiceIds)->where('status', 'DOWN')->count(),
            'unknown' => ServiceLog::whereIn('service_id', $activeServiceIds)->where('status', 'UNKNOWN')->count(),
            'today' => ServiceLog::whereIn('service_id', $activeServiceIds)->whereDate('created_at', today())->count(),
            'this_week' => ServiceLog::whereIn('service_id', $activeServiceIds)->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
        ];
    }
    
    /**
     * Get count of logs where status changed.
     * ✅ DIPERBAIKI: HANYA DARI SERVICE AKTIF
     */
    private function getStatusChangedCount()
    {
        $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();
        
        $logs = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->with('service')
            ->orderBy('service_id')
            ->orderBy('created_at', 'asc')
            ->get();
        
        $changes = 0;
        $lastStatusByService = [];
        
        foreach ($logs as $log) {
            $serviceId = $log->service_id;
            
            if (isset($lastStatusByService[$serviceId])) {
                if ($lastStatusByService[$serviceId] !== $log->status) {
                    $changes++;
                }
            }
            $lastStatusByService[$serviceId] = $log->status;
        }
        
        return $changes;
    }
    
    /**
     * Show specific log details.
     * ✅ DIPERBAIKI: CEK APAKAH SERVICE AKTIF
     */
    public function show($id)
    {
        $log = ServiceLog::with('service')->findOrFail($id);
        
        // 🔥 CEK APAKAH SERVICE LOG TERSEBUT AKTIF (TIDAK DIARSIP)
        if ($log->service && $log->service->is_archived) {
            abort(404, 'Log tidak ditemukan atau service telah diarsipkan');
        }
        
        $previousLog = ServiceLog::where('service_id', $log->service_id)
                               ->where('id', '<', $id)
                               ->latest()
                               ->first();
        
        $nextLog = ServiceLog::where('service_id', $log->service_id)
                           ->where('id', '>', $id)
                           ->oldest()
                           ->first();
        
        $statusHistory = ServiceLog::where('service_id', $log->service_id)
                                 ->select('status', 'created_at', 'id')
                                 ->orderBy('created_at', 'desc')
                                 ->limit(10)
                                 ->get();
        
        return view('logs-detail', compact(
            'log',
            'previousLog',
            'nextLog',
            'statusHistory'
        ));
    }
    
    /**
     * Get status change history for a service.
     * ✅ DIPERBAIKI: CEK APAKAH SERVICE AKTIF
     */
    public function getStatusHistory($serviceId)
    {
        // 🔥 CEK APAKAH SERVICE AKTIF
        $service = Service::find($serviceId);
        if (!$service || $service->is_archived) {
            return response()->json([
                'success' => false,
                'message' => 'Service tidak ditemukan atau telah diarsipkan'
            ], 404);
        }
        
        $logs = ServiceLog::where('service_id', $serviceId)
                         ->orderBy('created_at', 'asc')
                         ->get();
        
        $changes = [];
        $previousStatus = null;
        
        foreach ($logs as $log) {
            if ($previousStatus !== null && $previousStatus !== $log->status) {
                $changes[] = [
                    'from' => $previousStatus,
                    'to' => $log->status,
                    'changed_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'log_id' => $log->id,
                ];
            }
            $previousStatus = $log->status;
        }
        
        return response()->json([
            'success' => true,
            'data' => $changes,
            'total_changes' => count($changes),
        ]);
    }
    
    /**
     * Get latest status for each service.
     * ✅ DIPERBAIKI: HANYA DARI SERVICE AKTIF
     */
    public function getLatestStatuses()
    {
        $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();
        
        $latestLogs = ServiceLog::with('service')
                               ->whereIn('service_id', $activeServiceIds)
                               ->whereIn('id', function($query) {
                                   $query->select(DB::raw('MAX(id)'))
                                       ->from('service_logs')
                                       ->groupBy('service_id');
                               })
                               ->get();
        
        return response()->json([
            'success' => true,
            'data' => $latestLogs,
        ]);
    }
    
    /**
     * Export logs to CSV.
     * ✅ DIPERBAIKI: HANYA DARI SERVICE AKTIF
     */
    public function export(Request $request)
    {
        $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();
        
        $query = ServiceLog::with('service')
            ->whereIn('service_id', $activeServiceIds);
        
        if ($request->has('service_id') && $request->service_id) {
            $query->where('service_id', $request->service_id);
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'service_logs_' . now()->format('Ymd_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID',
                'Service Name',
                'Service ID',
                'Status',
                'Response Time (s)',
                'Response Code',
                'Message',
                'Created At',
                'Checked At'
            ]);
            
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->service->name ?? 'Unknown Service',
                    $log->service_id,
                    $log->status ?? 'UNKNOWN',
                    number_format($log->response_time ?? 0, 2),
                    $log->response_code ?? '-',
                    $log->message ?? '-',
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->checked_at ? $log->checked_at->format('Y-m-d H:i:s') : '-',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Delete old logs (bulk delete).
     * ✅ DIPERBAIKI: CEK APAKAH LOG DARI SERVICE AKTIF
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:service_logs,id',
        ]);
        
        // 🔥 HANYA HAPUS LOG DARI SERVICE AKTIF
        $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();
        
        $deleted = ServiceLog::whereIn('id', $request->ids)
            ->whereIn('service_id', $activeServiceIds)
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deleted} logs",
            'deleted_count' => $deleted,
        ]);
    }
    
    /**
     * Clear logs older than specified days.
     * ✅ DIPERBAIKI: HANYA DARI SERVICE AKTIF
     */
    public function clearOldLogs(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1',
        ]);
        
        $activeServiceIds = Service::where('is_archived', 0)->pluck('id')->toArray();
        $cutoffDate = now()->subDays($request->days);
        
        $deleted = ServiceLog::whereIn('service_id', $activeServiceIds)
            ->where('created_at', '<', $cutoffDate)
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deleted} logs older than {$request->days} days",
            'deleted_count' => $deleted,
            'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
        ]);
    }
}