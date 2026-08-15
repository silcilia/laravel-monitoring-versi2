<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Monitoring Service</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', 'Arial', sans-serif;
            font-size: 12px;
            padding: 20px 25px;
            color: #1e293b;
            background: #ffffff;
        }

        /* ================= HEADER ================= */
        .header {
            text-align: center;
            border-bottom: 3px solid #0d3b66;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #0d3b66;
            margin: 0;
        }
        .header .subtitle {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }
        .header .subtitle span {
            background: #f1f5f9;
            padding: 2px 12px;
            border-radius: 10px;
            margin: 0 4px;
        }

        /* ================= RINGKASAN ================= */
        .summary-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .summary-section h2 {
            font-size: 16px;
            font-weight: 700;
            color: #0d3b66;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #e2e8f0;
        }

        .summary-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 20px;
            padding: 10px 16px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 12px;
        }
        .summary-stats .item {
            font-size: 12px;
            color: #475569;
        }
        .summary-stats .item .num {
            font-weight: 700;
            font-size: 14px;
        }
        .summary-stats .item .num.green { color: #059669; }
        .summary-stats .item .num.yellow { color: #d97706; }
        .summary-stats .item .num.red { color: #dc2626; }
        .summary-stats .item .num.blue { color: #2563eb; }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .summary-table th {
            background: #0d3b66;
            color: white;
            text-align: left;
            padding: 6px 10px;
            border: 1px solid #0d3b66;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .summary-table td {
            padding: 6px 10px;
            border: 1px solid #e2e8f0;
            font-size: 11px;
            color: #1e293b;
        }
        .summary-table tr:nth-child(even) {
            background: #f8fafc;
        }
        .summary-table .badge {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-up { background: #d1fae5; color: #065f46; }
        .badge-down { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-unknown { background: #e2e8f0; color: #475569; }

        /* ================= SERVICE CARD ================= */
        .service-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 20px;
            page-break-inside: avoid;
            overflow: hidden;
        }
        .service-card .card-header {
            background: #0d3b66;
            color: white;
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }
        .service-card .card-header .name {
            font-size: 15px;
            font-weight: 700;
        }
        .service-card .card-header .status-badge {
            padding: 2px 14px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-up { background: #10b981; color: white; }
        .status-down { background: #ef4444; color: white; }
        .status-warning { background: #f59e0b; color: #1e293b; }
        .status-unknown { background: #94a3b8; color: white; }

        .service-card .card-body {
            padding: 14px 16px;
            background: #fafbfc;
        }

        /* ================= INFO GRID ================= */
        .info-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 24px;
            margin-bottom: 10px;
        }
        .info-grid .item {
            font-size: 12px;
            color: #475569;
        }
        .info-grid .item strong {
            color: #1e293b;
        }

        /* ================= STATS ================= */
        .stats {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
        }
        .stats .stat {
            padding: 4px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background: white;
        }
        .stat-total { background: #eff6ff; color: #1e40af; border-color: #93c5fd; }
        .stat-up { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
        .stat-warning { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
        .stat-down { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
        .stat-uptime { background: #ede9fe; color: #5b21b6; border-color: #c4b5fd; }

        /* ================= TABLE LOG ================= */
        .table-wrapper {
            overflow-x: auto;
            margin-top: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table th {
            background: #f1f5f9;
            text-align: left;
            padding: 5px 10px;
            border: 1px solid #e2e8f0;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            font-size: 9px;
        }
        table td {
            padding: 4px 10px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
            color: #1e293b;
        }
        table tr:nth-child(even) {
            background: #f8fafc;
        }

        .status-cell {
            font-weight: 600;
        }
        .status-up { color: #059669; }
        .status-down { color: #dc2626; }
        .status-warning { color: #d97706; }
        .status-unknown { color: #94a3b8; }

        .no-data {
            text-align: center;
            padding: 15px;
            color: #94a3b8;
            font-style: italic;
        }

        /* ================= FOOTER ================= */
        .footer {
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            margin-top: 10px;
        }

        .page-break {
            page-break-after: always;
        }

        @media print {
            .page-break {
                page-break-after: always;
            }
            .service-card {
                border-color: #cbd5e1;
            }
            .service-card .card-header {
                background: #0d3b66 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .status-up, .status-down, .status-warning, .status-unknown,
            .badge-up, .badge-down, .badge-warning, .badge-unknown {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .stat-total, .stat-up, .stat-warning, .stat-down, .stat-uptime {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .summary-table th {
                background: #0d3b66 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <!-- ================= HEADER ================= -->
    <div class="header">
        <h1>LAPORAN MONITORING SERVICE</h1>
        <div class="subtitle">
            <span>Tanggal: {{ $generatedAt }}</span>
            <span>Total Service: {{ $totalServices }}</span>
        </div>
    </div>

    <!-- ================= RINGKASAN SEMUA SERVICE ================= -->
    @php
        $totalUp = 0;
        $totalDown = 0;
        $totalWarning = 0;
        $totalUnknown = 0;
        $totalLogs = 0;
        $avgUptime = 0;

        foreach($reportData as $data) {
            $stats = $data['stats'];
            $totalUp += $stats['up'];
            $totalDown += $stats['down'];
            $totalWarning += $stats['warning'];
            $totalLogs += $stats['total'];
            $avgUptime += $stats['uptime'];

            $status = strtolower($data['service']->last_status ?? 'unknown');
            if ($status == 'up') $totalUp++;
            elseif ($status == 'down') $totalDown++;
            elseif ($status == 'warning') $totalWarning++;
            else $totalUnknown++;
        }
        $avgUptime = $totalServices > 0 ? round($avgUptime / $totalServices, 2) : 0;
    @endphp

    <div class="summary-section">
        <h2>RINGKASAN</h2>

        <div class="summary-stats">
            <span class="item">Total Service: <span class="num blue">{{ $totalServices }}</span></span>
            <span class="item">Status UP: <span class="num green">{{ $totalUp }}</span></span>
            <span class="item">Status WARNING: <span class="num yellow">{{ $totalWarning }}</span></span>
            <span class="item">Status DOWN: <span class="num red">{{ $totalDown }}</span></span>
            <span class="item">Total Log: <span class="num blue">{{ $totalLogs }}</span></span>
            <span class="item">Rata-rata Uptime: <span class="num green">{{ $avgUptime }}%</span></span>
        </div>

        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width:5%;">No</th>
                    <th style="width:25%;">Nama Service</th>
                    <th style="width:20%;">Target</th>
                    <th style="width:10%;">Status</th>
                    <th style="width:8%;">UP</th>
                    <th style="width:8%;">WARNING</th>
                    <th style="width:8%;">DOWN</th>
                    <th style="width:10%;">Uptime</th>
                    <th style="width:6%;">Periode</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $index => $data)
                    @php
                        $service = $data['service'];
                        $stats = $data['stats'];
                        $period = $data['period'];
                        $status = strtolower($service->last_status ?? 'unknown');
                        $badgeClass = match($status) {
                            'up' => 'badge-up',
                            'down' => 'badge-down',
                            'warning' => 'badge-warning',
                            default => 'badge-unknown',
                        };
                        $statusLabel = strtoupper($service->last_status ?? 'UNKNOWN');
                        $uptimeColor = $stats['uptime'] >= 80 ? '#059669' : ($stats['uptime'] >= 50 ? '#d97706' : '#dc2626');
                    @endphp
                    <tr>
                        <td style="text-align:center;">{{ $index + 1 }}</td>
                        <td><strong>{{ $service->name }}</strong></td>
                        <td>{{ $service->target }}</td>
                        <td><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                        <td style="text-align:center;color:#059669;">{{ $stats['up'] }}</td>
                        <td style="text-align:center;color:#d97706;">{{ $stats['warning'] }}</td>
                        <td style="text-align:center;color:#dc2626;">{{ $stats['down'] }}</td>
                        <td style="text-align:center;font-weight:700;color:{{ $uptimeColor }};">
                            {{ $stats['uptime'] }}%
                        </td>
                        <td style="text-align:center;font-size:9px;">{{ $period['days'] }} hari</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ================= PAGE BREAK ================= -->
    <div class="page-break"></div>

    <!-- ================= DETAIL PER SERVICE ================= -->
    @foreach($reportData as $index => $data)
        @php
            $service = $data['service'];
            $stats = $data['stats'];
            $period = $data['period'];
            $status = strtolower($service->last_status ?? 'unknown');
            $badgeClass = match($status) {
                'up' => 'status-up',
                'down' => 'status-down',
                'warning' => 'status-warning',
                default => 'status-unknown',
            };
            $statusLabel = strtoupper($service->last_status ?? 'UNKNOWN');
            $uptimeColor = $stats['uptime'] >= 80 ? '#059669' : ($stats['uptime'] >= 50 ? '#d97706' : '#dc2626');
        @endphp

        @if($index > 0)
            <div class="page-break"></div>
        @endif

        <div class="service-card">
            <div class="card-header">
                <span class="name">{{ $index + 1 }}. {{ $service->name }}</span>
                <span class="status-badge {{ $badgeClass }}">Status: {{ $statusLabel }}</span>
            </div>

            <div class="card-body">
                <div class="info-grid">
                    <span class="item"><strong>Target:</strong> {{ $service->target }}</span>
                    <span class="item"><strong>Tipe:</strong> {{ strtoupper($service->type ?? 'HTTP') }}</span>
                    <span class="item"><strong>Periode:</strong> {{ $period['start'] }} - {{ $period['end'] }} ({{ $period['days'] }} hari)</span>
                    <span class="item"><strong>Uptime Rate:</strong> <span style="font-weight:700;color:{{ $uptimeColor }};">{{ $stats['uptime'] }}%</span></span>
                </div>

                <div class="stats">
                    <span class="stat stat-total">Total Log: {{ $stats['total'] }}</span>
                    <span class="stat stat-up">UP: {{ $stats['up'] }}x</span>
                    <span class="stat stat-warning">WARNING: {{ $stats['warning'] }}x</span>
                    <span class="stat stat-down">DOWN: {{ $stats['down'] }}x</span>
                    <span class="stat stat-uptime" style="color:{{ $uptimeColor }};">Uptime: {{ $stats['uptime'] }}%</span>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:22%;">Waktu</th>
                                <th style="width:12%;">Status</th>
                                <th style="width:14%;">Response Code</th>
                                <th style="width:12%;">Response Time</th>
                                <th style="width:40%;">Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data['logs']->take(10) as $log)
                                <tr>
                                    <td>{{ $log->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</td>
                                    <td class="status-cell status-{{ strtolower($log->status ?? 'unknown') }}">
                                        {{ $log->status ?? 'UNKNOWN' }}
                                    </td>
                                    <td>{{ $log->response_code ?? '-' }}</td>
                                    <td>{{ $log->response_time ? number_format($log->response_time, 2) . 's' : '-' }}</td>
                                    <td>{{ Str::limit($log->message ?? '-', 80) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="no-data">Tidak ada data log untuk periode ini</td>
                                </tr>
                            @endforelse
                            @if($data['logs']->count() > 10)
                                <tr>
                                    <td colspan="5" style="text-align:center;font-style:italic;color:#94a3b8;font-size:9px;">
                                        ... dan {{ $data['logs']->count() - 10 }} log lainnya (total {{ $data['logs']->count() }} log)
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

    <!-- ================= FOOTER ================= -->
    <div class="footer">
        Dicetak: {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>