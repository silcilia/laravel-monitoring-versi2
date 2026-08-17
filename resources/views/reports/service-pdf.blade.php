<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Monitoring Service - {{ $reportData['service']['name'] }}</title>
    <style>
        /* ============================================================
                   RESET & BASE
                   ============================================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 11px;
            padding: 20px 25px;
            color: #1a202c;
            background: #f8fafc;
            line-height: 1.5;
        }

        /* ============================================================
                   CARD
                   ============================================================ */
        .card {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px 24px;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        /* ============================================================
                   KOP SURAT / HEADER
                   ============================================================ */
        .kop-surat {
            text-align: center;
            padding: 8px 0 12px 0;
            border-bottom: 4px double #1a4d7a;
            margin-bottom: 14px;
        }

        .kop-surat .logo-title {
            font-size: 28px;
            font-weight: 900;
            color: #0a2e5c;
            letter-spacing: 4px;
            text-transform: uppercase;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .kop-surat .logo-title span {
            color: #3b82f6;
        }

        .kop-surat .sub-logo {
            font-size: 12px;
            color: #4a5568;
            letter-spacing: 2px;
            font-weight: 400;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .kop-surat .sub-logo strong {
            color: #1a4d7a;
            font-weight: 700;
        }

        .kop-surat .info-periode {
            font-size: 11px;
            color: #2d3748;
            margin-top: 6px;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .kop-surat .info-periode strong {
            color: #0a2e5c;
            font-weight: 700;
        }

        .kop-surat .info-periode .badge-header {
            display: inline-block;
            background: #1a365d;
            color: #60a5fa;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 700;
            margin-left: 6px;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .kop-surat .info-periode .printed {
            display: block;
            font-size: 9px;
            color: #94a3b8;
            margin-top: 2px;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        /* ============================================================
                   SECTION TITLE
                   ============================================================ */
        .section {
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #1a202c;
            padding: 6px 14px;
            margin-bottom: 10px;
            background: #dbeafe;
            border-left: 5px solid #3b82f6;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .section-title-sm {
            font-size: 11px;
            font-weight: 700;
            color: #1a202c;
            padding: 5px 14px;
            margin-bottom: 8px;
            background: #fee2e2;
            border-left: 5px solid #ef4444;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        /* ============================================================
                   DIVIDER
                   ============================================================ */
        .divider-gradient {
            border: none;
            height: 1px;
            background: linear-gradient(to right, transparent, #e2e8f0, transparent);
            margin: 12px 0;
        }

        /* ============================================================
                   INFO TABLE
                   ============================================================ */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #f0f4f8;
            font-size: 10px;
            vertical-align: middle;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .info-label {
            font-weight: 700;
            color: #4a5568;
            width: 120px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .info-value {
            color: #1a202c;
            font-weight: 500;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .info-value strong {
            color: #0a2e5c;
            font-weight: 700;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        /* ============================================================
                   STATUS BADGE
                   ============================================================ */
        .status-down {
            color: #9b2c2c;
            font-weight: 700;
            background: #fed7d7;
            padding: 2px 12px;
            border-radius: 12px;
            display: inline-block;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .status-up {
            color: #22543d;
            font-weight: 700;
            background: #c6f6d5;
            padding: 2px 12px;
            border-radius: 12px;
            display: inline-block;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .status-warning {
            color: #744210;
            font-weight: 700;
            background: #fefcbf;
            padding: 2px 12px;
            border-radius: 12px;
            display: inline-block;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .status-unknown {
            color: #2d3748;
            font-weight: 700;
            background: #e2e8f0;
            padding: 2px 12px;
            border-radius: 12px;
            display: inline-block;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        /* ============================================================
                   CHART / RINGKASAN EKSEKUTIF
                   ============================================================ */
        .chart-container {
            padding: 14px 16px;
            background: #f7fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .bar-track {
            width: 100%;
            height: 26px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #edf2f7;
            overflow: hidden;
        }

        .bar-track table {
            width: 100%;
            height: 26px;
            border-collapse: collapse;
        }

        .bar-track td {
            height: 26px;
            padding: 0;
        }

        .bar-up {
            background: #48bb78;
        }

        .bar-down {
            background: #fc8181;
        }

        .bar-warning {
            background: #f6ad55;
        }

        .legend-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .legend-table td {
            padding: 4px 6px;
            font-size: 10px;
            font-weight: 500;
            vertical-align: middle;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .color-box {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 4px;
            margin-right: 8px;
            vertical-align: middle;
        }

        .color-box.up {
            background: #38a169;
        }

        .color-box.down {
            background: #e53e3e;
        }

        .color-box.warning {
            background: #dd6b20;
        }

        .legend-percent {
            font-weight: 700;
            color: #2d3748;
            font-size: 11px;
            text-align: right;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .legend-rt-row td {
            border-top: 2px solid #e2e8f0;
            padding-top: 8px;
            font-size: 10px;
            color: #718096;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .legend-rt-row .legend-percent {
            font-size: 12px;
            color: #0a2e5c;
            font-weight: 800;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        /* ============================================================
                   BADGE
                   ============================================================ */
        .badge {
            display: inline-block;
            padding: 1px 10px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .badge-up {
            background: #c6f6d5;
            color: #22543d;
        }

        .badge-warning {
            background: #fefcbf;
            color: #744210;
        }

        .badge-down {
            background: #fed7d7;
            color: #9b2c2c;
        }

        .badge-unknown {
            background: #e2e8f0;
            color: #2d3748;
        }

        /* ============================================================
                   CRITICAL DATES
                   ============================================================ */
        .critical-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 4px;
        }

        .critical-item td {
            padding: 6px 14px;
            font-size: 10px;
            border-left: 5px solid;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .critical-item.danger td {
            background: #fff5f5;
            border-color: #fc8181;
        }

        .critical-item.warning td {
            background: #fffbeb;
            border-color: #f6ad55;
        }

        .critical-item .date {
            font-weight: 700;
            font-size: 10px;
            color: #2d3748;
            width: 30%;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .critical-item .status {
            font-size: 10px;
            color: #4a5568;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        /* ============================================================
                   LOG TABLE
                   ============================================================ */
        .table-responsive {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            overflow: hidden;
        }

        table.log-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }

        table.log-table thead th {
            background: #0a2e5c;
            color: #ffffff;
            padding: 6px 10px;
            text-align: left;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        table.log-table tbody td {
            padding: 5px 10px;
            border-bottom: 1px solid #f0f4f8;
            font-size: 8.5px;
            color: #2d3748;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        table.log-table tbody tr:last-child td {
            border-bottom: none;
        }

        table.log-table tbody tr.even-row {
            background: #fafcfd;
        }

        /* ============================================================
                   TEXT
                   ============================================================ */
        .text-muted {
            color: #718096;
            font-size: 8.5px;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .no-data {
            text-align: center;
            padding: 24px;
            color: #718096;
            font-style: italic;
            font-size: 10px;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .text-center {
            text-align: center;
        }

        /* ============================================================
                   FOOTER
                   ============================================================ */
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 12px;
            border-top: 2px solid #e2e8f0;
            font-size: 8.5px;
            color: #94a3b8;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        .footer strong {
            color: #0a2e5c;
            font-weight: 700;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        /* ============================================================
                   PAGE BREAK
                   ============================================================ */
        .page-break {
            page-break-before: always;
        }

        /* ============================================================
                   RESPONSIVE
                   ============================================================ */
        @media (max-width: 600px) {
            .info-table td {
                display: block;
                width: 100%;
                padding: 3px 8px;
            }

            .info-label {
                width: 100%;
                font-size: 8px;
            }

            .critical-item .date {
                width: 100%;
                display: block;
            }

            .critical-item td {
                padding: 5px 10px;
            }
        }
    </style>
</head>
<body>

    <div class="card">

        <!-- ============================================================
        KOP SURAT
        ============================================================ -->
        <div class="kop-surat">
            <div class="logo-title">
                MONITORING
            </div>
            <div class="sub-logo">
                <strong>SISTEM MONITORING</strong>
            </div>
            <div class="info-periode">
                <strong>{{ $reportData['service']['name'] }}</strong>
                &bull;
                {{ $reportData['period']['date_from'] }}
                &rarr;
                {{ $reportData['period']['date_to'] }}
                &bull;
                <strong>{{ number_format($reportData['statistics']['total_checks']) }}</strong> data
                <span class="badge-header">LIVE</span>
                <span class="printed">
                    Dicetak: {{ now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB
                </span>
            </div>
        </div>

        <!-- ============================================================
        INFORMASI SERVICE
        ============================================================ -->
        <div class="section">
            <div class="section-title">INFORMASI SERVICE</div>
            <table class="info-table">
                <tr>
                    <td class="info-label">Nama Service</td>
                    <td class="info-value"><strong>{{ $reportData['service']['name'] }}</strong></td>
                    <td class="info-label">Service Dibuat</td>
                    <td class="info-value">{{ $reportData['service']['created_at'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Target</td>
                    <td class="info-value">{{ $reportData['service']['target'] }}</td>
                    <td class="info-label">Periode Laporan</td>
                    <td class="info-value">
                        {{ $reportData['period']['date_from'] }}
                        &rarr;
                        {{ $reportData['period']['date_to'] }}
                        <span class="text-muted">({{ $reportData['period']['total_days'] }} hari)</span>
                    </td>
                </tr>
                <tr>
                    <td class="info-label">Tipe</td>
                    <td class="info-value"><strong>{{ strtoupper($reportData['service']['type']) }}</strong></td>
                    <td class="info-label">Status Terakhir</td>
                    <td class="info-value">
                        <span class="status-{{ strtolower($reportData['service']['last_status']) }}">
                            {{ $reportData['service']['last_status'] }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <hr class="divider-gradient">

        <!-- ============================================================
        RINGKASAN EKSEKUTIF
        ============================================================ -->
        <div class="section">
            <div class="section-title">RINGKASAN EKSEKUTIF</div>

            @php
                $upPct = $reportData['statistics']['uptime_percentage'] ?? 0;
                $downPct = $reportData['statistics']['down_percentage'] ?? 0;
                $warnPct = $reportData['statistics']['warning_percentage'] ?? 0;

                // Normalisasi: pastikan total = 100%
                $totalPct = $upPct + $downPct + $warnPct;
                if ($totalPct > 0 && $totalPct != 100) {
                    $diff = 100 - $totalPct;
                    $upPct = round($upPct + $diff, 2);
                }
            @endphp

            <div class="chart-container">
                <!-- BAR CHART -->
                <div class="bar-track">
                    <table>
                        <tr>
                            @if($upPct > 0)
                                <td class="bar-up" style="width: {{ $upPct }}%;"></td>
                            @endif
                            @if($downPct > 0)
                                <td class="bar-down" style="width: {{ $downPct }}%;"></td>
                            @endif
                            @if($warnPct > 0)
                                <td class="bar-warning" style="width: {{ $warnPct }}%;"></td>
                            @endif
                            @if($upPct <= 0 && $downPct <= 0 && $warnPct <= 0)
                                <td style="width: 100%; background: #e2e8f0; text-align: center; color: #718096; font-size: 9px; font-family: 'DejaVu Sans', 'Arial', sans-serif;">
                                    Tidak ada data
                                </td>
                            @endif
                        </tr>
                    </table>
                </div>

                <!-- LEGEND -->
                <table class="legend-table">
                    <tr>
                        <td><span class="color-box up"></span>UP</td>
                        <td class="legend-percent">{{ number_format($upPct, 2) }}%</td>
                    </tr>
                    <tr>
                        <td><span class="color-box down"></span>DOWN</td>
                        <td class="legend-percent">{{ number_format($downPct, 2) }}%</td>
                    </tr>
                    <tr>
                        <td><span class="color-box warning"></span>WARNING</td>
                        <td class="legend-percent">{{ number_format($warnPct, 2) }}%</td>
                    </tr>
                    <tr class="legend-rt-row">
                        <td><strong>Avg Response Time</strong></td>
                        <td class="legend-percent">{{ $reportData['statistics']['avg_response_time'] ?? 0 }}s</td>
                    </tr>
                </table>
            </div>
        </div>

        <hr class="divider-gradient">

        <!-- ============================================================
        TANGGAL KRITIS
        ============================================================ -->
        @if(!empty($reportData['critical_dates']))
        <div class="section">
            <div class="section-title-sm">TANGGAL KRITIS</div>
            <table class="critical-table">
                @foreach($reportData['critical_dates'] as $date => $data)
                    @php
                        $level = ($data['down_count'] ?? 0) > 0 ? 'danger' : 'warning';
                        $statusText = ($data['down_count'] ?? 0) > 0 ? 'DOWN' : 'WARNING';
                    @endphp
                    <tr class="critical-item {{ $level }}">
                        <td class="date">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                        <td class="status">
                            <strong>{{ $statusText }}</strong>
                            &bull;
                            Uptime <strong>{{ $data['uptime'] ?? 0 }}%</strong>
                            @if(($data['down_count'] ?? 0) > 0)
                                <span class="badge badge-down">{{ $data['down_count'] }}x down</span>
                            @endif
                            @if(($data['warning_count'] ?? 0) > 0)
                                <span class="badge badge-warning">{{ $data['warning_count'] }}x warning</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>

        <hr class="divider-gradient">
        @endif

        <!-- ============================================================
        DETAIL LOG
        ============================================================ -->
        <div class="section page-break">
            <div class="section-title">DETAIL LOG</div>

            @php
                $logCount = $reportData['logs']->count();
                $totalLogs = $reportData['statistics']['total_checks'] ?? 0;
            @endphp

            @if($logCount > 0)
                <p class="text-muted" style="margin-bottom:6px; padding-left:4px;">
                    Menampilkan <strong>{{ $logCount }}</strong> data log terbaru
                    dari <strong>{{ number_format($totalLogs) }}</strong> total data
                </p>

                <div class="table-responsive">
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th style="width:18%;">Waktu</th>
                                <th style="width:11%; text-align:center;">Status</th>
                                <th style="width:11%; text-align:center;">Code</th>
                                <th style="width:12%; text-align:center;">RT (s)</th>
                                <th style="width:48%;">Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['logs'] as $index => $log)
                                <tr class="{{ $index % 2 == 1 ? 'even-row' : '' }}">
                                    <td>{{ $log['date'] }}</td>
                                    <td style="text-align:center;">
                                        <span class="badge badge-{{ strtolower($log['status']) }}">
                                            {{ $log['status'] }}
                                        </span>
                                    </td>
                                    <td style="text-align:center; font-weight:700; font-family: 'DejaVu Sans', 'Arial', sans-serif;">
                                        {{ $log['response_code'] }}
                                    </td>
                                    <td style="text-align:center; font-weight:600; font-family: 'DejaVu Sans', 'Arial', sans-serif;">
                                        {{ $log['response_time'] }}
                                    </td>
                                    <td style="font-size:8px; font-family: 'DejaVu Sans', 'Arial', sans-serif;">
                                        {{ Str::limit($log['message'] ?? '-', 55) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="no-data">
                    Tidak ada data log untuk periode ini
                </div>
            @endif
        </div>

        <!-- ============================================================
        FOOTER
        ============================================================ -->
        <div class="footer">
            Laporan dibuat otomatis oleh <strong>Sistem Monitoring</strong>
            &bull;
            {{ $reportData['period']['date_from'] }}
            &rarr;
            {{ $reportData['period']['date_to'] }}
            &bull;
            Total <strong>{{ number_format($reportData['statistics']['total_checks'] ?? 0) }}</strong> data
            <br>
            <span style="font-size: 7px; color: #a0aec0;">
                Dokumen ini dicetak secara otomatis dan tidak memerlukan tanda tangan
            </span>
        </div>

    </div>

</body>
</html>