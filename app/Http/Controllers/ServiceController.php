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
        $allowedSorts = ['no', 'name', 'target', 'status', 'uptime', 'last_check', 'created_at', 'ssl_status', 'ssl_days_remaining'];
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
        // 📊 SSL STATISTICS
        // ============================================================
        $sslStats = $this->getSSLStatistics();

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
        ->when($sort === 'ssl_status', function($q) use ($direction) {
            return $q->orderByRaw("
                CASE ssl_status
                    WHEN 'VALID' THEN 1
                    WHEN 'WARNING' THEN 2
                    WHEN 'CRITICAL' THEN 3
                    WHEN 'EXPIRED' THEN 4
                    ELSE 5
                END " . $direction
            );
        })
        ->when($sort === 'ssl_days_remaining', function($q) use ($direction) {
            return $q->orderBy('ssl_days_remaining', $direction);
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
            
            // 🔥 Tambahkan informasi SSL yang diformat
            $service->ssl_info = $this->formatSSLInfoForDisplay($service);
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
            'showArchived',
            'sslStats'
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
            
            // 🔥 Tambahkan informasi SSL
            $sslDetails = $this->getSSLDetails($service);
            $sslFormatted = $this->formatSSLInfoForDisplay($service);

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
                        'ssl' => $sslDetails,
                    ]
                ]);
            }

            return view('services-detail', compact('service', 'sslDetails', 'sslFormatted'));

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

            // 🔥 SSL Statistics
            $sslStats = $this->getSSLStatistics();

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
                    'recent_issues' => $recentIssues,
                    'ssl_stats' => $sslStats,
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
                'recentIssues',
                'sslStats'
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
                    'Status Arsip',
                    'SSL Status',
                    'SSL Expiry Date',
                    'SSL Days Remaining',
                    'SSL Issuer',
                    'SSL Subject',
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
                        $service->is_archived ? 'DIARSIP' : 'AKTIF',
                        $service->ssl_status ?? '-',
                        $service->ssl_expiry_date ? Carbon::parse($service->ssl_expiry_date)->format('Y-m-d') : '-',
                        $service->ssl_days_remaining ?? '-',
                        $service->ssl_issuer ?? '-',
                        $service->ssl_subject ?? '-',
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
                    'last_checked' => $lastLog?->created_at?->diffForHumans(),
                    'ssl_status' => $service->ssl_status ?? 'N/A',
                    'ssl_days_remaining' => $service->ssl_days_remaining ?? '-',
                    'ssl_expiry_date' => $service->ssl_expiry_date 
                        ? Carbon::parse($service->ssl_expiry_date)->format('d-m-Y')
                        : '-',
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan status kesehatan: ' . $e->getMessage()
            ], 404);
        }
    }

    // ================================================================
    // 🔥 SSL RELATED METHODS
    // ================================================================

    /**
     * 🔒 Get SSL details for a service
     */
    public function getSSLDetails($service)
    {
        if (!$service) {
            return null;
        }

        // Cek apakah service menggunakan HTTPS
        $isHttps = str_starts_with($service->target, 'https://');
        
        if (!$isHttps && $service->type !== 'https') {
            return [
                'available' => false,
                'message' => 'SSL tidak tersedia untuk service ini (bukan HTTPS)',
                'status' => 'N/A',
                'status_icon' => '🔓',
                'status_label' => 'N/A',
            ];
        }

        return [
            'available' => true,
            'status' => $service->ssl_status ?? 'UNKNOWN',
            'status_icon' => $this->getSSLStatusIcon($service->ssl_status),
            'status_label' => $this->getSSLStatusLabel($service->ssl_status),
            'issuer' => $service->ssl_issuer ?? 'Unknown',
            'subject' => $service->ssl_subject ?? 'Unknown',
            'valid_from' => $service->ssl_valid_from ?? 'Unknown',
            'valid_to' => $service->ssl_expiry_date 
                ? Carbon::parse($service->ssl_expiry_date)->format('d-m-Y H:i:s')
                : 'Unknown',
            'days_remaining' => $service->ssl_days_remaining ?? 0,
            'is_expired' => $service->ssl_is_expired ?? false,
            'message' => $service->ssl_message ?? 'Belum ada informasi SSL',
            'checked_at' => $service->ssl_checked_at 
                ? Carbon::parse($service->ssl_checked_at)->format('d-m-Y H:i:s')
                : 'Belum diperiksa',
        ];
    }

    /**
     * 🎨 Get SSL status icon
     */
    private function getSSLStatusIcon($status)
    {
        return match($status) {
            'VALID' => '🟢',
            'WARNING' => '🟡',
            'CRITICAL' => '🔴',
            'EXPIRED' => '🔴',
            default => '❓',
        };
    }

    /**
     * 🏷️ Get SSL status label
     */
    private function getSSLStatusLabel($status)
    {
        return match($status) {
            'VALID' => 'VALID - Aman',
            'WARNING' => 'WARNING - Akan Expired',
            'CRITICAL' => 'CRITICAL - Segera Perbarui!',
            'EXPIRED' => 'EXPIRED - BERBAHAYA!',
            default => 'UNKNOWN',
        };
    }

    /**
     * 📋 Format SSL info for display in views
     */
    private function formatSSLInfoForDisplay($service)
    {
        $details = $this->getSSLDetails($service);
        
        if (!$details || !$details['available']) {
            return [
                'html' => '<span class="badge badge-secondary">🔓 Non-HTTPS</span>',
                'text' => 'Non-HTTPS',
                'status' => 'N/A',
                'days' => '-',
                'expiry' => '-',
                'tooltip' => 'Service ini tidak menggunakan HTTPS',
            ];
        }

        $days = $details['days_remaining'];
        $status = $details['status'];
        
        // Tentukan warna badge
        $badgeClass = match($status) {
            'VALID' => 'success',
            'WARNING' => 'warning',
            'CRITICAL' => 'danger',
            'EXPIRED' => 'danger',
            default => 'secondary',
        };

        // Tentukan teks untuk days
        $daysText = $days > 0 ? $days . ' hari' : ($days == 0 ? 'Hari ini' : 'EXPIRED!');

        return [
            'html' => '<span class="badge badge-' . $badgeClass . '" title="' . $details['status_label'] . ' - Exp: ' . $details['valid_to'] . '">' 
                . $details['status_icon'] . ' ' . $details['status'] . ' (' . $daysText . ')</span>',
            'text' => $details['status'] . ' (' . $daysText . ')',
            'status' => $status,
            'days' => $days,
            'expiry' => $details['valid_to'],
            'tooltip' => $details['status_label'] . ' - Expiry: ' . $details['valid_to'] . ' - Sisa: ' . $daysText,
            'icon' => $details['status_icon'],
            'badge_class' => $badgeClass,
        ];
    }

    /**
     * 📊 Get SSL statistics for dashboard
     */
    public function getSSLStatistics()
    {
        $services = Service::where('is_archived', 0)->get();
        
        $stats = [
            'total' => $services->count(),
            'https' => 0,
            'non_https' => 0,
            'valid' => 0,
            'warning' => 0,
            'critical' => 0,
            'expired' => 0,
            'unknown' => 0,
            'expiring_soon' => 0, // 7 hari
            'expiring_soon_list' => [],
        ];

        foreach ($services as $service) {
            if (str_starts_with($service->target, 'https://') || $service->type === 'https') {
                $stats['https']++;
                
                $status = $service->ssl_status ?? 'UNKNOWN';
                
                if ($status === 'VALID') {
                    $stats['valid']++;
                } elseif ($status === 'WARNING') {
                    $stats['warning']++;
                } elseif ($status === 'CRITICAL') {
                    $stats['critical']++;
                } elseif ($status === 'EXPIRED') {
                    $stats['expired']++;
                } else {
                    $stats['unknown']++;
                }

                // Cek SSL yang akan expired dalam 7 hari
                if ($service->ssl_days_remaining !== null && $service->ssl_days_remaining <= 7 && $service->ssl_days_remaining > 0) {
                    $stats['expiring_soon']++;
                    $stats['expiring_soon_list'][] = [
                        'name' => $service->name,
                        'days' => $service->ssl_days_remaining,
                        'expiry' => $service->ssl_expiry_date 
                            ? Carbon::parse($service->ssl_expiry_date)->format('d-m-Y')
                            : 'Unknown',
                    ];
                }
            } else {
                $stats['non_https']++;
            }
        }

        return $stats;
    }

    /**
     * 🔄 Refresh SSL for a specific service
     */
    public function refreshSSL($id, ServiceMonitorService $monitor)
    {
        try {
            $service = Service::findOrFail($id);
            
            // Get SSL info using the monitor service
            $sslInfo = $monitor->getSSLInfo($service);
            
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
                
                Log::info("🔄 SSL refreshed for service: {$service->name}");
                
                if (request()->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'SSL berhasil di-refresh',
                        'data' => $this->getSSLDetails($service)
                    ]);
                }
                
                return redirect()->back()->with('success', 'SSL certificate berhasil di-refresh!');
            } else {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Service ini bukan HTTPS atau SSL tidak tersedia'
                    ], 400);
                }
                return redirect()->back()->with('warning', 'Service ini bukan HTTPS atau SSL tidak tersedia');
            }
            
        } catch (\Exception $e) {
            Log::error("❌ Error refreshing SSL: " . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal refresh SSL: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Gagal refresh SSL: ' . $e->getMessage());
        }
    }

    /**
     * 🔄 Refresh all SSL certificates
     */
    public function refreshAllSSL(ServiceMonitorService $monitor)
    {
        try {
            $services = Service::where('is_archived', 0)->get();
            $updated = 0;
            
            foreach ($services as $service) {
                if (str_starts_with($service->target, 'https://') || $service->type === 'https') {
                    $sslInfo = $monitor->getSSLInfo($service);
                    
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
            }
            
            Log::info("🔄 All SSL certificates refreshed: {$updated} services updated");
            
            return redirect()->back()->with('success', "Berhasil refresh SSL untuk {$updated} service!");
            
        } catch (\Exception $e) {
            Log::error("❌ Error refreshing all SSL: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal refresh semua SSL: ' . $e->getMessage());
        }
    }

    /**
     * 📊 Get SSL status for all services (AJAX)
     */
    public function getSSLStatus(Request $request)
    {
        try {
            $services = Service::where('is_archived', 0)
                ->select('id', 'name', 'target', 'type', 'ssl_status', 'ssl_days_remaining', 'ssl_expiry_date', 'ssl_issuer')
                ->get()
                ->map(function($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'target' => $service->target,
                        'type' => $service->type,
                        'ssl' => $this->getSSLDetails($service),
                        'display' => $this->formatSSLInfoForDisplay($service),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $services,
                'statistics' => $this->getSSLStatistics(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil status SSL: ' . $e->getMessage()
            ], 500);
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
                $service->ssl_info = $this->formatSSLInfoForDisplay($service);
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
                ->select('id', 'last_status', 'last_check_at', 'ssl_status', 'ssl_days_remaining')
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
                            : '-',
                        'ssl_status' => $service->ssl_status ?? 'N/A',
                        'ssl_days' => $service->ssl_days_remaining ?? '-',
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
    // 🔥🔥🔥 DOWNLOAD SINGLE REPORT
    // ================================================================

    /**
     * 📥 DOWNLOAD SINGLE SERVICE REPORT (PDF)
     * Route: GET /services/{id}/download-report
     */
    public function downloadReport(Request $request, $id)
    {
        try {
            $service = Service::findOrFail($id);
            
            // Ambil parameter date_from dan date_to dari request
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $format = $request->input('format', 'pdf');
            
            // Query logs
            $query = ServiceLog::where('service_id', $id);
            
            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            
            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }
            
            $logs = $query->orderBy('created_at', 'desc')->get();
            
            // ============================================================
            // HITUNG STATISTIK
            // ============================================================
            $totalChecks = $logs->count();
            $upCount = $logs->where('status', 'UP')->count();
            $downCount = $logs->where('status', 'DOWN')->count();
            $warningCount = $logs->where('status', 'WARNING')->count();
            
            $uptime = $totalChecks > 0 ? round(($upCount / $totalChecks) * 100, 2) : 0;
            $downPct = $totalChecks > 0 ? round(($downCount / $totalChecks) * 100, 2) : 0;
            $warningPct = $totalChecks > 0 ? round(($warningCount / $totalChecks) * 100, 2) : 0;
            
            // Average Response Time
            $avgResponseTime = $logs->avg('response_time');
            $avgResponseTime = $avgResponseTime ? number_format($avgResponseTime, 2) : 0;
            
            // ============================================================
            // TANGGAL KRITIS (Critical Dates)
            // ============================================================
            $criticalDates = [];
            $groupedByDate = $logs->groupBy(function($log) {
                return $log->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d');
            });
            
            foreach ($groupedByDate as $date => $dayLogs) {
                $dayUp = $dayLogs->where('status', 'UP')->count();
                $dayDown = $dayLogs->where('status', 'DOWN')->count();
                $dayWarning = $dayLogs->where('status', 'WARNING')->count();
                $dayTotal = $dayLogs->count();
                $dayUptime = $dayTotal > 0 ? round(($dayUp / $dayTotal) * 100, 2) : 0;
                
                // Hanya tampilkan jika ada DOWN atau WARNING
                if ($dayDown > 0 || $dayWarning > 0) {
                    $criticalDates[$date] = [
                        'date' => $date,
                        'uptime' => $dayUptime,
                        'down_count' => $dayDown,
                        'warning_count' => $dayWarning,
                        'total_checks' => $dayTotal,
                    ];
                }
            }
            
            // Urutkan dari tanggal terbaru
            krsort($criticalDates);
            
            // ============================================================
            // FORMAT LOGS UNTUK VIEW
            // ============================================================
            $formattedLogs = $logs->take(100)->map(function($log) {
                return [
                    'date' => $log->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
                    'status' => $log->status ?? 'UNKNOWN',
                    'response_code' => $log->response_code ?? '-',
                    'response_time' => $log->response_time ? number_format($log->response_time, 2) : '-',
                    'message' => $log->message ?? '-',
                ];
            });
            
            // ============================================================
            // BUILD REPORT DATA
            // ============================================================
            $reportData = [
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'target' => $service->target,
                    'type' => $service->type ?? 'http',
                    'last_status' => $service->last_status ?? 'UNKNOWN',
                    'created_at' => $service->created_at ? $service->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') : 'N/A',
                ],
                'period' => [
                    'date_from' => $dateFrom ? Carbon::parse($dateFrom)->format('d/m/Y') : 'Semua',
                    'date_to' => $dateTo ? Carbon::parse($dateTo)->format('d/m/Y') : 'Sekarang',
                    'total_days' => $dateFrom && $dateTo ? Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1 : '-',
                ],
                'statistics' => [
                    'total_checks' => $totalChecks,
                    'up_count' => $upCount,
                    'down_count' => $downCount,
                    'warning_count' => $warningCount,
                    'uptime_percentage' => $uptime,
                    'down_percentage' => $downPct,
                    'warning_percentage' => $warningPct,
                    'avg_response_time' => $avgResponseTime,
                ],
                'critical_dates' => $criticalDates,
                'logs' => $formattedLogs,
            ];
            
            // ============================================================
            // GENERATE PDF
            // ============================================================
            if (!class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
                throw new \Exception('DomPDF tidak terinstall. Jalankan: composer require barryvdh/laravel-dompdf');
            }
            
            $pdf = Pdf::loadView('reports.service-pdf', compact('reportData'));
            $pdf->setPaper('A4', 'portrait');
            
            $filename = 'laporan_' . str_replace(' ', '_', $service->name) . '_' . date('Y-m-d') . '.pdf';
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('❌ Error download report: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal download laporan: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Gagal download laporan: ' . $e->getMessage());
        }
    }
    // ================================================================
    // 🔄 UPDATE WA INTERVAL
    // ================================================================

    /**
     * 🔄 UPDATE WA INTERVAL FOR SERVICE
     * Route: POST /services/{id}/wa-interval
     */
    public function updateWaInterval(Request $request, $id)
    {
        try {
            $request->validate([
                'wa_interval' => 'required|integer|min:0|max:1440',
            ]);
            
            $service = Service::findOrFail($id);
            $service->update([
                'wa_interval_minutes' => $request->wa_interval,
            ]);
            
            Log::info("📝 WA Interval updated for {$service->name}: {$request->wa_interval} minutes");
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Interval WA berhasil diupdate',
                    'data' => [
                        'wa_interval' => $service->wa_interval_minutes,
                    ]
                ]);
            }
            
            return redirect()->back()->with('success', 'Interval WA berhasil diupdate!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors());
            
        } catch (\Exception $e) {
            Log::error('❌ Error update WA interval: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal update interval: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Gagal update interval: ' . $e->getMessage());
        }
    }

    // ================================================================
    // 🔥🔥🔥 DOWNLOAD MULTI REPORT
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
                    'ssl_info' => $this->getSSLDetails($service),
                    'ssl_display' => $this->formatSSLInfoForDisplay($service),
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
                $sslInfo = $data['ssl_info'] ?? null;

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

                // Informasi SSL
                if ($sslInfo && $sslInfo['available']) {
                    fputcsv($file, [
                        'SSL Status: ' . $sslInfo['status_label'] ?? 'N/A',
                        'SSL Issuer: ' . ($sslInfo['issuer'] ?? '-'),
                        'SSL Subject: ' . ($sslInfo['subject'] ?? '-'),
                        'SSL Expiry: ' . ($sslInfo['valid_to'] ?? '-'),
                        'SSL Days Remaining: ' . ($sslInfo['days_remaining'] ?? '-') . ' hari',
                    ]);
                } else {
                    fputcsv($file, [
                        'SSL Status: Non-HTTPS / Tidak tersedia',
                    ]);
                }

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