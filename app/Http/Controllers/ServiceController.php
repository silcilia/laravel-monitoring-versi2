<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceLog;
use Illuminate\Http\Request;
use App\Services\ServiceMonitorService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ServiceController extends Controller
{
    /**
     * Display a listing of the services.
     */
    public function index(Request $request)
    {
        // 🔥 AMBIL INTERVAL WA DARI REQUEST ATAU SESSION (DEFAULT 5)
        $waInterval = $request->input('wa_interval', session('wa_interval', 5));
        
        // 🔥 SIMPAN KE SESSION DAN DATABASE
        if ($request->has('wa_interval')) {
            session(['wa_interval' => $waInterval]);
            Service::query()->update(['wa_interval_minutes' => $waInterval]);
            Log::info("📝 WA Interval updated to {$waInterval} minutes for all services");
        }

        // ============================================================
        // 🔥 SORTING PARAMETERS
        // ============================================================
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $perPage = $request->input('perPage', 10);
        
        // 🔥 PARAMETER SHOW ARCHIVED
        $showArchived = $request->input('show_archived', false);
        
        // ✅ Validasi kolom yang boleh di-sort
        $allowedSorts = ['no', 'name', 'target', 'status', 'uptime', 'last_check', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }
        
        // ✅ Validasi direction
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? strtolower($direction) : 'asc';
        
        // ============================================================
        // 📊 STATISTICS - HANYA SERVICE AKTIF (TIDAK DIARSIP)
        // ============================================================
        $totalServices = Service::where('is_archived', 0)->count();
        $totalArchived = Service::where('is_archived', 1)->count();
        $totalActive = $totalServices;
        
        $totalUp = Service::where('last_status', 'UP')->where('is_archived', 0)->count();
        $totalWarning = Service::where('last_status', 'WARNING')->where('is_archived', 0)->count();
        $totalDown = Service::where('last_status', 'DOWN')->where('is_archived', 0)->count();

        // ============================================================
        // 🔍 QUERY WITH SORTING & ARCHIVE FILTER
        // ============================================================
        $query = Service::query();
        
        if ($showArchived) {
            $query->where('is_archived', 1);
        } else {
            $query->where('is_archived', 0);
        }
        
        $query->when($sort === 'name', function($q) use ($direction) {
            return $q->orderBy('name', $direction);
        })
        ->when($sort === 'target', function($q) use ($direction) {
            return $q->orderBy('target', $direction);
        })
        ->when($sort === 'status', function($q) use ($direction) {
            return $q->orderByRaw("
                CASE last_status
                    WHEN 'UP' THEN 1
                    WHEN 'WARNING' THEN 2
                    WHEN 'DOWN' THEN 3
                    ELSE 4
                END " . $direction
            );
        })
        ->when($sort === 'uptime', function($q) use ($direction) {
            return $q->orderBy('uptime', $direction);
        })
        ->when($sort === 'last_check', function($q) use ($direction) {
            return $q->orderBy('last_check_at', $direction);
        })
        ->when($sort === 'created_at' || $sort === 'no', function($q) use ($direction) {
            return $q->orderBy('id', $direction);
        });
        
        $services = $query->paginate($perPage)->appends([
            'perPage' => $perPage,
            'sort' => $sort,
            'direction' => $direction,
            'wa_interval' => $waInterval,
            'show_archived' => $showArchived
        ]);
        
        foreach ($services as $service) {
            $service->uptime = $this->calculateUptime($service->id, 30);
        }
        
        return view('services', compact(
            'services', 
            'totalServices',
            'totalActive',
            'totalArchived',
            'totalUp', 
            'totalWarning', 
            'totalDown',
            'perPage',
            'waInterval',
            'sort',
            'direction',
            'showArchived'
        ));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create()
    {
        return redirect()->route('services');
    }

    /**
     * 🔥 PERBAIKI: Fix target berdasarkan tipe
     */
    private function fixTarget($target, $type)
    {
        if ($type === 'ping') {
            return trim($target);
        }
        
        if (!preg_match('/^https?:\/\/.+/', $target)) {
            return 'https://' . $target;
        }
        return $target;
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request, ServiceMonitorService $monitor)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'type' => ['required', Rule::in(['http', 'https', 'ping', 'port'])],
                'wa_interval' => 'nullable|integer|min:0|max:1440',
            ];

            if (in_array($request->type, ['http', 'https', 'port'])) {
                $rules['target'] = [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        if (!preg_match('/^https?:\/\/.+/', $value)) {
                            $fail('Format URL tidak valid. Harus diawali dengan http:// atau https://');
                        }
                    },
                ];
            } else if ($request->type === 'ping') {
                $rules['target'] = [
                    'required',
                    'string',
                    'max:255',
                ];
            }

            $validated = $request->validate($rules);
            $validated['target'] = $this->fixTarget($validated['target'], $validated['type']);

            $existingTarget = Service::where('target', $validated['target'])->first();
            if ($existingTarget) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Target "' . $validated['target'] . '" sudah digunakan oleh service "' . $existingTarget->name . '"');
            }

            $existingName = Service::where('name', $validated['name'])->first();
            if ($existingName) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Nama service "' . $validated['name'] . '" sudah digunakan');
            }

            $service = Service::create([
                'name' => $validated['name'],
                'target' => $validated['target'],
                'type' => $validated['type'],
                'last_status' => 'UNKNOWN',
                'wa_interval_minutes' => $validated['wa_interval'] ?? 5,
                'is_archived' => 0,
            ]);

            $monitor->check($service);

            return redirect()
                ->route('services')
                ->with('success', 'Service "' . $service->name . '" berhasil ditambahkan');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $service
            ]);
        }
        
        return redirect()->route('services');
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, $id, ServiceMonitorService $monitor)
    {
        try {
            $service = Service::findOrFail($id);

            $rules = [
                'name' => 'required|string|max:255',
                'type' => ['required', Rule::in(['http', 'https', 'ping', 'port'])],
                'wa_interval' => 'nullable|integer|min:0|max:1440',
            ];

            if (in_array($request->type, ['http', 'https', 'port'])) {
                $rules['target'] = [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        if (!preg_match('/^https?:\/\/.+/', $value)) {
                            $fail('Format URL tidak valid. Harus diawali dengan http:// atau https://');
                        }
                    },
                ];
            } else if ($request->type === 'ping') {
                $rules['target'] = [
                    'required',
                    'string',
                    'max:255',
                ];
            }

            $validated = $request->validate($rules);
            $validated['target'] = $this->fixTarget($validated['target'], $validated['type']);

            $existingTarget = Service::where('target', $validated['target'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingTarget) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Target "' . $validated['target'] . '" sudah digunakan oleh service "' . $existingTarget->name . '"');
            }

            $existingName = Service::where('name', $validated['name'])
                ->where('id', '!=', $id)
                ->first();
            if ($existingName) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Nama service "' . $validated['name'] . '" sudah digunakan');
            }

            $service->update([
                'name' => $validated['name'],
                'target' => $validated['target'],
                'type' => $validated['type'],
                'wa_interval_minutes' => $validated['wa_interval'] ?? $service->wa_interval_minutes,
            ]);

            $monitor->check($service);

            return redirect()
                ->route('services')
                ->with('success', 'Service "' . $service->name . '" berhasil diupdate');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $service = Service::findOrFail($id);
            $serviceName = $service->name;
            $service->delete();

            return redirect()
                ->route('services')
                ->with('success', 'Service "' . $serviceName . '" berhasil dihapus');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ================================================================
    // 🔥📦 ARCHIVE & RESTORE METHODS
    // ================================================================

    public function archive($id)
    {
        try {
            $service = Service::findOrFail($id);
            
            if ($service->is_archived) {
                return redirect()->back()->with('warning', 'Service "' . $service->name . '" sudah diarsipkan');
            }
            
            $service->update([
                'is_archived' => 1,
                'archived_at' => now(),
            ]);
            
            Log::info("📦 Service diarsipkan: {$service->name} (ID: {$service->id})");
            
            return redirect()->back()->with('success', 'Service "' . $service->name . '" berhasil diarsipkan');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengarsipkan service: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $service = Service::findOrFail($id);
            
            if (!$service->is_archived) {
                return redirect()->back()->with('warning', 'Service "' . $service->name . '" tidak dalam status arsip');
            }
            
            $service->update([
                'is_archived' => 0,
                'archived_at' => null,
            ]);
            
            Log::info("🔄 Service dipulihkan: {$service->name} (ID: {$service->id})");
            
            return redirect()->back()->with('success', 'Service "' . $service->name . '" berhasil dipulihkan');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memulihkan service: ' . $e->getMessage());
        }
    }

    public function destroyPermanent($id)
    {
        try {
            $service = Service::findOrFail($id);
            
            if (!$service->is_archived) {
                return redirect()->back()->with('error', 'Service harus diarsipkan terlebih dahulu sebelum dihapus permanen');
            }
            
            $serviceName = $service->name;
            ServiceLog::where('service_id', $id)->delete();
            $service->delete();
            
            Log::info("🗑️ Service dihapus permanen: {$serviceName} (ID: {$id})");
            
            return redirect()->back()->with('success', 'Service "' . $serviceName . '" berhasil dihapus permanen');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus service: ' . $e->getMessage());
        }
    }

    // ================================================================
    // 🔥 DETAIL SERVICE
    // ================================================================

    public function detail($id)
    {
        try {
            $service = Service::findOrFail($id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $service->id,
                        'name' => $service->name,
                        'target' => $service->target,
                        'type' => $service->type,
                        'last_status' => $service->last_status ?? 'UNKNOWN',
                        'last_code' => $service->last_code ?? '-',
                        'last_response_time' => $service->last_response_time ?? 0,
                        'last_message' => $service->last_message ?? '-',
                        'last_action' => $service->last_action ?? '-',
                        'last_check_at' => $service->last_check_at 
                            ? $service->last_check_at->format('Y-m-d H:i:s') 
                            : null,
                        'created_at' => $service->created_at?->format('Y-m-d H:i:s'),
                        'updated_at' => $service->updated_at?->format('Y-m-d H:i:s'),
                    ]
                ]);
            }

            return view('services-detail', compact('service'));

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service tidak ditemukan atau terjadi kesalahan: ' . $e->getMessage()
                ], 404);
            }
            
            return redirect()
                ->route('services')
                ->with('error', 'Service tidak ditemukan');
        }
    }

    /**
     * Calculate uptime percentage for a specific service.
     */
    public function calculateUptime($serviceId, $days = 30)
    {
        $logs = ServiceLog::where('service_id', $serviceId)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $total = $logs->count();
        
        if ($total === 0) {
            $service = Service::find($serviceId);
            if ($service) {
                if ($service->last_status === 'UP') return 100.00;
                elseif ($service->last_status === 'WARNING') return 70.00;
                elseif ($service->last_status === 'DOWN') return 0.00;
            }
            return 0.00;
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
        
        $uptime = round($totalWeight / $total, 2);
        return max(0, min(100, $uptime));
    }

    /**
     * Get service logs history.
     */
    public function logs($id, Request $request)
    {
        try {
            $service = Service::findOrFail($id);
            
            $perPage = $request->input('perPage', 20);
            $status = $request->input('status');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $onlyChanges = $request->input('only_changes', false);

            $query = ServiceLog::where('service_id', $id);

            if ($status) {
                $query->where('status', $status);
            }

            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            if ($onlyChanges) {
                $query->where('is_status_change', true);
            }

            $logs = $query->latest()
                ->paginate($perPage)
                ->appends([
                    'perPage' => $perPage,
                    'status' => $status,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'only_changes' => $onlyChanges
                ]);

            $totalChanges = ServiceLog::where('service_id', $id)
                ->where('is_status_change', true)
                ->count();

            $changesLast7Days = ServiceLog::where('service_id', $id)
                ->where('is_status_change', true)
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $logs->items(),
                    'pagination' => [
                        'total' => $logs->total(),
                        'per_page' => $logs->perPage(),
                        'current_page' => $logs->currentPage(),
                        'last_page' => $logs->lastPage()
                    ],
                    'statistics' => [
                        'total_changes' => $totalChanges,
                        'changes_last_7_days' => $changesLast7Days,
                    ]
                ]);
            }

            return view('services-logs', compact(
                'service', 
                'logs', 
                'status', 
                'dateFrom', 
                'dateTo',
                'onlyChanges',
                'totalChanges',
                'changesLast7Days'
            ));

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data logs: ' . $e->getMessage()
                ], 404);
            }

            return redirect()
                ->route('services')
                ->with('error', 'Service tidak ditemukan');
        }
    }

    /**
     * 🔥 FORCE CHECK SERVICE
     */
    public function check($id, ServiceMonitorService $monitor)
    {
        try {
            $service = Service::findOrFail($id);
            
            if ($service->is_archived) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Service sedang diarsipkan, tidak dapat di-check'
                    ], 400);
                }
                return redirect()->back()->with('warning', 'Service "' . $service->name . '" sedang diarsipkan, tidak dapat di-check');
            }
            
            $monitor->check($service);
            $service->refresh();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Service "' . $service->name . '" berhasil di-check',
                    'data' => [
                        'status' => $service->last_status ?? 'UNKNOWN',
                        'response_code' => $service->last_code ?? 'N/A',
                        'response_time' => $service->last_response_time ?? 0,
                        'message' => $service->last_message ?? '-',
                        'checked_at' => $service->last_check_at 
                            ? $service->last_check_at->format('Y-m-d H:i:s') 
                            : '-'
                    ]
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Service "' . $service->name . '" berhasil di-check');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal check service: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Gagal check service: ' . $e->getMessage());
        }
    }

    /**
     * Get service status overview for dashboard.
     */
    public function overview()
    {
        try {
            $totalServices = Service::where('is_archived', 0)->count();
            $totalUp = Service::where('last_status', 'UP')->where('is_archived', 0)->count();
            $totalWarning = Service::where('last_status', 'WARNING')->where('is_archived', 0)->count();
            $totalDown = Service::where('last_status', 'DOWN')->where('is_archived', 0)->count();
            $totalUnknown = Service::where('last_status', 'UNKNOWN')->where('is_archived', 0)->count();
            $totalArchived = Service::where('is_archived', 1)->count();

            $servicesByType = Service::where('is_archived', 0)
                ->select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray();

            $recentIssues = ServiceLog::where('status', 'DOWN')
                ->orWhere('status', 'WARNING')
                ->with('service')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function($log) {
                    return [
                        'service' => $log->service->name ?? 'Unknown',
                        'status' => $log->status,
                        'message' => $log->message,
                        'time' => $log->created_at->diffForHumans()
                    ];
                });

            $response = [
                'success' => true,
                'data' => [
                    'total' => $totalServices,
                    'up' => $totalUp,
                    'warning' => $totalWarning,
                    'down' => $totalDown,
                    'unknown' => $totalUnknown,
                    'archived' => $totalArchived,
                    'uptime_percentage' => $totalServices > 0 
                        ? round(($totalUp / $totalServices) * 100, 2) 
                        : 0,
                    'services_by_type' => $servicesByType,
                    'recent_issues' => $recentIssues
                ]
            ];

            if (request()->ajax()) {
                return response()->json($response);
            }

            return view('dashboard-overview', compact(
                'totalServices',
                'totalUp',
                'totalWarning',
                'totalDown',
                'totalUnknown',
                'totalArchived',
                'servicesByType',
                'recentIssues'
            ));

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil overview: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->route('dashboard')
                ->with('error', 'Gagal mengambil data overview');
        }
    }

    /**
     * Export services data to CSV.
     */
    public function export()
    {
        try {
            $services = Service::with(['logs' => function($query) {
                $query->latest()->limit(1);
            }])->get();

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="services_' . date('Y-m-d') . '.csv"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function() use ($services) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");

                fputcsv($file, [
                    'ID',
                    'Nama Service',
                    'Target',
                    'Type',
                    'Status Terakhir',
                    'Response Code',
                    'Response Time (s)',
                    'Message',
                    'Terakhir Diperiksa',
                    'Dibuat Pada',
                    'Diupdate Pada',
                    'Status Arsip'
                ]);

                foreach ($services as $service) {
                    $latestLog = $service->logs->first();
                    fputcsv($file, [
                        $service->id,
                        $service->name,
                        $service->target,
                        $service->type ?? 'http',
                        $service->last_status ?? 'UNKNOWN',
                        $latestLog?->response_code ?? '-',
                        $latestLog?->response_time ? number_format($latestLog->response_time, 3) : '-',
                        $latestLog?->message ?? '-',
                        $latestLog?->created_at?->format('Y-m-d H:i:s') ?? '-',
                        $service->created_at?->format('Y-m-d H:i:s'),
                        $service->updated_at?->format('Y-m-d H:i:s'),
                        $service->is_archived ? 'DIARSIP' : 'AKTIF'
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete services.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada service yang dipilih'
                ], 400);
            }

            $deletedCount = Service::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deletedCount} service",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus service: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get service health status.
     */
    public function health($id)
    {
        try {
            $service = Service::findOrFail($id);
            
            $lastLog = $service->logs()->latest()->first();
            
            $healthScore = 100;
            if ($service->last_status == 'DOWN') {
                $healthScore = 0;
            } elseif ($service->last_status == 'WARNING') {
                $healthScore = 50;
            } elseif ($service->last_status == 'UP') {
                if ($lastLog && $lastLog->response_time > 3) {
                    $healthScore = 70;
                } elseif ($lastLog && $lastLog->response_time > 2) {
                    $healthScore = 85;
                } else {
                    $healthScore = 100;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'status' => $service->last_status ?? 'UNKNOWN',
                    'health_score' => $healthScore,
                    'response_time' => $lastLog?->response_time,
                    'last_checked' => $lastLog?->created_at?->diffForHumans()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan status kesehatan: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Download PDF report for a specific service.
     */
    public function downloadReport($id, Request $request)
    {
        try {
            if (!class_exists('Barryvdh\DomPDF\Facade\Pdf') && !class_exists('Barryvdh\DomPDF\PDF')) {
                throw new \Exception('DomPDF tidak terinstall. Jalankan: composer require barryvdh/laravel-dompdf');
            }

            $service = Service::findOrFail($id);
            
            $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->get('date_to', now()->format('Y-m-d'));

            // Ambil logs yang sudah difilter (hanya perubahan status)
            $logs = ServiceLog::where('service_id', $id)
                ->where('is_status_change', true)
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo)
                ->orderBy('created_at', 'desc')
                ->get();

            // Jika tidak ada log perubahan, ambil semua log (fallback)
            if ($logs->isEmpty()) {
                $logs = ServiceLog::where('service_id', $id)
                    ->whereDate('created_at', '>=', $dateFrom)
                    ->whereDate('created_at', '<=', $dateTo)
                    ->orderBy('created_at', 'desc')
                    ->limit(100)
                    ->get();
            }

            $reportData = $this->generateReportData($service, $logs, $dateFrom, $dateTo);

            return $this->exportPdfReport($reportData, $service);

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat laporan: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Gagal membuat laporan: ' . $e->getMessage());
        }
    }

    /**
     * Generate report data for a service.
     */
    private function generateReportData($service, $logs, $dateFrom, $dateTo)
    {
        $totalChecks = $logs->count();
        $upCount = $logs->where('status', 'UP')->count();
        $warningCount = $logs->where('status', 'WARNING')->count();
        $downCount = $logs->where('status', 'DOWN')->count();

        $avgResponseTime = $logs->avg('response_time');
        $maxResponseTime = $logs->max('response_time');
        $minResponseTime = $logs->min('response_time');

        // Hitung persentase
        $uptimePercentage = $totalChecks > 0 ? round(($upCount / $totalChecks) * 100, 2) : 0;
        $downPercentage = $totalChecks > 0 ? round(($downCount / $totalChecks) * 100, 2) : 0;
        $warningPercentage = $totalChecks > 0 ? round(($warningCount / $totalChecks) * 100, 2) : 0;

        // Log per tanggal untuk critical dates
        $logsByDate = $logs->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });

        $criticalDates = [];
        $logsByDate->each(function($dateLogs, $date) use (&$criticalDates) {
            $total = $dateLogs->count();
            $up = $dateLogs->where('status', 'UP')->count();
            $down = $dateLogs->where('status', 'DOWN')->count();
            $warning = $dateLogs->where('status', 'WARNING')->count();
            
            if ($down > 0 || $warning > 0) {
                $criticalDates[$date] = [
                    'total_checks' => $total,
                    'down_count' => $down,
                    'warning_count' => $warning,
                    'up_count' => $up,
                    'uptime' => $total > 0 ? round(($up / $total) * 100, 2) : 0,
                ];
            }
        });

        // Ambil 5 critical dates terbaru
        $criticalDates = array_slice($criticalDates, 0, 5, true);

        // Format logs untuk ditampilkan di PDF (hanya 50 log terbaru)
        $formattedLogs = $logs->take(50)->map(function($log) {
            return [
                'date' => $log->created_at->format('d/m/Y H:i:s'),
                'status' => $log->status ?? 'UNKNOWN',
                'response_code' => $log->response_code ?? '-',
                'response_time' => $log->response_time ? number_format($log->response_time, 3) : '-',
                'message' => $log->message ?? '-',
            ];
        });

        return [
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'target' => $service->target,
                'type' => $service->type,
                'last_status' => $service->last_status ?? 'UNKNOWN',
                'created_at' => $service->created_at?->format('d/m/Y H:i:s'),
            ],
            'period' => [
                'date_from' => date('d/m/Y', strtotime($dateFrom)),
                'date_to' => date('d/m/Y', strtotime($dateTo)),
                'total_days' => (new \DateTime($dateTo))->diff(new \DateTime($dateFrom))->days + 1
            ],
            'statistics' => [
                'total_checks' => $totalChecks,
                'up_count' => $upCount,
                'warning_count' => $warningCount,
                'down_count' => $downCount,
                'uptime_percentage' => $uptimePercentage,
                'down_percentage' => $downPercentage,
                'warning_percentage' => $warningPercentage,
                'avg_response_time' => $avgResponseTime ? number_format($avgResponseTime, 3) : '0.000',
                'max_response_time' => $maxResponseTime ? number_format($maxResponseTime, 3) : '0.000',
                'min_response_time' => $minResponseTime ? number_format($minResponseTime, 3) : '0.000',
            ],
            'critical_dates' => $criticalDates,
            'logs' => $formattedLogs,
        ];
    }

    /**
     * Export report as PDF.
     */
    private function exportPdfReport($reportData, $service)
    {
        try {
            $filename = 'laporan_' . str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $service->name) 
                . '_' . str_replace('/', '-', $reportData['period']['date_from']) . '_to_' . str_replace('/', '-', $reportData['period']['date_to']) . '.pdf';
            
            if (!view()->exists('reports.service-pdf')) {
                throw new \Exception('View reports.service-pdf tidak ditemukan. Buat file di resources/views/reports/service-pdf.blade.php');
            }

            $pdf = Pdf::loadView('reports.service-pdf', compact('reportData'));
            $pdf->setPaper('A4', 'portrait');
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            throw new \Exception('Gagal generate PDF: ' . $e->getMessage());
        }
    }

    // ================================================================
    // 🔍 SEARCH METHODS
    // ================================================================

    public function search(Request $request)
    {
        try {
            $query = $request->input('q', '');
            $perPage = $request->input('per_page', 10);
            
            if (empty($query)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'pagination' => [
                        'total' => 0,
                        'from' => 0,
                        'to' => 0,
                        'current_page' => 1,
                        'last_page' => 1,
                        'prev_page_url' => null,
                        'next_page_url' => null
                    ]
                ]);
            }
            
            $services = Service::where('name', 'LIKE', "%{$query}%")
                ->orWhere('target', 'LIKE', "%{$query}%")
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            foreach ($services as $service) {
                $service->uptime = $this->calculateUptime($service->id, 30);
            }
            
            return response()->json([
                'success' => true,
                'data' => $services->items(),
                'pagination' => [
                    'total' => $services->total(),
                    'from' => $services->firstItem(),
                    'to' => $services->lastItem(),
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'prev_page_url' => $services->previousPageUrl(),
                    'next_page_url' => $services->nextPageUrl()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function statusChanges($id, Request $request)
    {
        try {
            $service = Service::findOrFail($id);
            
            $days = $request->input('days', 7);
            
            $changes = ServiceLog::where('service_id', $id)
                ->where('is_status_change', true)
                ->where('created_at', '>=', now()->subDays($days))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($log) {
                    return [
                        'id' => $log->id,
                        'old_status' => $log->previous_status,
                        'new_status' => $log->status,
                        'changed_at' => $log->created_at->format('Y-m-d H:i:s'),
                        'message' => $log->message,
                    ];
                });
            
            $totalChanges = $changes->count();
            
            $statusCounts = [
                'UP_TO_DOWN' => $changes->where('old_status', 'UP')->where('new_status', 'DOWN')->count(),
                'DOWN_TO_UP' => $changes->where('old_status', 'DOWN')->where('new_status', 'UP')->count(),
                'UP_TO_WARNING' => $changes->where('old_status', 'UP')->where('new_status', 'WARNING')->count(),
                'WARNING_TO_UP' => $changes->where('old_status', 'WARNING')->where('new_status', 'UP')->count(),
                'WARNING_TO_DOWN' => $changes->where('old_status', 'WARNING')->where('new_status', 'DOWN')->count(),
                'DOWN_TO_WARNING' => $changes->where('old_status', 'DOWN')->where('new_status', 'WARNING')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'service' => [
                        'id' => $service->id,
                        'name' => $service->name,
                    ],
                    'period' => $days . ' days',
                    'total_changes' => $totalChanges,
                    'status_counts' => $statusCounts,
                    'changes' => $changes,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data perubahan status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📡 GET ALL SERVICES STATUS (AJAX POLLING)
     */
    public function getStatus(Request $request)
    {
        try {
            $services = Service::where('is_archived', 0)
                ->select('id', 'last_status', 'last_check_at')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function($service) {
                    return [
                        'id' => $service->id,
                        'last_status' => $service->last_status ?? 'UNKNOWN',
                        'last_check_at' => $service->last_check_at 
                            ? \Carbon\Carbon::parse($service->last_check_at)
                                ->setTimezone('Asia/Jakarta')
                                ->format('H:i:s') 
                            : '-'
                    ];
                });

            return response()->json([
                'success' => true,
                'services' => $services
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status: ' . $e->getMessage()
            ], 500);
        }
    }

    // ================================================================
    // 🔥🔥🔥 DOWNLOAD MULTI REPORT (BARU)
    // ================================================================

    /**
     * 📥 DOWNLOAD MULTI REPORT (PDF / EXCEL)
     * 🔗 POST /services/download-multi-report
     */
    public function downloadMultiReport(Request $request)
    {
        try {
            // 🔥 Ambil data dari request
            $servicesData = json_decode($request->input('services'), true);
            $format = $request->input('format', 'pdf');

            if (empty($servicesData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada service yang dipilih'
                ], 400);
            }

            // 🔥 Kumpulkan data per service
            $reportData = [];
            $totalServices = count($servicesData);

            foreach ($servicesData as $serviceData) {
                $serviceId = $serviceData['id'];
                $period = $serviceData['period'];
                $serviceName = $serviceData['name'];

                // Ambil service dari database
                $service = Service::find($serviceId);
                if (!$service) {
                    continue;
                }

                // Tentukan tanggal mulai
                $endDate = Carbon::now()->endOfDay();
                $startDate = Carbon::now()->startOfDay();

                if ($period === 'all') {
                    // Semua data dari tanggal service dibuat
                    $startDate = Carbon::parse($service->created_at)->startOfDay();
                } else {
                    // Periode tertentu (7, 14, 30, 60, 90 hari)
                    $days = (int) $period;
                    $startDate = Carbon::now()->subDays($days)->startOfDay();
                }

                // Ambil logs service
                $logs = ServiceLog::where('service_id', $service->id)
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<=', $endDate)
                    ->orderBy('created_at', 'asc')
                    ->get();

                // Hitung statistik
                $totalLogs = $logs->count();
                $upCount = $logs->where('status', 'UP')->count();
                $downCount = $logs->where('status', 'DOWN')->count();
                $warningCount = $logs->where('status', 'WARNING')->count();

                $uptime = $totalLogs > 0 ? round(($upCount / $totalLogs) * 100, 2) : 0;

                $reportData[] = [
                    'service' => $service,
                    'logs' => $logs,
                    'stats' => [
                        'total' => $totalLogs,
                        'up' => $upCount,
                        'down' => $downCount,
                        'warning' => $warningCount,
                        'uptime' => $uptime,
                    ],
                    'period' => [
                        'start' => $startDate->format('d/m/Y'),
                        'end' => $endDate->format('d/m/Y'),
                        'days' => $startDate->diffInDays($endDate) + 1,
                    ]
                ];
            }

            // 🔥 Generate berdasarkan format
            if ($format === 'excel') {
                return $this->exportMultiExcel($reportData);
            }

            // Default: PDF
            return $this->exportMultiPdf($reportData);

        } catch (\Exception $e) {
            Log::error('❌ Error download multi report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal download laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📄 EXPORT MULTI PDF
     */
    private function exportMultiPdf($reportData)
    {
        if (!class_exists('Barryvdh\DomPDF\Facade\Pdf') && !class_exists('Barryvdh\DomPDF\PDF')) {
            throw new \Exception('DomPDF tidak terinstall. Jalankan: composer require barryvdh/laravel-dompdf');
        }

        $data = [
            'reportData' => $reportData,
            'totalServices' => count($reportData),
            'generatedAt' => Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('reports.multi-report', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'laporan_service_' . date('Y-m-d_H-i-s') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * 📊 EXPORT MULTI EXCEL (CSV)
     */
    private function exportMultiExcel($reportData)
    {
        $filename = 'laporan_service_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($reportData) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM untuk UTF-8

            // Header utama
            fputcsv($file, [
                'LAPORAN SERVICE',
                'Tanggal: ' . Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
                'Total Service: ' . count($reportData),
                '',
            ]);

            fputcsv($file, []); // Baris kosong

            foreach ($reportData as $data) {
                $service = $data['service'];
                $stats = $data['stats'];
                $period = $data['period'];

                // Header per service
                fputcsv($file, [
                    '==================================================',
                ]);
                fputcsv($file, [
                    'SERVICE: ' . $service->name,
                ]);
                fputcsv($file, [
                    'Target: ' . $service->target,
                    'Tipe: ' . strtoupper($service->type ?? 'HTTP'),
                    'Status Terakhir: ' . ($service->last_status ?? 'UNKNOWN'),
                    'Uptime: ' . $stats['uptime'] . '%',
                ]);
                fputcsv($file, [
                    'Periode: ' . $period['start'] . ' - ' . $period['end'],
                    'Total Hari: ' . $period['days'] . ' hari',
                ]);
                fputcsv($file, [
                    'Total Check: ' . $stats['total'],
                    'UP: ' . $stats['up'],
                    'WARNING: ' . $stats['warning'],
                    'DOWN: ' . $stats['down'],
                ]);
                fputcsv($file, []);

                // Detail logs
                fputcsv($file, [
                    'Waktu',
                    'Status',
                    'Response Code',
                    'Response Time (s)',
                    'Message'
                ]);

                if ($data['logs']->isEmpty()) {
                    fputcsv($file, ['Tidak ada data log untuk periode ini', '', '', '', '']);
                } else {
                    foreach ($data['logs'] as $log) {
                        fputcsv($file, [
                            $log->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
                            $log->status ?? 'UNKNOWN',
                            $log->response_code ?? '-',
                            $log->response_time ? number_format($log->response_time, 2) : '-',
                            $log->message ?? '-',
                        ]);
                    }
                }

                fputcsv($file, []); // Baris kosong antar service
                fputcsv($file, []); // Baris kosong antar service
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}