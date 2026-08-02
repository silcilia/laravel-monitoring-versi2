<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Service extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'target',
        'type',
        'last_status',
        'last_code',
        'last_response_time',
        'last_message',
        'last_check_at',
        'last_wa_sent_at',
        'last_wa_status',
        'wa_interval_minutes',
        'last_interval_checked_at',
        'last_interval_status',
        'last_interval_value',
        'interval_wa_sent_in_this_cycle',
        'is_archived',
        'archived_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_check_at' => 'datetime',
        'last_wa_sent_at' => 'datetime',
        'last_response_time' => 'float',
        'wa_interval_minutes' => 'integer',
        'last_interval_checked_at' => 'datetime',
        'last_interval_status' => 'string',
        'last_interval_value' => 'integer',
        'interval_wa_sent_in_this_cycle' => 'boolean',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
    ];

    /**
     * 🔥 DEFAULT VALUES - PERBAIKAN UNTUK MENCEGAH NULL
     */
    protected $attributes = [
        'is_archived' => 0,
        'wa_interval_minutes' => 0,
        'interval_wa_sent_in_this_cycle' => false,
        'last_status' => 'UNKNOWN',
        'last_response_time' => 0,
        'last_message' => '-',
        'type' => 'http',
    ];

    /**
     * Get the logs for the service.
     */
    public function logs()
    {
        return $this->hasMany(ServiceLog::class);
    }

    // ================================================================
    // 🔥 SCOPES UNTUK ARSIP
    // ================================================================

    /**
     * Scope untuk service yang aktif (tidak diarsip)
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', 0);
    }

    /**
     * Scope untuk service yang diarsip
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', 1);
    }

    /**
     * Scope untuk service yang bisa di-check (aktif + tidak diarsip)
     */
    public function scopeCheckable($query)
    {
        return $query->where('is_archived', 0);
    }

    // ================================================================
    // 🔥 SCOPES UNTUK LOG PERUBAHAN STATUS
    // ================================================================

    /**
     * Scope untuk service yang memiliki perubahan status
     */
    public function scopeStatusChanges($query)
    {
        return $query->whereHas('logs', function($q) {
            $q->where('is_status_change', true);
        });
    }

    /**
     * Scope untuk service yang TIDAK memiliki perubahan status
     */
    public function scopeNoStatusChanges($query)
    {
        return $query->whereHas('logs', function($q) {
            $q->where('is_status_change', false);
        });
    }

    // ================================================================
    // 🔥 LOGIKA INTERVAL
    // ================================================================

    /**
     * 🔥 CEK APAKAH SUDAH MELEWATI INTERVAL
     */
    public function isIntervalReached(): bool
    {
        $interval = $this->wa_interval_minutes ?? 0;
        
        if ($interval <= 0) {
            return false;
        }

        if (empty($this->last_interval_checked_at)) {
            return true;
        }

        $minutesSinceLastCheck = $this->last_interval_checked_at->diffInMinutes(now());
        
        return $minutesSinceLastCheck >= $interval;
    }

    /**
     * 🔥 MULAI INTERVAL BARU
     */
    public function startNewInterval(string $currentStatus): void
    {
        $this->update([
            'last_interval_checked_at' => now(),
            'last_interval_status' => $currentStatus,
            'last_interval_value' => $this->wa_interval_minutes ?? 0,
            'interval_wa_sent_in_this_cycle' => false,
        ]);
    }

    /**
     * 🔥 TANDAI WA SUDAH TERKIRIM DI INTERVAL INI
     */
    public function markWaSentInThisCycle(): void
    {
        $this->update([
            'interval_wa_sent_in_this_cycle' => true,
        ]);
    }

    /**
     * 🔥 CEK APAKAH PERLU KIRIM WA (LOGIKA PER-SERVICE)
     */
    public function shouldSendWaByInterval(string $currentStatus): bool
    {
        $interval = $this->wa_interval_minutes ?? 0;
        
        if ($interval <= 0) {
            return false;
        }

        if ($currentStatus === 'UP') {
            if (empty($this->last_interval_checked_at)) {
                return true;
            }
            return false;
        }

        if (empty($this->last_interval_checked_at)) {
            return true;
        }

        if ($this->isIntervalReached() && !$this->interval_wa_sent_in_this_cycle) {
            return true;
        }

        if ($this->last_interval_status !== $currentStatus && 
            !$this->interval_wa_sent_in_this_cycle) {
            return true;
        }

        return false;
    }

    /**
     * 🔥 UPDATE WAKTU TERAKHIR KIRIM WA
     */
    public function updateLastWaSent($status)
    {
        $this->update([
            'last_wa_sent_at' => now(),
            'last_wa_status' => $status,
        ]);
    }

    // ================================================================
    // 🔥 METHOD UNTUK ARSIP
    // ================================================================

    /**
     * 🔥 CEK APAKAH SERVICE DIARSIP
     */
    public function isArchived(): bool
    {
        return (bool) ($this->is_archived ?? 0);
    }

    /**
     * 🔥 ARSIPKAN SERVICE
     */
    public function archive(): bool
    {
        if ($this->is_archived) {
            return false;
        }

        return $this->update([
            'is_archived' => 1,
            'archived_at' => now(),
        ]);
    }

    /**
     * 🔥 PULIHKAN SERVICE DARI ARSIP
     */
    public function restore(): bool
    {
        if (!$this->is_archived) {
            return false;
        }

        return $this->update([
            'is_archived' => 0,
            'archived_at' => null,
        ]);
    }

    /**
     * 🔥 HAPUS PERMANEN SERVICE (TERMASUK LOGS)
     */
    public function deletePermanently(): bool
    {
        if (!$this->is_archived) {
            return false;
        }

        // Hapus semua log terkait
        $this->logs()->delete();
        
        // Hapus service
        return $this->delete();
    }

    // ================================================================
    // 🔥 METHOD UNTUK LOG PERUBAHAN STATUS
    // ================================================================

    /**
     * 🔥 CEK APAKAH STATUS BERUBAH
     */
    public function hasStatusChanged(string $newStatus): bool
    {
        return ($this->last_status ?? 'UNKNOWN') !== $newStatus;
    }

    /**
     * 🔥 DAPATKAN STATUS SEBELUMNYA
     */
    public function getPreviousStatus(): string
    {
        return $this->last_status ?? 'UNKNOWN';
    }

    /**
     * 🔥 CATAT PERUBAHAN STATUS KE LOG
     */
    public function logStatusChange(string $newStatus, array $data = []): ServiceLog
    {
        $oldStatus = $this->getPreviousStatus();
        
        return ServiceLog::create([
            'service_id' => $this->id,
            'status' => $newStatus,
            'response_code' => $data['response_code'] ?? null,
            'response_time' => $data['response_time'] ?? 0,
            'message' => $data['message'] ?? 'Status berubah dari ' . $oldStatus . ' ke ' . $newStatus,
            'is_status_change' => true,
            'previous_status' => $oldStatus,
            'checked_at' => now(),
        ]);
    }

    /**
     * 🔥 UPDATE LOG TERAKHIR (TANPA BUAT LOG BARU)
     */
    public function updateLastLog(array $data = []): bool
    {
        $lastLog = $this->logs()->latest()->first();
        
        if (!$lastLog) {
            return false;
        }
        
        return $lastLog->update([
            'response_time' => $data['response_time'] ?? $lastLog->response_time,
            'response_code' => $data['response_code'] ?? $lastLog->response_code,
            'message' => $data['message'] ?? $lastLog->message,
            'checked_at' => now(),
        ]);
    }

    /**
     * 🔥 UPDATE SERVICE SETELAH CHECK (DENGAN LOGIKA PERUBAHAN)
     */
    public function updateAfterCheck(string $newStatus, array $data = []): array
    {
        $oldStatus = $this->getPreviousStatus();
        $isChanged = $this->hasStatusChanged($newStatus);
        
        // Data untuk update service
        $updateData = [
            'last_status' => $newStatus,
            'last_code' => $data['response_code'] ?? null,
            'last_response_time' => $data['response_time'] ?? 0,
            'last_message' => $data['message'] ?? '-',
            'last_check_at' => now(),
        ];
        
        // Update service
        $this->update($updateData);
        
        // LOGIKA LOG
        $logCreated = false;
        $logData = null;
        
        if ($isChanged) {
            // 🔥 STATUS BERUBAH → BUAT LOG BARU
            $logData = $this->logStatusChange($newStatus, $data);
            $logCreated = true;
            
            Log::info("📝 Log baru: Service {$this->name} status berubah {$oldStatus} → {$newStatus}");
        } else {
            // 🔥 STATUS TETAP → UPDATE LOG TERAKHIR
            $this->updateLastLog($data);
            
            Log::info("🔄 Status tetap: Service {$this->name} masih {$newStatus} (update waktu check)");
        }
        
        return [
            'is_changed' => $isChanged,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'log_created' => $logCreated,
            'log' => $logData,
        ];
    }

    // ================================================================
    // METHOD EXISTING (TIDAK BERUBAH)
    // ================================================================

    public function getUptime($days = 30)
    {
        $logs = $this->logs()
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $total = $logs->count();
        
        if ($total === 0) {
            if ($this->last_status === 'UP') return 100.00;
            elseif ($this->last_status === 'WARNING') return 70.00;
            elseif ($this->last_status === 'DOWN') return 0.00;
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

    public function getStatusInfo()
    {
        $status = $this->last_status ?? 'UNKNOWN';
        
        $statusMap = [
            'UP' => [
                'label' => 'UP',
                'class' => 'up',
                'color' => '#059669',
                'icon' => '✅'
            ],
            'DOWN' => [
                'label' => 'DOWN',
                'class' => 'down',
                'color' => '#dc2626',
                'icon' => '❌'
            ],
            'WARNING' => [
                'label' => 'WARNING',
                'class' => 'warning',
                'color' => '#d97706',
                'icon' => '⚠️'
            ],
            'UNKNOWN' => [
                'label' => 'UNKNOWN',
                'class' => 'unknown',
                'color' => '#94a3b8',
                'icon' => '❓'
            ]
        ];

        return $statusMap[$status] ?? $statusMap['UNKNOWN'];
    }

    public function isDown()
    {
        return $this->last_status === 'DOWN';
    }

    public function isUp()
    {
        return $this->last_status === 'UP';
    }

    public function isWarning()
    {
        return $this->last_status === 'WARNING';
    }

    public function getResponseTimeHuman()
    {
        if ($this->last_response_time === null) {
            return '-';
        }
        
        if ($this->last_response_time < 1) {
            return number_format($this->last_response_time * 1000, 0) . ' ms';
        }
        
        return number_format($this->last_response_time, 2) . ' s';
    }

    public function getLastCheckAtHuman()
    {
        if (!$this->last_check_at) {
            return '-';
        }
        
        return $this->last_check_at->setTimezone('Asia/Jakarta')->format('H:i:s');
    }

    public function getLastWaSentHuman()
    {
        if (!$this->last_wa_sent_at) {
            return 'Belum pernah';
        }
        
        return $this->last_wa_sent_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s');
    }

    public function getTimeSinceLastWa()
    {
        if (!$this->last_wa_sent_at) {
            return '-';
        }
        
        return $this->last_wa_sent_at->diffForHumans();
    }

    // ================================================================
    // 🔥 SCOPES EXISTING
    // ================================================================

    public function scopeStatus($query, $status)
    {
        return $query->where('last_status', $status);
    }

    public function scopeUp($query)
    {
        return $query->where('last_status', 'UP');
    }

    public function scopeDown($query)
    {
        return $query->where('last_status', 'DOWN');
    }

    public function scopeWarning($query)
    {
        return $query->where('last_status', 'WARNING');
    }

    public function scopeWaInterval($query, $minutes)
    {
        return $query->where('wa_interval_minutes', $minutes);
    }

    public function scopeNeverSentWa($query)
    {
        return $query->whereNull('last_wa_sent_at');
    }

    public function scopeReadyForWaReminder($query)
    {
        return $query->where('wa_interval_minutes', '>', 0)
            ->where(function ($q) {
                $q->whereNull('last_wa_sent_at')
                    ->orWhereRaw('TIMESTAMPDIFF(MINUTE, last_wa_sent_at, NOW()) >= wa_interval_minutes');
            });
    }

    public static function getStatistics()
    {
        $total = self::count();
        $up = self::up()->count();
        $down = self::down()->count();
        $warning = self::warning()->count();
        $unknown = $total - ($up + $down + $warning);

        return [
            'total' => $total,
            'up' => $up,
            'down' => $down,
            'warning' => $warning,
            'unknown' => $unknown,
            'uptime_percentage' => $total > 0 ? round(($up / $total) * 100, 2) : 0,
        ];
    }

    /**
     * 🔥 STATISTIK DENGAN FILTER ARSIP
     */
    public static function getStatisticsWithArchive()
    {
        $all = self::all();
        $total = $all->count();
        $archived = $all->where('is_archived', 1)->count();
        $active = $total - $archived;
        
        $up = self::where('last_status', 'UP')->where('is_archived', 0)->count();
        $down = self::where('last_status', 'DOWN')->where('is_archived', 0)->count();
        $warning = self::where('last_status', 'WARNING')->where('is_archived', 0)->count();
        $unknown = $active - ($up + $down + $warning);

        return [
            'total' => $total,
            'active' => $active,
            'archived' => $archived,
            'up' => $up,
            'down' => $down,
            'warning' => $warning,
            'unknown' => $unknown,
            'uptime_percentage' => $active > 0 ? round(($up / $active) * 100, 2) : 0,
        ];
    }
}