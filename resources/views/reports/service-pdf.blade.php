<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Monitoring Service - {{ $reportData['service']['name'] }}</title>
    <style>
        /* ============================================================
           RESET & BASE
           Catatan render PDF (dompdf):
           - Semua teks WAJIB dibungkus tag inline sendiri (<span>/<strong>),
             jangan taruh font-weight langsung di <td>. dompdf kadang
             fallback ke font serif (Times) kalau teks polos di dalam <td>
             diberi style lewat class pada <td> itu sendiri.
           - Hindari CSS gradient utk background; dompdf tidak konsisten
             merendernya -> pakai warna solid.
           - Hanya pakai font-weight 400 / 700 (bukan 500/600/800) supaya
             pasti dipetakan ke varian Regular/Bold font yang di-embed.
           ============================================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
        }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 11px;
            font-weight: 400;
            padding: 22px 28px;
            color: #263449;
            background: #eef1f6;
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }

        /* ============================================================
           CARD
           ============================================================ */
        .card {
            background: #ffffff;
            border-radius: 12px;
            padding: 0 0 22px 0;
            margin-bottom: 16px;
            border: 1px solid #dce3ee;
            box-shadow: 0 4px 14px rgba(10, 35, 66, 0.06);
            overflow: hidden;
        }

        /* ============================================================
           KOP SURAT / HEADER
           Warna SOLID (bukan gradient) - dominan biru tua, senada web
           ============================================================ */
        .kop-surat {
            text-align: center;
            padding: 26px 24px 22px 24px;
            margin-bottom: 20px;
            background-color: #0b2545;
            border-bottom: 4px solid #2f6fb3;
        }

        .kop-surat .logo-title {
            font-size: 25px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 3.5px;
            text-transform: uppercase;
        }

        .kop-surat .logo-title span {
            color: #6fb1ff;
        }

        .kop-surat .sub-logo {
            font-size: 10.5px;
            color: #a9c3e4;
            letter-spacing: 2.5px;
            font-weight: 400;
            margin-top: 3px;
        }

        .kop-surat .sub-logo strong {
            color: #dce9fb;
            font-weight: 700;
        }

        .kop-surat .info-periode {
            font-size: 11px;
            color: #e4edfa;
            font-weight: 400;
            margin-top: 14px;
        }

        .kop-surat .info-periode strong {
            color: #ffffff;
            font-weight: 700;
        }

        .kop-surat .info-periode .badge-header {
            display: inline-block;
            background-color: #16406e;
            color: #8fd4a8;
            padding: 2px 12px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-left: 6px;
            border: 1px solid #2f6fb3;
        }

        .kop-surat .info-periode .printed {
            display: block;
            font-size: 8.5px;
            color: #7d9ac2;
            font-weight: 400;
            margin-top: 6px;
        }

        /* ============================================================
           BODY WRAPPER
           ============================================================ */
        .card-body {
            padding: 0 24px;
        }

        /* ============================================================
           SECTION TITLE
           ============================================================ */
        .section {
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #0b2545;
            padding: 7px 14px;
            margin-bottom: 10px;
            background-color: #e8f0fc;
            border-left: 4px solid #164a86;
            letter-spacing: 0.4px;
        }

        .section-title-sm {
            font-size: 10.5px;
            font-weight: 700;
            color: #7a2020;
            padding: 6px 14px;
            margin-bottom: 8px;
            background-color: #fdeceb;
            border-left: 4px solid #c0392b;
            letter-spacing: 0.4px;
        }

        /* ============================================================
           DIVIDER
           ============================================================ */
        .divider-gradient {
            border: none;
            height: 1px;
            background-color: #dce3ee;
            margin: 14px 0;
        }

        /* ============================================================
           INFO TABLE
           Font-weight HANYA di span di dalamnya, td cuma utk layout
           ============================================================ */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f1f4f9;
            vertical-align: middle;
        }

        .info-label {
            width: 120px;
        }

        .info-label .txt-label {
            font-weight: 700;
            color: #7383a0;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .info-value .txt-value {
            color: #263449;
            font-weight: 400;
            font-size: 9.5px;
        }

        .info-value strong {
            color: #0b2545;
            font-weight: 700;
            font-size: 9.5px;
        }

        /* ============================================================
           STATUS BADGE
           ============================================================ */
        .status-down {
            color: #7a2020;
            font-weight: 700;
            background-color: #fbd9d6;
            padding: 2px 12px;
            border-radius: 12px;
            display: inline-block;
            font-size: 9.5px;
        }

        .status-up {
            color: #1c5c3a;
            font-weight: 700;
            background-color: #cdf0da;
            padding: 2px 12px;
            border-radius: 12px;
            display: inline-block;
            font-size: 9.5px;
        }

        .status-warning {
            color: #7a5a10;
            font-weight: 700;
            background-color: #fdf0c4;
            padding: 2px 12px;
            border-radius: 12px;
            display: inline-block;
            font-size: 9.5px;
        }

        .status-unknown {
            color: #45536b;
            font-weight: 700;
            background-color: #e4e9f1;
            padding: 2px 12px;
            border-radius: 12px;
            display: inline-block;
            font-size: 9.5px;
        }

        /* ============================================================
           CHART / RINGKASAN EKSEKUTIF
           ============================================================ */
        .chart-container {
            padding: 16px 18px;
            background-color: #f6f8fc;
            border-radius: 10px;
            border: 1px solid #dce3ee;
        }

        .bar-track {
            width: 100%;
            height: 24px;
            border-radius: 6px;
            border: 1px solid #dce3ee;
            background-color: #e4e9f1;
            overflow: hidden;
        }

        .bar-track table {
            width: 100%;
            height: 24px;
            border-collapse: collapse;
        }

        .bar-track td {
            height: 24px;
            padding: 0;
        }

        .bar-up {
            background-color: #2e9e5b;
        }

        .bar-down {
            background-color: #d9534f;
        }

        .bar-warning {
            background-color: #e8a33d;
        }

        .legend-table {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
        }

        .legend-table td {
            padding: 5px 6px;
            vertical-align: middle;
        }

        .legend-table .txt-legend-label {
            font-weight: 700;
            color: #45536b;
            font-size: 9.5px;
        }

        .color-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 4px;
            margin-right: 8px;
            vertical-align: middle;
        }

        .color-box.up {
            background-color: #2e9e5b;
        }

        .color-box.down {
            background-color: #d9534f;
        }

        .color-box.warning {
            background-color: #e8a33d;
        }

        .legend-percent {
            text-align: right;
        }

        .legend-percent strong {
            font-weight: 700;
            color: #263449;
            font-size: 10.5px;
        }

        .legend-rt-row td {
            border-top: 2px solid #dce3ee;
            padding-top: 9px;
        }

        .legend-rt-row .txt-legend-label {
            color: #7383a0;
            font-size: 9.5px;
        }

        .legend-rt-row .legend-percent strong {
            font-size: 12px;
            color: #0b2545;
        }

        /* ============================================================
           BADGE
           ============================================================ */
        .badge {
            display: inline-block;
            padding: 1px 10px;
            border-radius: 10px;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-up {
            background-color: #cdf0da;
            color: #1c5c3a;
        }

        .badge-warning {
            background-color: #fdf0c4;
            color: #7a5a10;
        }

        .badge-down {
            background-color: #fbd9d6;
            color: #7a2020;
        }

        .badge-unknown {
            background-color: #e4e9f1;
            color: #45536b;
        }

        /* ============================================================
           CRITICAL DATES
           ============================================================ */
        .critical-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 5px;
        }

        .critical-item td {
            padding: 7px 14px;
            border-left: 4px solid;
            border-radius: 4px;
        }

        .critical-item.danger td {
            background-color: #fdf2f1;
            border-color: #d9534f;
        }

        .critical-item.warning td {
            background-color: #fdf8ec;
            border-color: #e8a33d;
        }

        .critical-item .date {
            width: 30%;
        }

        .critical-item .date strong {
            font-weight: 700;
            font-size: 9.5px;
            color: #263449;
        }

        .critical-item .status {
            font-size: 9.5px;
            color: #45536b;
            font-weight: 400;
        }

        .critical-item .status strong {
            font-weight: 700;
        }

        /* ============================================================
           LOG TABLE
           ============================================================ */
        .table-responsive {
            border-radius: 8px;
            border: 1px solid #dce3ee;
            background-color: #ffffff;
            overflow: hidden;
        }

        table.log-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.log-table thead th {
            background-color: #0b2545;
            color: #ffffff;
            padding: 7px 10px;
            text-align: left;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.log-table tbody td {
            padding: 6px 10px;
            border-bottom: 1px solid #f1f4f9;
            vertical-align: middle;
        }

        table.log-table tbody tr:last-child td {
            border-bottom: none;
        }

        table.log-table tbody tr.even-row {
            background-color: #f6f8fc;
        }

        .txt-cell {
            font-size: 8.5px;
            color: #364157;
            font-weight: 400;
        }

        .txt-cell-strong {
            font-size: 8.5px;
            color: #263449;
            font-weight: 700;
        }

        .txt-cell-center {
            font-size: 8.5px;
            color: #364157;
            font-weight: 400;
            text-align: center;
            display: block;
        }

        .txt-cell-strong-center {
            font-size: 8.5px;
            color: #263449;
            font-weight: 700;
            text-align: center;
            display: block;
        }

        /* ============================================================
           TEXT
           ============================================================ */
        .text-muted {
            color: #8695ae;
            font-size: 8.5px;
            font-weight: 400;
        }

        .no-data {
            text-align: center;
            padding: 26px;
            color: #8695ae;
            font-style: italic;
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }

        /* ============================================================
           FOOTER
           ============================================================ */
        .footer {
            text-align: center;
            margin-top: 22px;
            padding: 14px 24px 0 24px;
            border-top: 2px solid #dce3ee;
            font-size: 8.5px;
            color: #a3aec4;
            font-weight: 400;
        }

        .footer strong {
            color: #0b2545;
            font-weight: 700;
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
                s/d
                {{ $reportData['period']['date_to'] }}
                &bull;
                <strong>{{ number_format($reportData['statistics']['total_checks']) }}</strong> data
                <span class="badge-header">LIVE</span>
                <span class="printed">
                    Dicetak: {{ now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB
                </span>
            </div>
        </div>

        <div class="card-body">

        <!-- ============================================================
        INFORMASI SERVICE
        ============================================================ -->
        <div class="section">
            <div class="section-title">INFORMASI SERVICE</div>
            <table class="info-table">
                <tr>
                    <td class="info-label"><span class="txt-label">Nama Service</span></td>
                    <td class="info-value"><strong>{{ $reportData['service']['name'] }}</strong></td>
                    <td class="info-label"><span class="txt-label">Service Dibuat</span></td>
                    <td class="info-value"><span class="txt-value">{{ $reportData['service']['created_at'] ?? 'N/A' }}</span></td>
                </tr>
                <tr>
                    <td class="info-label"><span class="txt-label">Target</span></td>
                    <td class="info-value"><span class="txt-value">{{ $reportData['service']['target'] }}</span></td>
                    <td class="info-label"><span class="txt-label">Periode Laporan</span></td>
                    <td class="info-value">
                        <span class="txt-value">
                            {{ $reportData['period']['date_from'] }}
                            s/d
                            {{ $reportData['period']['date_to'] }}
                        </span>
                        <span class="text-muted">({{ $reportData['period']['total_days'] }} hari)</span>
                    </td>
                </tr>
                <tr>
                    <td class="info-label"><span class="txt-label">Tipe</span></td>
                    <td class="info-value"><strong>{{ strtoupper($reportData['service']['type']) }}</strong></td>
                    <td class="info-label"><span class="txt-label">Status Terakhir</span></td>
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
                                <td style="width: 100%; background-color: #e4e9f1; text-align: center;">
                                    <span class="text-muted">Tidak ada data</span>
                                </td>
                            @endif
                        </tr>
                    </table>
                </div>

                <!-- LEGEND -->
                <table class="legend-table">
                    <tr>
                        <td><span class="color-box up"></span><span class="txt-legend-label">UP</span></td>
                        <td class="legend-percent"><strong>{{ number_format($upPct, 2) }}%</strong></td>
                    </tr>
                    <tr>
                        <td><span class="color-box down"></span><span class="txt-legend-label">DOWN</span></td>
                        <td class="legend-percent"><strong>{{ number_format($downPct, 2) }}%</strong></td>
                    </tr>
                    <tr>
                        <td><span class="color-box warning"></span><span class="txt-legend-label">WARNING</span></td>
                        <td class="legend-percent"><strong>{{ number_format($warnPct, 2) }}%</strong></td>
                    </tr>
                    <tr class="legend-rt-row">
                        <td><span class="txt-legend-label">Avg Response Time</span></td>
                        <td class="legend-percent"><strong>{{ $reportData['statistics']['avg_response_time'] ?? 0 }}s</strong></td>
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
                        <td class="date"><strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong></td>
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
                                <th style="width:16%;">Waktu</th>
                                <th style="width:10%; text-align:center;">Status</th>
                                <th style="width:10%; text-align:center;">Code</th>
                                <th style="width:10%; text-align:center;">RT (s)</th>
                                <th style="width:54%;">Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['logs'] as $index => $log)
                                @php
                                    // Bersihkan pesan dari emoji/simbol yang tidak punya
                                    // glyph di font PDF, supaya tidak tampil '???' / kotak.
                                    // Ini murni pembersihan tampilan, tidak mengubah data asli.
                                    $rawMessage = $log['message'] ?? '-';
                                    $cleanMessage = preg_replace('/[^\x{0020}-\x{024F}\x{2010}-\x{2015}\x{2018}-\x{201F}\x{2022}]/u', ' ', $rawMessage);
                                    $cleanMessage = trim(preg_replace('/\s+/', ' ', $cleanMessage ?? ''));
                                    $cleanMessage = $cleanMessage !== '' ? $cleanMessage : '-';
                                @endphp
                                <tr class="{{ $index % 2 == 1 ? 'even-row' : '' }}">
                                    <td><span class="txt-cell">{{ $log['date'] }}</span></td>
                                    <td style="text-align:center;">
                                        <span class="badge badge-{{ strtolower($log['status']) }}">
                                            {{ $log['status'] }}
                                        </span>
                                    </td>
                                    <td><span class="txt-cell-strong-center">{{ $log['response_code'] }}</span></td>
                                    <td><span class="txt-cell-strong-center">{{ $log['response_time'] }}</span></td>
                                    <td><span class="txt-cell">{{ Str::limit($cleanMessage, 80) }}</span></td>
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
            s/d
            {{ $reportData['period']['date_to'] }}
            &bull;
            Total <strong>{{ number_format($reportData['statistics']['total_checks'] ?? 0) }}</strong> data
            <br>
            <span style="font-size: 7px; color: #b7c0d1;">
                Dokumen ini dicetak secara otomatis dan tidak memerlukan tanda tangan
            </span>
        </div>

        </div><!-- /.card-body -->

    </div>

</body>
</html>