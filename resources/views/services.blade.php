@extends('layouts.app')

@section('content')
<!-- 🔥 FORCE NO CACHE -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">

<style>
    /* ================= ROOT VARIABLES ================= */
    :root {
        --bg-service: #f0f2f5;
        --bg-card-service: #ffffff;
        --bg-table-service: #fafbfc;
        --bg-hover-service: #f8fafc;
        --bg-input-service: #ffffff;
        --bg-toast-service: #ffffff;
        --bg-modal-service: #ffffff;
        --bg-modal-header-service: #fafbfc;
        --bg-modal-footer-service: #fafbfc;
        --bg-status-bar-service: #f8fafc;
        --bg-detail-service: #f8fafc;
        --bg-detail-alt-service: #f1f5f9;
        --bg-info-box-service: #eff6ff;
        --border-info-box-service: #93c5fd;
        --text-info-box-service: #1e40af;
        --bg-delete-modal-service: #ffffff;
        
        --text-service: #1e293b;
        --text-secondary-service: #475569;
        --text-muted-service: #94a3b8;
        --text-light-service: #6b7280;
        --border-service: #e8ecf1;
        --border-table-service: #f1f4f8;
        --shadow-service: rgba(0, 0, 0, 0.04);
        --shadow-hover-service: rgba(0, 0, 0, 0.08);
    }

    /* Dark mode override */
    [data-theme="dark"] {
        --bg-service: #0f172a;
        --bg-card-service: #1e293b;
        --bg-table-service: #1e293b;
        --bg-hover-service: #2d3a4f;
        --bg-input-service: #1e293b;
        --bg-toast-service: #1e293b;
        --bg-modal-service: #1e293b;
        --bg-modal-header-service: #1e293b;
        --bg-modal-footer-service: #1e293b;
        --bg-status-bar-service: #1e293b;
        --bg-detail-service: #2d3a4f;
        --bg-detail-alt-service: #1e293b;
        --bg-info-box-service: #1a2332;
        --border-info-box-service: #3b82f6;
        --text-info-box-service: #93c5fd;
        --bg-delete-modal-service: #1e293b;
        
        --text-service: #e2e8f0;
        --text-secondary-service: #94a3b8;
        --text-muted-service: #64748b;
        --text-light-service: #94a3b8;
        --border-service: #334155;
        --border-table-service: #334155;
        --shadow-service: rgba(0, 0, 0, 0.2);
        --shadow-hover-service: rgba(0, 0, 0, 0.3);
    }

    /* ================= STYLE UTAMA ================= */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    .service-container {
        padding: 24px;
        max-width: 1440px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        background: var(--bg-service);
        min-height: 100vh;
        transition: background 0.3s ease, color 0.3s ease;
        color: var(--text-service);
    }

    /* ================= HEADER ================= */
    .service-header {
        background: linear-gradient(135deg, #0a2e5c 0%, #1a4d7a 50%, #1e5f8e 100%);
        padding: 28px 36px;
        border-radius: 16px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(10, 46, 92, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.08);
        transition: box-shadow 0.3s ease;
    }

    .service-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 50%;
        pointer-events: none;
    }

    .service-header::after {
        content: '';
        position: absolute;
        bottom: -60%;
        left: 20%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 50%;
        pointer-events: none;
    }

    .service-header .header-left {
        display: flex;
        align-items: center;
        gap: 18px;
        position: relative;
        z-index: 1;
    }

    .service-header .header-icon {
        width: 56px;
        height: 56px;
        background: rgba(255, 255, 255, 0.12);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: white;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .service-header .header-text h1 {
        font-size: 24px;
        font-weight: 700;
        color: white;
        margin: 0;
        letter-spacing: -0.3px;
        line-height: 1.2;
    }

    .service-header .header-text .header-subtitle {
        color: rgba(255, 255, 255, 0.75);
        font-size: 13px;
        font-weight: 400;
        margin-top: 2px;
    }

    .service-header .header-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .wa-interval-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.1);
        padding: 4px 12px 4px 16px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
    }

    .wa-interval-wrapper .wa-label {
        color: rgba(255, 255, 255, 0.75);
        font-size: 11px;
        font-weight: 500;
        white-space: nowrap;
        letter-spacing: 0.3px;
    }

    .wa-interval-wrapper .wa-label .wa-icon {
        margin-right: 4px;
    }

    .wa-interval-wrapper select {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        outline: none;
        transition: all 0.2s ease;
        font-family: inherit;
        min-width: 100px;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='rgba(255,255,255,0.7)' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 30px;
    }

    .wa-interval-wrapper select:hover {
        background-color: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.4);
    }

    .wa-interval-wrapper select:focus {
        border-color: rgba(255, 255, 255, 0.5);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
    }

    .wa-interval-wrapper select option {
        background: #1e293b;
        color: white;
    }

    .btn-primary {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        padding: 11px 24px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
        backdrop-filter: blur(10px);
    }

    .btn-primary:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .btn-primary svg {
        width: 18px;
        height: 18px;
    }

    /* 🔥 TOMBOL DOWNLOAD MULTI */
    .btn-download-multi {
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
        padding: 11px 24px;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }

    .btn-download-multi:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(5, 150, 105, 0.35);
    }

    .btn-archive {
        background: #6b7280;
        color: white;
        padding: 6px 14px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-archive:hover {
        background: #4b5563;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-restore {
        background: #8b5cf6;
        color: white;
        padding: 6px 14px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-restore:hover {
        background: #7c3aed;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
    }

    .btn-delete-permanent {
        background: #ef4444;
        color: white;
        padding: 6px 14px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-delete-permanent:hover {
        background: #dc2626;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    .status-badge.archived {
        background: #f3f4f6;
        color: #6b7280;
        border-color: #d1d5db;
    }
    .status-badge.archived .status-dot {
        background: #9ca3af;
    }

    [data-theme="dark"] .status-badge.archived {
        background: #374151;
        color: #9ca3af;
        border-color: #4b5563;
    }
    [data-theme="dark"] .status-badge.archived .status-dot {
        background: #6b7280;
    }

    .btn-toggle-archive {
        background: var(--bg-card-service);
        color: var(--text-service);
        padding: 8px 16px;
        border: 1px solid var(--border-service);
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: inherit;
    }
    .btn-toggle-archive:hover {
        background: var(--bg-hover-service);
        border-color: var(--text-muted-service);
    }
    .btn-toggle-archive.active {
        background: #8b5cf6;
        color: white;
        border-color: #8b5cf6;
    }
    .btn-toggle-archive.active:hover {
        background: #7c3aed;
    }

    .archive-count {
        background: #8b5cf6;
        color: white;
        font-size: 10px;
        font-weight: 700;
        padding: 1px 8px;
        border-radius: 10px;
        margin-left: 4px;
    }

    .btn-toggle-archive.active .archive-count {
        background: rgba(255, 255, 255, 0.3);
    }

    /* ================= AUTO REFRESH TIMER ================= */
    .auto-refresh-timer {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: rgba(10, 46, 92, 0.85);
        color: white;
        padding: 8px 14px;
        border-radius: 8px;
        z-index: 99999;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        user-select: none;
        cursor: default;
        transition: background 0.3s ease;
    }

    [data-theme="dark"] .auto-refresh-timer {
        background: rgba(15, 23, 42, 0.9);
        border-color: rgba(255, 255, 255, 0.05);
    }

    .auto-refresh-timer .icon { font-size: 14px; }
    .auto-refresh-timer .label { opacity: 0.7; font-size: 10px; }
    .auto-refresh-timer .countdown {
        font-weight: 700;
        font-size: 14px;
        min-width: 40px;
        text-align: center;
        color: #6ee7b7;
    }
    .auto-refresh-timer .countdown.warning { color: #fcd34d; }
    .auto-refresh-timer .countdown.danger {
        color: #fca5a5;
        animation: blink 0.5s infinite;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    /* ================= TOAST ================= */
    .toast-container {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 420px;
        width: 100%;
    }

    .toast {
        background: var(--bg-toast-service);
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: 0 10px 40px var(--shadow-service);
        border-left: 5px solid;
        animation: slideInRight 0.4s ease;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        color: var(--text-service);
        border: 1px solid var(--border-service);
    }

    .toast.hide { animation: slideOutRight 0.4s ease forwards; }
    .toast-success { border-left-color: #10b981; }
    .toast-error { border-left-color: #ef4444; }
    .toast-warning { border-left-color: #f59e0b; }
    .toast-info { border-left-color: #3b82f6; }

    .toast .toast-icon { font-size: 22px; flex-shrink: 0; margin-top: 2px; }
    .toast .toast-content { flex: 1; }
    .toast .toast-title { font-weight: 600; font-size: 14px; color: var(--text-service); }
    .toast .toast-message { font-size: 13px; color: var(--text-secondary-service); margin-top: 2px; }
    .toast .toast-close {
        background: none;
        border: none;
        font-size: 20px;
        color: var(--text-muted-service);
        cursor: pointer;
        padding: 0 4px;
        line-height: 1;
        transition: color 0.2s ease;
    }
    .toast .toast-close:hover { color: var(--text-service); }

    @keyframes slideInRight {
        from { transform: translateX(120%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(120%); opacity: 0; }
    }

    /* ================= STATS ================= */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-item {
        background: var(--bg-card-service);
        padding: 20px 24px;
        border-radius: 14px;
        border: 1px solid var(--border-service);
        box-shadow: 0 2px 8px var(--shadow-service);
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        color: var(--text-service);
    }

    .stat-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        border-radius: 4px 0 0 4px;
    }
    .stat-item:nth-child(1)::before { background: #4f46e5; }
    .stat-item:nth-child(2)::before { background: #059669; }
    .stat-item:nth-child(3)::before { background: #d97706; }
    .stat-item:nth-child(4)::before { background: #dc2626; }
    .stat-item:nth-child(5)::before { background: #8b5cf6; }

    .stat-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px var(--shadow-hover-service);
    }

    .stat-item .stat-number {
        font-size: 30px;
        font-weight: 800;
        color: var(--text-service);
        display: block;
        letter-spacing: -0.5px;
        line-height: 1.2;
        transition: color 0.3s ease;
    }
    .stat-item .stat-number.purple { color: #4f46e5; }
    .stat-item .stat-number.green { color: #059669; }
    .stat-item .stat-number.yellow { color: #d97706; }
    .stat-item .stat-number.red { color: #dc2626; }
    .stat-item .stat-number.archive { color: #8b5cf6; }

    .stat-item .stat-label {
        font-size: 12px;
        color: var(--text-muted-service);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
        transition: color 0.3s ease;
    }

    /* ================= UPTIME ================= */
    .uptime-value { font-size: 16px; font-weight: 700; transition: color 0.3s ease; }
    .uptime-value.green { color: #059669; }
    .uptime-value.yellow { color: #d97706; }
    .uptime-value.red { color: #dc2626; }

    .uptime-bar {
        width: 100%;
        height: 4px;
        background: var(--border-service);
        border-radius: 2px;
        margin-top: 4px;
        overflow: hidden;
        transition: background 0.3s ease;
    }
    .uptime-bar .uptime-fill {
        height: 100%;
        border-radius: 2px;
        transition: width 0.5s ease;
    }
    .uptime-bar .uptime-fill.green { background: #059669; }
    .uptime-bar .uptime-fill.yellow { background: #d97706; }
    .uptime-bar .uptime-fill.red { background: #dc2626; }

    /* ================= SEARCH BOX ================= */
    .search-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        max-width: 500px;
    }

    .search-wrapper .search-input-wrap {
        position: relative;
        flex: 1;
    }

    .search-wrapper .search-input-wrap .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted-service);
        font-size: 16px;
        pointer-events: none;
        transition: color 0.3s ease;
    }

    .search-wrapper .search-input-wrap input {
        width: 100%;
        padding: 10px 14px 10px 38px;
        border: 1px solid var(--border-service);
        border-radius: 10px;
        font-size: 14px;
        background: var(--bg-input-service);
        color: var(--text-service);
        outline: none;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .search-wrapper .search-input-wrap input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        background: var(--bg-input-service);
    }

    .search-wrapper .search-input-wrap input::placeholder {
        color: var(--text-muted-service);
    }

    .search-wrapper .btn-search {
        background: #4f46e5;
        color: white;
        padding: 10px 22px;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .search-wrapper .btn-search:hover {
        background: #4338ca;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }

    .search-wrapper .btn-search:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .search-wrapper .btn-reset {
        background: var(--border-service);
        color: var(--text-secondary-service);
        padding: 10px 16px;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .search-wrapper .btn-reset:hover {
        background: var(--text-muted-service);
        color: var(--bg-service);
        transform: translateY(-1px);
    }

    /* ================= SEARCH STATUS BAR ================= */
    .search-status {
        display: none;
        padding: 10px 16px;
        background: var(--bg-status-bar-service);
        border-bottom: 1px solid var(--border-service);
        font-size: 13px;
        color: var(--text-secondary-service);
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
    }

    .search-status.active {
        display: flex;
    }

    .search-status .status-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid var(--border-service);
        border-top: 2px solid #4f46e5;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        flex-shrink: 0;
    }

    .search-status .status-text {
        flex: 1;
        color: var(--text-secondary-service);
    }

    .search-status .status-cancel {
        background: none;
        border: none;
        color: var(--text-muted-service);
        cursor: pointer;
        font-size: 18px;
        padding: 0 4px;
        transition: color 0.2s ease;
    }

    .search-status .status-cancel:hover {
        color: var(--text-service);
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* ================= TABLE ================= */
    .table-container {
        background: var(--bg-card-service);
        border-radius: 14px;
        box-shadow: 0 2px 8px var(--shadow-service);
        border: 1px solid var(--border-service);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-table-service);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        background: var(--bg-table-service);
        transition: all 0.3s ease;
    }

    .table-header .header-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .table-header h2 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-service);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: color 0.3s ease;
    }

    .table-header .table-info {
        font-size: 13px;
        color: var(--text-muted-service);
        transition: color 0.3s ease;
    }
    .table-header .table-info strong { color: var(--text-service); }

    .table-header-right {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .perpage-selector {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--text-secondary-service);
        transition: color 0.3s ease;
    }

    .perpage-selector select {
        padding: 6px 12px;
        border: 1px solid var(--border-service);
        border-radius: 6px;
        background: var(--bg-input-service);
        color: var(--text-service);
        font-size: 13px;
        cursor: pointer;
        outline: none;
        transition: all 0.2s ease;
    }
    .perpage-selector select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    .perpage-selector select option {
        background: var(--bg-input-service);
        color: var(--text-service);
    }

    .table-scroll {
        overflow-x: auto;
        padding: 0 24px 24px;
    }

    .table-container table {
        width: 100%;
        border-collapse: collapse;
    }

    .table-container thead th {
        text-align: left;
        padding: 14px 16px;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted-service);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border-table-service);
        background: var(--bg-table-service);
        position: sticky;
        top: 0;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .table-container tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-table-service);
        color: var(--text-service);
        font-size: 14px;
        vertical-align: middle;
        transition: all 0.3s ease;
    }
    .table-container tbody tr:last-child td { border-bottom: none; }
    .table-container tbody tr:hover { background: var(--bg-hover-service); }

    /* ================= SORTING CSS ================= */
    .sortable-header {
        cursor: pointer;
        user-select: none;
        transition: all 0.2s ease;
        position: relative;
        padding-right: 22px !important;
    }

    .sortable-header:hover {
        color: #4f46e5;
    }

    .sortable-header .sort-icon {
        position: absolute;
        right: 4px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 9px;
        color: #cbd5e1;
        transition: color 0.2s ease;
        display: inline-flex;
        flex-direction: column;
        line-height: 1;
        letter-spacing: 0;
        font-weight: 300;
        opacity: 0.6;
    }

    .sortable-header:hover .sort-icon {
        opacity: 1;
    }

    .sortable-header .sort-icon .arrow-up {
        margin-bottom: -1px;
        font-size: 8px;
    }

    .sortable-header .sort-icon .arrow-down {
        margin-top: -1px;
        font-size: 8px;
    }

    .sortable-header.active-asc .sort-icon .arrow-up {
        color: #4f46e5 !important;
        opacity: 1 !important;
        font-weight: 700;
    }

    .sortable-header.active-asc .sort-icon .arrow-down {
        color: #cbd5e1 !important;
        opacity: 0.3 !important;
    }

    .sortable-header.active-desc .sort-icon .arrow-down {
        color: #4f46e5 !important;
        opacity: 1 !important;
        font-weight: 700;
    }

    .sortable-header.active-desc .sort-icon .arrow-up {
        color: #cbd5e1 !important;
        opacity: 0.3 !important;
    }

    .sortable-header.active-asc,
    .sortable-header.active-desc {
        color: #4f46e5 !important;
    }

    [data-theme="dark"] .sortable-header:hover {
        color: #818cf8;
    }

    [data-theme="dark"] .sortable-header.active-asc,
    [data-theme="dark"] .sortable-header.active-desc {
        color: #818cf8 !important;
    }

    [data-theme="dark"] .sortable-header .sort-icon {
        color: #475569;
    }

    [data-theme="dark"] .sortable-header.active-asc .sort-icon .arrow-up {
        color: #818cf8 !important;
    }

    [data-theme="dark"] .sortable-header.active-desc .sort-icon .arrow-down {
        color: #818cf8 !important;
    }

    [data-theme="dark"] .sortable-header.active-asc .sort-icon .arrow-down,
    [data-theme="dark"] .sortable-header.active-desc .sort-icon .arrow-up {
        color: #475569 !important;
    }

    @media (max-width: 768px) {
        .sortable-header {
            padding-right: 18px !important;
            font-size: 10px !important;
        }
        .sortable-header .sort-icon {
            font-size: 7px;
            right: 2px;
        }
        .sortable-header .sort-icon .arrow-up,
        .sortable-header .sort-icon .arrow-down {
            font-size: 6px;
        }
    }

    /* ================= SERVICE INFO ================= */
    .service-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .service-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        color: white;
        flex-shrink: 0;
        text-transform: uppercase;
    }
    .service-avatar.color-1 { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
    .service-avatar.color-2 { background: linear-gradient(135deg, #059669, #10b981); }
    .service-avatar.color-3 { background: linear-gradient(135deg, #d97706, #f59e0b); }
    .service-avatar.color-4 { background: linear-gradient(135deg, #dc2626, #ef4444); }
    .service-avatar.color-5 { background: linear-gradient(135deg, #2563eb, #3b82f6); }
    .service-avatar.color-6 { background: linear-gradient(135deg, #7c3aed, #8b5cf6); }
    .service-avatar.color-7 { background: linear-gradient(135deg, #db2777, #ec4899); }
    .service-avatar.color-8 { background: linear-gradient(135deg, #0d9488, #14b8a6); }

    .service-name {
        font-weight: 600;
        color: var(--text-service);
        font-size: 14px;
        transition: color 0.3s ease;
    }
    .service-type {
        font-size: 11px;
        color: var(--text-muted-service);
        display: block;
        margin-top: 1px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        transition: color 0.3s ease;
    }

    .service-target {
        font-size: 13px;
        color: var(--text-secondary-service);
        font-family: 'SF Mono', 'Courier New', monospace;
        background: var(--bg-hover-service);
        padding: 3px 12px;
        border-radius: 4px;
        display: inline-block;
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        border: 1px solid var(--border-service);
        transition: all 0.3s ease;
    }

    /* ================= STATUS BADGE ================= */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border: 1px solid;
        transition: all 0.3s ease;
    }
    .status-badge .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-badge.up {
        background: #d1fae5;
        color: #065f46;
        border-color: #6ee7b7;
    }
    .status-badge.up .status-dot {
        background: #059669;
    }

    .status-badge.down {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fca5a5;
    }
    .status-badge.down .status-dot {
        background: #dc2626;
    }

    .status-badge.warning {
        background: #fef3c7;
        color: #92400e;
        border-color: #fcd34d;
    }
    .status-badge.warning .status-dot {
        background: #d97706;
    }

    .status-badge.unknown {
        background: var(--bg-hover-service);
        color: var(--text-muted-service);
        border-color: var(--border-service);
    }
    .status-badge.unknown .status-dot {
        background: var(--text-muted-service);
    }

    .service-no {
        font-weight: 600;
        color: var(--text-muted-service);
        font-size: 13px;
        font-family: 'Inter', sans-serif;
        min-width: 30px;
        display: inline-block;
        transition: color 0.3s ease;
    }

    /* ================= BUTTONS ================= */
    .action-buttons {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .btn-action {
        padding: 6px 14px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
        cursor: pointer;
        color: white;
    }
    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-detail { background: #2563eb; }
    .btn-detail:hover { background: #1d4ed8; }

    .btn-download { background: #059669; }
    .btn-download:hover { background: #047857; }

    .btn-edit { background: #d97706; }
    .btn-edit:hover { background: #b45309; }

    .btn-delete { background: #dc2626; }
    .btn-delete:hover { background: #b91c1c; }

    .btn-check {
        background: #7c3aed;
        color: white;
        padding: 6px 14px;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-check:hover {
        background: #6d28d9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
    }
    .btn-check:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    /* ================= PAGINATION ================= */
    .pagination-wrapper {
        padding: 16px 24px 20px;
        border-top: 1px solid var(--border-table-service);
        background: var(--bg-table-service);
        border-radius: 0 0 14px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        transition: all 0.3s ease;
    }

    .pagination-info {
        font-size: 13px;
        color: var(--text-secondary-service);
        transition: color 0.3s ease;
    }
    .pagination-info strong { color: var(--text-service); }

    .pagination-links {
        display: flex;
        gap: 4px;
        align-items: center;
        flex-wrap: wrap;
    }

    .pagination-links .page-link {
        padding: 6px 12px;
        background: var(--bg-card-service);
        border: 1px solid var(--border-service);
        border-radius: 6px;
        font-size: 13px;
        color: var(--text-secondary-service);
        text-decoration: none;
        transition: all 0.2s ease;
        min-width: 36px;
        text-align: center;
    }
    .pagination-links .page-link:hover:not(.active) {
        background: var(--bg-hover-service);
        border-color: var(--text-muted-service);
        transform: translateY(-1px);
    }
    .pagination-links .page-link.active {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
    }
    .pagination-links .page-link.disabled {
        background: var(--bg-hover-service);
        color: var(--text-muted-service);
        cursor: not-allowed;
        pointer-events: none;
        border-color: var(--border-service);
    }
    .pagination-links .page-dots {
        padding: 6px 4px;
        color: var(--text-muted-service);
    }

    /* ================= CUSTOM MODAL ================= */
    .custom-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        z-index: 99999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.25s ease;
    }
    .custom-modal-overlay.active { display: flex; }

    .custom-modal {
        background: var(--bg-delete-modal-service);
        border-radius: 20px;
        max-width: 550px;
        width: 92%;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
        border: 1px solid var(--border-service);
        color: var(--text-service);
    }

    .custom-modal-header {
        padding: 24px 28px 16px;
        text-align: center;
    }

    .custom-modal-header .modal-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        margin: 0 auto 12px;
    }

    .custom-modal-header .modal-icon.warning {
        background: #fef3c7;
        color: #d97706;
    }

    .custom-modal-header .modal-icon.danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .custom-modal-header .modal-icon.success {
        background: #d1fae5;
        color: #059669;
    }

    .custom-modal-header .modal-icon.info {
        background: #dbeafe;
        color: #2563eb;
    }

    .custom-modal-header .modal-icon.archive {
        background: #ede9fe;
        color: #7c3aed;
    }

    .custom-modal-header .modal-icon.restore {
        background: #d1fae5;
        color: #059669;
    }

    .custom-modal-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-service);
        margin: 0 0 6px;
        transition: color 0.3s ease;
    }

    .custom-modal-header p {
        font-size: 14px;
        color: var(--text-secondary-service);
        margin: 0;
        line-height: 1.6;
        transition: color 0.3s ease;
    }

    .custom-modal-body {
        padding: 0 28px 20px;
        max-height: 50vh;
        overflow-y: auto;
    }

    .custom-modal-body .highlight-name {
        font-weight: 700;
        color: var(--text-service);
        background: var(--bg-hover-service);
        padding: 2px 10px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .custom-modal-body .info-text {
        font-size: 13px;
        color: var(--text-secondary-service);
        margin-top: 8px;
        line-height: 1.8;
        text-align: left;
    }

    .custom-modal-footer {
        padding: 16px 28px 24px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        border-top: 1px solid var(--border-service);
        background: var(--bg-modal-footer-service);
        transition: all 0.3s ease;
    }

    .custom-modal-footer .btn-modal {
        padding: 10px 28px;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .custom-modal-footer .btn-modal:hover {
        transform: translateY(-2px);
    }

    .custom-modal-footer .btn-cancel {
        background: var(--bg-hover-service);
        color: var(--text-secondary-service);
        border: 1px solid var(--border-service);
    }

    .custom-modal-footer .btn-cancel:hover {
        background: var(--border-service);
        box-shadow: 0 4px 12px var(--shadow-service);
    }

    .custom-modal-footer .btn-confirm {
        background: #059669;
        color: white;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }

    .custom-modal-footer .btn-confirm:hover {
        background: #047857;
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.35);
    }

    .custom-modal-footer .btn-confirm-danger {
        background: #dc2626;
        color: white;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    }

    .custom-modal-footer .btn-confirm-danger:hover {
        background: #b91c1c;
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.35);
    }

    .custom-modal-footer .btn-confirm-archive {
        background: #8b5cf6;
        color: white;
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.25);
    }

    .custom-modal-footer .btn-confirm-archive:hover {
        background: #7c3aed;
        box-shadow: 0 6px 20px rgba(139, 92, 246, 0.35);
    }

    .custom-modal-footer .btn-confirm-restore {
        background: #059669;
        color: white;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }

    .custom-modal-footer .btn-confirm-restore:hover {
        background: #047857;
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.35);
    }

    .btn-modal:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    /* ================= MODAL FORM ================= */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(6px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.25s ease;
    }
    .modal-overlay.active { display: flex; }

    .modal-content {
        background: var(--bg-modal-service);
        border-radius: 16px;
        max-width: 580px;
        width: 92%;
        max-height: 92vh;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
        animation: slideUp 0.3s ease;
        border: 1px solid var(--border-service);
        color: var(--text-service);
    }

    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-table-service);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--bg-modal-header-service);
        transition: all 0.3s ease;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-service);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: color 0.3s ease;
    }
    .modal-header h2 .modal-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: white;
        flex-shrink: 0;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 26px;
        color: var(--text-muted-service);
        cursor: pointer;
        padding: 0 8px;
        border-radius: 8px;
        transition: all 0.2s ease;
        line-height: 1;
    }
    .modal-close:hover {
        background: var(--bg-hover-service);
        color: var(--text-service);
    }

    .modal-close:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .modal-body {
        padding: 24px;
        max-height: 55vh;
        overflow-y: auto;
    }
    .modal-body::-webkit-scrollbar { width: 5px; }
    .modal-body::-webkit-scrollbar-track {
        background: var(--bg-hover-service);
        border-radius: 10px;
    }
    .modal-body::-webkit-scrollbar-thumb {
        background: var(--text-muted-service);
        border-radius: 10px;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-table-service);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: var(--bg-modal-footer-service);
        border-radius: 0 0 16px 16px;
        transition: all 0.3s ease;
    }

    .modal-footer .btn-cancel-modal:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
    }

    .modal-footer .btn-submit-modal:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    /* ================= DETAIL ================= */
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .detail-item {
        background: var(--bg-detail-service);
        border-radius: 10px;
        padding: 14px 16px;
        border: 1px solid var(--border-service);
        transition: all 0.3s ease;
    }
    .detail-item.full-width { grid-column: 1 / -1; }

    .detail-item .detail-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted-service);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
        transition: color 0.3s ease;
    }

    .detail-item .detail-value {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-service);
        word-break: break-all;
        transition: color 0.3s ease;
    }

    .detail-item .detail-value .status-badge { font-size: 11px; padding: 4px 12px; }

    .detail-item .detail-value .response-code {
        font-family: 'SF Mono', 'Courier New', monospace;
        font-weight: 700;
        padding: 2px 12px;
        border-radius: 4px;
        display: inline-block;
    }
    .detail-item .detail-value .response-code.success {
        background: #d1fae5;
        color: #065f46;
    }
    [data-theme="dark"] .detail-item .detail-value .response-code.success {
        background: #064e3b;
        color: #6ee7b7;
    }
    .detail-item .detail-value .response-code.error {
        background: #fee2e2;
        color: #991b1b;
    }
    [data-theme="dark"] .detail-item .detail-value .response-code.error {
        background: #7f1d1d;
        color: #fca5a5;
    }
    .detail-item .detail-value .response-code.warning {
        background: #fef3c7;
        color: #92400e;
    }
    [data-theme="dark"] .detail-item .detail-value .response-code.warning {
        background: #78350f;
        color: #fcd34d;
    }

    .detail-item .detail-value .response-time {
        font-family: 'SF Mono', 'Courier New', monospace;
        font-weight: 600;
    }
    .detail-item .detail-value .response-time.fast { color: #059669; }
    .detail-item .detail-value .response-time.slow { color: #dc2626; }
    .detail-item .detail-value .response-time.medium { color: #d97706; }

    .detail-timestamp {
        font-size: 13px;
        color: var(--text-secondary-service);
        font-family: 'SF Mono', 'Courier New', monospace;
        background: var(--bg-hover-service);
        padding: 4px 12px;
        border-radius: 6px;
        display: inline-block;
        transition: all 0.3s ease;
    }

    .detail-message {
        background: var(--bg-hover-service);
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 14px;
        color: var(--text-service);
        border-left: 4px solid #4f46e5;
        word-break: break-word;
        max-height: 80px;
        overflow-y: auto;
        font-weight: 400;
        transition: all 0.3s ease;
    }

    .detail-message.empty-message {
        border-left-color: #f59e0b;
        background: var(--bg-info-box-service);
        color: var(--text-info-box-service);
    }
    .detail-message.empty-message::before {
        content: '📄 ';
        font-size: 16px;
    }

    [data-theme="dark"] .detail-message {
        background: var(--bg-detail-alt-service);
    }

    /* ================= DOWNLOAD MODAL ================= */
    .download-service-info {
        background: var(--bg-detail-service);
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 20px;
        border: 1px solid var(--border-service);
        transition: all 0.3s ease;
    }

    .download-service-info .service-name-display {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-service);
        transition: color 0.3s ease;
    }
    .download-service-info .service-meta {
        font-size: 13px;
        color: var(--text-secondary-service);
        margin-top: 4px;
        transition: color 0.3s ease;
    }
    .download-service-info .service-meta span { margin-right: 16px; }

    .download-period {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    .period-btn {
        padding: 7px 16px;
        border: 2px solid var(--border-service);
        border-radius: 8px;
        background: var(--bg-card-service);
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary-service);
        transition: all 0.2s ease;
        font-family: inherit;
    }
    .period-btn:hover {
        border-color: #059669;
        color: #059669;
    }
    .period-btn.active {
        border-color: #059669;
        background: #ecfdf5;
        color: #065f46;
        font-weight: 600;
    }
    [data-theme="dark"] .period-btn.active {
        background: #064e3b;
        color: #6ee7b7;
    }

    .period-btn.disabled {
        opacity: 0.4 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        position: relative;
    }
    .period-btn.disabled::after {
        content: ' 🔒';
        font-size: 10px;
    }

    .download-date-range {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
    .download-date-range .date-group { flex: 1; min-width: 130px; }
    .download-date-range .date-group label {
        font-size: 12px;
        font-weight: 500;
        color: var(--text-secondary-service);
        display: block;
        margin-bottom: 4px;
        transition: color 0.3s ease;
    }
    .download-date-range .date-group input {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid var(--border-service);
        border-radius: 8px;
        font-size: 13px;
        background: var(--bg-input-service);
        color: var(--text-service);
        transition: all 0.2s ease;
        outline: none;
        font-family: inherit;
    }
    .download-date-range .date-group input:focus {
        border-color: #059669;
        background: var(--bg-input-service);
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
    }
    .download-date-range .date-group input::-webkit-calendar-picker-indicator {
        filter: var(--date-picker-filter);
    }
    [data-theme="dark"] .download-date-range .date-group input {
        --date-picker-filter: invert(1);
    }

    .download-date-range .date-group input.error {
        border-color: #dc2626 !important;
        background: #fef2f2 !important;
    }

    .download-date-range .date-group input.valid {
        border-color: #10b981 !important;
    }

    .date-error-message {
        color: #dc2626;
        font-size: 12px;
        margin-top: 4px;
        animation: fadeIn 0.3s ease;
    }

    [data-theme="dark"] .download-date-range .date-group input.error {
        background: #7f1d1d !important;
        border-color: #f87171 !important;
    }

    /* ================= FORM DALAM MODAL ================= */
    .modal-body .form-group { margin-bottom: 18px; }
    .modal-body .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-service);
        margin-bottom: 6px;
        transition: color 0.3s ease;
    }
    .modal-body .form-group label .required { color: #dc2626; margin-left: 2px; }
    .modal-body .form-group .helper-text {
        font-size: 12px;
        color: var(--text-muted-service);
        margin-top: 4px;
        transition: color 0.3s ease;
    }

    .modal-body .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-service);
        border-radius: 8px;
        font-size: 14px;
        color: var(--text-service);
        transition: all 0.2s ease;
        background: var(--bg-input-service);
        outline: none;
        font-family: inherit;
    }
    .modal-body .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        background: var(--bg-input-service);
    }
    .modal-body .form-control.error { border-color: #dc2626; }
    .modal-body .form-control::placeholder {
        color: var(--text-muted-service);
    }

    .modal-body select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px;
        cursor: pointer;
    }
    [data-theme="dark"] .modal-body select.form-control {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394a3b8' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    }

    .modal-body .error-message {
        color: #dc2626;
        font-size: 13px;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* ================= BUTTONS MODAL ================= */
    .btn-submit-modal {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white;
        padding: 10px 28px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: inherit;
    }
    .btn-submit-modal:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.35);
    }
    .btn-submit-modal:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }
    .btn-submit-modal.edit-mode {
        background: linear-gradient(135deg, #d97706, #b45309);
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);
    }
    .btn-submit-modal.edit-mode:hover {
        box-shadow: 0 6px 20px rgba(217, 119, 6, 0.35);
    }

    .btn-download-modal {
        background: linear-gradient(135deg, #059669, #047857);
        color: white;
        padding: 10px 28px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: inherit;
    }
    .btn-download-modal:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.35);
    }
    .btn-download-modal:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .btn-cancel-modal {
        background: var(--bg-hover-service);
        color: var(--text-secondary-service);
        padding: 10px 24px;
        border: 1px solid var(--border-service);
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: inherit;
    }
    .btn-cancel-modal:hover {
        background: var(--border-service);
        transform: translateY(-1px);
    }

    .btn-cancel-modal:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
    }

    /* ================= EMPTY STATE ================= */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted-service);
    }
    .empty-state .empty-icon {
        font-size: 48px;
        display: block;
        margin-bottom: 12px;
        opacity: 0.6;
    }
    .empty-state h3 {
        color: var(--text-service);
        font-size: 18px;
        margin: 0 0 8px;
        font-weight: 600;
        transition: color 0.3s ease;
    }
    .empty-state p {
        margin: 0;
        font-size: 14px;
        color: var(--text-secondary-service);
        transition: color 0.3s ease;
    }

    .btn-empty-primary {
        background: #4f46e5;
        color: white;
        padding: 10px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        cursor: pointer;
        font-family: inherit;
        margin-top: 16px;
    }
    .btn-empty-primary:hover {
        background: #4338ca;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }

    /* ================= ANIMATIONS ================= */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.96);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stat-item {
        animation: fadeInUp 0.5s ease forwards;
    }
    .stat-item:nth-child(1) { animation-delay: 0.05s; }
    .stat-item:nth-child(2) { animation-delay: 0.10s; }
    .stat-item:nth-child(3) { animation-delay: 0.15s; }
    .stat-item:nth-child(4) { animation-delay: 0.20s; }
    .stat-item:nth-child(5) { animation-delay: 0.25s; }

    /* ================= SEARCH HIGHLIGHT ================= */
    mark {
        background: #fbbf24;
        padding: 0 2px;
        border-radius: 2px;
        color: #0f172a;
    }

    [data-theme="dark"] mark {
        background: #f59e0b;
        color: #0f172a;
    }

    /* ================= 🔥 MULTI DOWNLOAD MODAL ================= */
    .multi-download-body {
        max-height: 65vh;
        overflow-y: auto;
    }
    .multi-download-body::-webkit-scrollbar { width: 5px; }
    .multi-download-body::-webkit-scrollbar-track {
        background: var(--bg-hover-service);
        border-radius: 10px;
    }
    .multi-download-body::-webkit-scrollbar-thumb {
        background: var(--text-muted-service);
        border-radius: 10px;
    }

    .service-list-item {
        display: flex;
        align-items: center;
        padding: 10px 14px;
        border-radius: 8px;
        border: 2px solid var(--border-service);
        gap: 12px;
        flex-wrap: wrap;
        transition: all 0.2s ease;
        background: var(--bg-card-service);
        margin-bottom: 6px;
        cursor: pointer;
    }
    .service-list-item:hover {
        background: var(--bg-hover-service);
        border-color: var(--text-muted-service);
    }
    .service-list-item.selected {
        border-color: #059669;
        background: #ecfdf5;
    }
    [data-theme="dark"] .service-list-item.selected {
        background: #064e3b;
        border-color: #059669;
    }
    .service-list-item .service-info-multi {
        flex: 1;
        min-width: 150px;
    }
    .service-list-item .service-info-multi .name {
        font-weight: 600;
        color: var(--text-service);
        font-size: 14px;
    }
    .service-list-item .service-info-multi .meta {
        font-size: 11px;
        color: var(--text-muted-service);
    }
    .service-list-item .period-select-multi {
        padding: 4px 10px;
        border: 1px solid var(--border-service);
        border-radius: 6px;
        background: var(--bg-input-service);
        color: var(--text-service);
        font-size: 12px;
        min-width: 150px;
        cursor: pointer;
    }
    .service-list-item .period-select-multi:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .service-list-item .period-select-multi option:disabled {
        color: #6b7280;
    }

    .checkbox-custom {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #059669;
        flex-shrink: 0;
    }

    .checkbox-custom:checked + .service-info-multi .name {
        color: #059669;
    }

    .selected-check {
        display: none;
        color: #059669;
        font-size: 18px;
        margin-left: 4px;
    }

    .service-list-item.selected .selected-check {
        display: inline-block;
    }

    .format-group {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    .format-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 8px 18px;
        border: 2px solid var(--border-service);
        border-radius: 8px;
        background: var(--bg-card-service);
        transition: all 0.2s ease;
        font-weight: 500;
        font-size: 13px;
    }
    .format-group label:hover {
        border-color: #059669;
    }
    .format-group label.active-format {
        border-color: #059669;
        background: #ecfdf5;
        color: #065f46;
    }
    [data-theme="dark"] .format-group label.active-format {
        background: #064e3b;
        color: #6ee7b7;
    }
    .format-group label input[type="radio"] {
        accent-color: #059669;
        width: 16px;
        height: 16px;
    }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 1024px) {
        .stats-bar { grid-template-columns: repeat(3, 1fr); }
        .detail-grid { grid-template-columns: 1fr; }
        .search-wrapper { max-width: 100%; }
        .service-list-item { flex-wrap: wrap; }
    }

    @media (max-width: 768px) {
        .service-container { padding: 16px; }
        .service-header {
            padding: 20px 24px;
            flex-direction: column;
            align-items: stretch;
            border-radius: 12px;
        }
        .service-header h1 { font-size: 20px; }
        .stats-bar { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .stat-item .stat-number { font-size: 22px; }
        .stat-item { padding: 14px 16px; }
        .table-scroll { padding: 0 12px 12px; }
        .table-container thead th,
        .table-container tbody td { padding: 10px 10px; font-size: 12px; }
        .pagination-wrapper { flex-direction: column; align-items: stretch; padding: 12px 16px; }
        .pagination-links { justify-content: center; }
        .modal-content { width: 95%; }
        .modal-footer { flex-direction: column; }
        .btn-submit-modal, .btn-cancel-modal, .btn-download-modal { justify-content: center; }
        .toast-container { top: 16px; right: 16px; max-width: calc(100% - 32px); }
        .detail-grid { grid-template-columns: 1fr; }
        .action-buttons { flex-wrap: wrap; }
        .download-date-range { flex-direction: column; }
        .download-date-range .date-group { min-width: 100%; }
        .download-period { justify-content: center; }
        .service-target { max-width: 120px; font-size: 11px; }
        .auto-refresh-timer {
            bottom: 10px;
            right: 10px;
            padding: 6px 12px;
            font-size: 10px;
        }
        .auto-refresh-timer .countdown {
            font-size: 12px;
            min-width: 30px;
        }
        .uptime-value { font-size: 14px; }
        .table-header {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }
        .table-header-right {
            justify-content: space-between;
        }
        .perpage-selector {
            font-size: 12px;
        }
        .perpage-selector select {
            padding: 4px 8px;
            font-size: 12px;
        }
        .search-wrapper {
            flex-wrap: wrap;
        }
        .search-wrapper .btn-search,
        .search-wrapper .btn-reset {
            flex: 1;
            justify-content: center;
            padding: 8px 12px;
            font-size: 12px;
        }
        .wa-interval-wrapper {
            width: 100%;
            justify-content: center;
            padding: 6px 12px;
        }
        .wa-interval-wrapper select {
            min-width: 80px;
            font-size: 11px;
            padding: 4px 24px 4px 8px;
        }
        .btn-toggle-archive {
            font-size: 12px;
            padding: 6px 12px;
        }
        .btn-download-multi {
            font-size: 12px;
            padding: 8px 16px;
            width: 100%;
            justify-content: center;
        }
        .service-list-item {
            padding: 6px 10px;
            gap: 8px;
        }
        .service-list-item .service-info-multi .name {
            font-size: 12px;
        }
        .service-list-item .service-info-multi .meta {
            font-size: 10px;
        }
        .service-list-item .period-select-multi {
            font-size: 11px;
            min-width: 120px;
            padding: 3px 8px;
        }
        .format-group label {
            font-size: 12px;
            padding: 6px 12px;
        }
    }

    @media (max-width: 480px) {
        .stats-bar { grid-template-columns: 1fr 1fr; gap: 8px; }
        .stat-item { padding: 12px 14px; border-radius: 10px; }
        .stat-item .stat-number { font-size: 18px; }
        .stat-item .stat-label { font-size: 10px; }
        .service-header h1 { font-size: 17px; }
        .btn-primary { font-size: 12px; padding: 8px 16px; }
        .modal-header h2 { font-size: 15px; }
        .modal-body { padding: 14px; }
        .status-badge { font-size: 9px; padding: 3px 10px; gap: 5px; }
        .status-badge .status-dot { width: 6px; height: 6px; }
        .btn-action { font-size: 10px; padding: 4px 8px; }
        .btn-check { font-size: 10px; padding: 4px 8px; }
        .btn-archive, .btn-restore, .btn-delete-permanent { font-size: 10px; padding: 4px 8px; }
        .detail-item .detail-value { font-size: 13px; }
        .uptime-value { font-size: 12px; }
        .perpage-selector {
            font-size: 11px;
        }
        .perpage-selector select {
            padding: 3px 6px;
            font-size: 11px;
        }
        .pagination-links .page-link {
            padding: 4px 8px;
            font-size: 11px;
            min-width: 30px;
        }
        .search-wrapper .btn-search,
        .search-wrapper .btn-reset {
            font-size: 11px;
            padding: 6px 10px;
        }
        .search-wrapper .search-input-wrap input {
            padding: 8px 10px 8px 32px;
            font-size: 12px;
        }
        .search-wrapper .search-input-wrap .search-icon {
            font-size: 13px;
            left: 10px;
        }
        .btn-toggle-archive {
            font-size: 11px;
            padding: 5px 10px;
        }
        .service-list-item .period-select-multi {
            font-size: 10px;
            min-width: 100px;
        }
        .format-group {
            gap: 8px;
        }
        .format-group label {
            font-size: 11px;
            padding: 4px 10px;
        }
    }
</style>

<!-- ================= DATA SERVICE UNTUK INSTANT EDIT ================= -->
<script>
    var servicesMap = {};
    @foreach($services as $service)
        servicesMap[{{ $service->id }}] = {!! json_encode([
            'id' => $service->id,
            'name' => $service->name,
            'target' => $service->target,
            'type' => $service->type ?? 'http',
            'is_archived' => (bool) $service->is_archived
        ]) !!};
    @endforeach
</script>

<div class="service-container">
    <!-- ================= TOAST CONTAINER ================= -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- ================= CUSTOM CONFIRM MODAL ================= -->
    <div class="custom-modal-overlay" id="customConfirmModal">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <div class="modal-icon" id="confirmIcon">⚠️</div>
                <h3 id="confirmTitle">Konfirmasi</h3>
                <p id="confirmMessage">Apakah Anda yakin?</p>
            </div>
            <div class="custom-modal-body">
                <p id="confirmDetail" style="font-size: 14px; color: var(--text-secondary-service); text-align: center;"></p>
                <div id="confirmInfo" style="display: none;" class="info-text"></div>
            </div>
            <div class="custom-modal-footer">
                <button class="btn-modal btn-cancel" onclick="closeConfirmModal()">✕ Batal</button>
                <button class="btn-modal" id="confirmBtn" onclick="executeConfirm()">✔ Ya</button>
            </div>
        </div>
    </div>

    <!-- ================= HEADER ================= -->
    <div class="service-header">
        <div class="header-left">
            <div class="header-icon">⚙️</div>
            <div class="header-text">
                <h1>Manajemen Service</h1>
                <div class="header-subtitle">Kelola dan pantau seluruh layanan Anda</div>
            </div>
        </div>
        <div class="header-actions">
            <!-- 🔥 WA INTERVAL DROPDOWN -->
            <div class="wa-interval-wrapper">
                <span class="wa-label">
                    <span class="wa-icon">📱</span> WA Interval:
                </span>
                <select id="waInterval" onchange="changeWaInterval(this.value)">
                    <option value="0" {{ ($waInterval ?? 5) == 0 ? 'selected' : '' }}>🚨 Kirim Langsung</option>
                    <option value="5" {{ ($waInterval ?? 5) == 5 ? 'selected' : '' }}>⏱️ 5 Menit</option>
                    <option value="10" {{ ($waInterval ?? 5) == 10 ? 'selected' : '' }}>⏱️ 10 Menit</option>
                    <option value="15" {{ ($waInterval ?? 5) == 15 ? 'selected' : '' }}>⏱️ 15 Menit</option>
                    <option value="20" {{ ($waInterval ?? 5) == 20 ? 'selected' : '' }}>⏱️ 20 Menit</option>
                    <option value="30" {{ ($waInterval ?? 5) == 30 ? 'selected' : '' }}>⏱️ 30 Menit</option>
                    <option value="60" {{ ($waInterval ?? 5) == 60 ? 'selected' : '' }}>⏰ 1 Jam</option>
                    <option value="120" {{ ($waInterval ?? 5) == 120 ? 'selected' : '' }}>⏰ 2 Jam</option>
                    <option value="240" {{ ($waInterval ?? 5) == 240 ? 'selected' : '' }}>⏰ 4 Jam</option>
                    <option value="480" {{ ($waInterval ?? 5) == 480 ? 'selected' : '' }}>⏰ 8 Jam</option>
                    <option value="1440" {{ ($waInterval ?? 5) == 1440 ? 'selected' : '' }}>⏰ 24 Jam</option>
                </select>
            </div>

            <!-- 🔥 TOMBOL DOWNLOAD MULTI SERVICE -->
            <button class="btn-download-multi" onclick="openMultiDownloadModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-linecap="round"/>
                </svg>
                📥 Download Laporan
            </button>

            <button class="btn-primary" onclick="openCreateModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
                Tambah Service
            </button>
        </div>
    </div>

    <!-- ================= STATS ================= -->
    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-number purple">{{ $totalServices ?? 0 }}</span>
            <span class="stat-label">Total Service</span>
        </div>
        <div class="stat-item">
            <span class="stat-number green">{{ $totalUp ?? 0 }}</span>
            <span class="stat-label">Aktif (UP)</span>
        </div>
        <div class="stat-item">
            <span class="stat-number yellow">{{ $totalWarning ?? 0 }}</span>
            <span class="stat-label">Peringatan</span>
        </div>
        <div class="stat-item">
            <span class="stat-number red">{{ $totalDown ?? 0 }}</span>
            <span class="stat-label">Nonaktif (DOWN)</span>
        </div>
        <div class="stat-item">
            <span class="stat-number archive">{{ $totalArchived ?? 0 }}</span>
            <span class="stat-label">📦 Arsip</span>
        </div>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="table-container">
        <div class="table-header">
            <div class="header-left">
                <h2>
                    @if($showArchived ?? false)
                        📦 Daftar Arsip
                    @else
                        📋 Daftar Service
                    @endif
                </h2>
                <button class="btn-toggle-archive {{ ($showArchived ?? false) ? 'active' : '' }}" 
                        onclick="toggleArchive()">
                    @if($showArchived ?? false)
                        🏠 Kembali ke Aktif
                    @else
                        📦 Arsip <span class="archive-count">{{ $totalArchived ?? 0 }}</span>
                    @endif
                </button>
            </div>
            
            <!-- ================= SEARCH BOX ================= -->
            <div class="search-wrapper">
                <div class="search-input-wrap">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchService" placeholder="Cari service berdasarkan nama atau target..." autocomplete="off">
                </div>
                <button onclick="searchServices()" class="btn-search" id="btnSearch">🔍 Cari</button>
                <button onclick="resetSearch()" class="btn-reset">↺ Reset</button>
            </div>

            <div class="table-header-right">
                <div class="perpage-selector">
                    <label for="perPage">Tampilkan:</label>
                    <select id="perPage" onchange="changePerPage(this.value)">
                        <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ ($perPage ?? 10) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>data</span>
                </div>
                <span class="table-info" id="tableInfo">
                    Menampilkan <strong>{{ $services->firstItem() ?? 0 }}</strong> - <strong>{{ $services->lastItem() ?? 0 }}</strong> dari <strong>{{ $services->total() }}</strong> service
                </span>
            </div>
        </div>

        <div class="search-status" id="searchStatus">
            <div class="status-spinner"></div>
            <span class="status-text" id="searchStatusText">🔍 Sedang mencari...</span>
            <button class="status-cancel" onclick="cancelSearch()" title="Batalkan pencarian">✕</button>
        </div>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="width: 45px;" class="sortable-header" data-sort="no" onclick="sortTable('no')">
                            No
                            <span class="sort-icon">
                                <span class="arrow-up">▲</span>
                                <span class="arrow-down">▼</span>
                            </span>
                        </th>
                        <th class="sortable-header active-asc" data-sort="name" onclick="sortTable('name')">
                            Nama Service
                            <span class="sort-icon">
                                <span class="arrow-up">▲</span>
                                <span class="arrow-down">▼</span>
                            </span>
                        </th>
                        <th class="sortable-header" data-sort="target" onclick="sortTable('target')">
                            Target
                            <span class="sort-icon">
                                <span class="arrow-up">▲</span>
                                <span class="arrow-down">▼</span>
                            </span>
                        </th>
                        <th style="width: 110px;" class="sortable-header" data-sort="status" onclick="sortTable('status')">
                            Status
                            <span class="sort-icon">
                                <span class="arrow-up">▲</span>
                                <span class="arrow-down">▼</span>
                            </span>
                        </th>
                        <th style="width: 100px;" class="sortable-header" data-sort="uptime" onclick="sortTable('uptime')">
                            Uptime 30d
                            <span class="sort-icon">
                                <span class="arrow-up">▲</span>
                                <span class="arrow-down">▼</span>
                            </span>
                        </th>
                        <th style="width: 150px;" class="sortable-header" data-sort="last_check" onclick="sortTable('last_check')">
                            Terakhir Diperiksa
                            <span class="sort-icon">
                                <span class="arrow-up">▲</span>
                                <span class="arrow-down">▼</span>
                            </span>
                        </th>
                        <th style="width: 320px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($services as $index => $service)
                        @php
                            $colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5', 'color-6', 'color-7', 'color-8'];
                            $colorClass = $colors[$index % count($colors)];
                            $initials = strtoupper(substr($service->name, 0, 2));
                            $statusLabel = $service->last_status ?? 'UNKNOWN';
                            $no = ($services->currentPage() - 1) * $services->perPage() + $loop->iteration;
                            $lastChecked = $service->last_check_at ?? $service->updated_at;
                            $uptime = $service->uptime ?? 0;
                            $uptimeColor = $uptime >= 70 ? 'green' : ($uptime >= 50 ? 'yellow' : 'red');
                            $isArchived = $service->is_archived ?? 0;
                        @endphp
                        <tr data-service-id="{{ $service->id }}" data-archived="{{ $isArchived }}" data-service-name="{{ $service->name }}" data-service-created="{{ $service->created_at }}">
                            <td><span class="service-no">{{ $no }}</span></td>
                            <td>
                                <div class="service-info">
                                    <div class="service-avatar {{ $colorClass }}">{{ $initials }}</div>
                                    <div>
                                        <div class="service-name">{{ $service->name }}</div>
                                        <span class="service-type">{{ strtoupper($service->type ?? 'HTTP') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="service-target">{{ $service->target }}</span></td>
                            <td>
                                @if($isArchived)
                                    <span class="status-badge archived" id="status-{{ $service->id }}">
                                        <span class="status-dot"></span> 📦 ARSIP
                                    </span>
                                @elseif($statusLabel == 'UP')
                                    <span class="status-badge up" id="status-{{ $service->id }}">
                                        <span class="status-dot"></span> UP
                                    </span>
                                @elseif($statusLabel == 'DOWN')
                                    <span class="status-badge down" id="status-{{ $service->id }}">
                                        <span class="status-dot"></span> DOWN
                                    </span>
                                @elseif($statusLabel == 'WARNING')
                                    <span class="status-badge warning" id="status-{{ $service->id }}">
                                        <span class="status-dot"></span> WARNING
                                    </span>
                                @else
                                    <span class="status-badge unknown" id="status-{{ $service->id }}">
                                        <span class="status-dot"></span> UNKNOWN
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($isArchived)
                                    <div class="uptime-value" style="color: var(--text-muted-service);">-</div>
                                    <div class="uptime-bar"><div class="uptime-fill" style="width: 0%; background: var(--text-muted-service);"></div></div>
                                @else
                                    <div class="uptime-value {{ $uptimeColor }}">{{ number_format($uptime, 2) }}%</div>
                                    <div class="uptime-bar">
                                        <div class="uptime-fill {{ $uptimeColor }}" style="width: {{ $uptime }}%;"></div>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 13px; color: var(--text-secondary-service); font-family: 'Courier New', monospace; font-weight: 600;" id="time-{{ $service->id }}">
                                    {{ $lastChecked ? \Carbon\Carbon::parse($lastChecked)->setTimezone('Asia/Jakarta')->format('H:i:s') : '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @if($isArchived)
                                        <button onclick="confirmRestore({{ $service->id }}, '{{ addslashes($service->name) }}')" class="btn-restore" title="Pulihkan">🔄 Pulihkan</button>
                                        <button onclick="confirmDeletePermanent({{ $service->id }}, '{{ addslashes($service->name) }}')" class="btn-delete-permanent" title="Hapus Permanen">🗑️ Hapus</button>
                                    @else
                                        <button onclick="openDetailModal({{ $service->id }})" class="btn-action btn-detail" title="Detail">👁️</button>
                                        <button onclick="openDownloadModal({{ $service->id }}, '{{ addslashes($service->name) }}')" class="btn-action btn-download" title="Download PDF">📥</button>
                                        <button onclick="confirmArchive({{ $service->id }}, '{{ addslashes($service->name) }}')" class="btn-archive" title="Arsipkan">📦 Arsip</button>
                                        <button onclick="openEditModal({{ $service->id }})" class="btn-action btn-edit" title="Edit">✏️</button>
                                        <button onclick="confirmDelete({{ $service->id }}, '{{ addslashes($service->name) }}')" class="btn-action btn-delete" title="Hapus">🗑️</button>
                                        <button onclick="checkService({{ $service->id }})" class="btn-check" title="Check Now" id="checkBtn{{ $service->id }}">🔄</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <span class="empty-icon">📭</span>
                                    <h3>
                                        @if($showArchived ?? false)
                                            Tidak Ada Service Diarsip
                                        @else
                                            Belum Ada Service
                                        @endif
                                    </h3>
                                    <p>
                                        @if($showArchived ?? false)
                                            Belum ada service yang diarsipkan
                                        @else
                                            Mulai dengan menambahkan service pertama Anda
                                        @endif
                                    </p>
                                    @if(!($showArchived ?? false))
                                        <button onclick="openCreateModal()" class="btn-empty-primary">+ Tambah Service</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($services, 'hasPages') && $services->hasPages())
        <div class="pagination-wrapper" id="paginationWrapper">
            <div class="pagination-info">
                Menampilkan <strong>{{ $services->firstItem() ?? 0 }}</strong> - <strong>{{ $services->lastItem() ?? 0 }}</strong> dari <strong>{{ $services->total() }}</strong> data
            </div>
            <div class="pagination-links">
                @if($services->onFirstPage())
                    <span class="page-link disabled">‹</span>
                @else
                    <a href="{{ $services->previousPageUrl() }}" class="page-link">‹</a>
                @endif

                @php
                    $currentPage = $services->currentPage();
                    $lastPage = $services->lastPage();
                    $windowSize = 5;
                    $start = max(1, $currentPage - $windowSize);
                    $end = min($lastPage, $currentPage + $windowSize);
                    if ($end - $start + 1 < 10) {
                        if ($start == 1) {
                            $end = min($lastPage, 10);
                        } else {
                            $start = max(1, $lastPage - 9);
                            $end = $lastPage;
                        }
                    }
                @endphp

                @if($start > 1)
                    <a href="{{ $services->url(1) }}" class="page-link">1</a>
                    @if($start > 2)
                        <span class="page-dots">…</span>
                    @endif
                @endif

                @foreach(range($start, $end) as $page)
                    @if($page == $services->currentPage())
                        <span class="page-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $services->url($page) }}" class="page-link">{{ $page }}</a>
                    @endif
                @endforeach

                @if($end < $lastPage)
                    @if($end < $lastPage - 1)
                        <span class="page-dots">…</span>
                    @endif
                    <a href="{{ $services->url($lastPage) }}" class="page-link">{{ $lastPage }}</a>
                @endif

                @if($services->hasMorePages())
                    <a href="{{ $services->nextPageUrl() }}" class="page-link">›</a>
                @else
                    <span class="page-link disabled">›</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- ================= AUTO REFRESH TIMER ================= -->
<div class="auto-refresh-timer" id="autoRefreshTimer">
    <span class="icon">🔄</span>
    <span class="label">Refresh</span>
    <span class="countdown" id="countdownTimer">0:30</span>
</div>

<!-- ================= MODAL DETAIL ================= -->
<div class="modal-overlay" id="detailModal" onclick="if(event.target === this) closeDetailModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2>
                <span class="modal-icon" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">📊</span>
                <span id="detailModalTitle">Detail Service</span>
            </h2>
            <button class="modal-close" onclick="closeDetailModal()">&times;</button>
        </div>
        <div class="modal-body" id="detailModalBody">
            <div style="text-align: center; padding: 40px 0; color: var(--text-secondary-service);">
                <span style="font-size: 32px; display: block; margin-bottom: 8px;">⏳</span>
                <p>Memuat data...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="closeDetailModal()">✕ Tutup</button>
            <button class="btn-submit-modal" id="refreshDetailBtn" onclick="refreshDetail()" style="background: linear-gradient(135deg, #059669, #10b981);">🔄 Refresh</button>
        </div>
    </div>
</div>

<!-- ================= MODAL DOWNLOAD SINGLE ================= -->
<div class="modal-overlay" id="downloadModal" onclick="if(event.target === this) closeDownloadModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2>
                <span class="modal-icon" style="background: linear-gradient(135deg, #059669, #10b981);">📥</span>
                <span id="downloadModalTitle">Download Laporan PDF</span>
            </h2>
            <button class="modal-close" onclick="closeDownloadModal()">&times;</button>
        </div>
        <div class="modal-body" id="downloadModalBody">
            <div class="download-service-info">
                <div class="service-name-display" id="downloadServiceName">Memuat...</div>
                <div class="service-meta">
                    <span id="downloadServiceTarget">-</span>
                    <span id="downloadServiceType">-</span>
                </div>
            </div>

            <div id="downloadNotice" style="background: var(--bg-info-box-service); border: 1px solid var(--border-info-box-service); border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: var(--text-info-box-service);">
                📌 <strong>Info:</strong> Memuat informasi service...
            </div>

            <div class="form-group">
                <label>Periode Laporan</label>
                <div class="download-period" id="periodSelector">
                    <button class="period-btn" data-period="7" onclick="selectPeriod(this, 7)">7 Hari</button>
                    <button class="period-btn" data-period="14" onclick="selectPeriod(this, 14)">14 Hari</button>
                    <button class="period-btn" data-period="30" onclick="selectPeriod(this, 30)">30 Hari</button>
                    <button class="period-btn" data-period="60" onclick="selectPeriod(this, 60)">60 Hari</button>
                    <button class="period-btn" data-period="90" onclick="selectPeriod(this, 90)">90 Hari</button>
                </div>
                <div class="helper-text">Pilih rentang waktu laporan (periode yang tidak tersedia akan otomatis dinonaktifkan)</div>
            </div>

            <div class="form-group">
                <label>Tanggal Kustom</label>
                <div id="serviceAgeInfo" style="background: var(--bg-info-box-service); border: 1px solid var(--border-info-box-service); border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; font-size: 13px; color: var(--text-info-box-service); display: none;">
                    <span id="serviceAgeText">Memuat informasi service...</span>
                </div>
                <div class="download-date-range">
                    <div class="date-group">
                        <label>Dari Tanggal <span class="required" style="color: #dc2626;">*</span></label>
                        <input type="date" id="dateFrom" class="date-input" min="" max="" required>
                        <div class="date-error-message" style="display: none; color: #dc2626; font-size: 12px; margin-top: 4px;"></div>
                    </div>
                    <div class="date-group">
                        <label>Sampai Tanggal <span class="required" style="color: #dc2626;">*</span></label>
                        <input type="date" id="dateTo" class="date-input" min="" max="" required>
                        <div class="date-error-message" style="display: none; color: #dc2626; font-size: 12px; margin-top: 4px;"></div>
                    </div>
                </div>
                <div class="helper-text">
                    📅 Pilih rentang tanggal sesuai kebutuhan. Tanggal otomatis dibatasi sesuai umur service.
                </div>
            </div>

            <div id="downloadLoading" style="display: none; text-align: center; padding: 20px;">
                <span style="font-size: 24px; display: block; margin-bottom: 8px;">⏳</span>
                <p style="color: var(--text-secondary-service);">Sedang memproses laporan PDF...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="closeDownloadModal()">✕ Batal</button>
            <button class="btn-download-modal" id="btnDownloadNow" onclick="downloadReport()">📥 Download PDF</button>
        </div>
    </div>
</div>

<!-- ================= 🔥🔥🔥 MODAL DOWNLOAD MULTI SERVICE ================= -->
<div class="modal-overlay" id="multiDownloadModal" onclick="if(event.target === this) closeMultiDownloadModal()">
    <div class="modal-content" style="max-width: 850px;">
        <div class="modal-header">
            <h2>
                <span class="modal-icon" style="background: linear-gradient(135deg, #059669, #10b981);">📥</span>
                <span>Download Laporan Service</span>
            </h2>
            <button class="modal-close" onclick="closeMultiDownloadModal()">&times;</button>
        </div>
        <div class="modal-body multi-download-body" id="multiDownloadBody">
            
            <!-- ================= PANDUAN ================= -->
            <div style="background: var(--bg-info-box-service); border: 1px solid var(--border-info-box-service); border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px; color: var(--text-info-box-service);">
                📌 <strong>Panduan:</strong>
                <ul style="margin: 6px 0 0 18px; padding-left: 0;">
                    <li>Pilih <strong>satu atau lebih</strong> service yang ingin di-download</li>
                    <li>Setiap service bisa memilih periode <strong>sesuai umurnya</strong></li>
                    <li>Hasil laporan <strong>1 file</strong> (PDF atau Excel) berisi SEMUA service</li>
                    <li>Periode yang tidak tersedia <strong>otomatis dinonaktifkan</strong></li>
                </ul>
            </div>

            <!-- ================= TOMBOL AKSI CEPAT ================= -->
            <div style="display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                <button type="button" onclick="selectAllMulti(true)" class="btn-action" style="background: #4f46e5; color: white; padding: 6px 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 500;">
                    ✅ Pilih Semua
                </button>
                <button type="button" onclick="selectAllMulti(false)" class="btn-action" style="background: #6b7280; color: white; padding: 6px 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 500;">
                    ✖ Batal Pilih
                </button>
                <button type="button" onclick="selectByStatusMulti('UP')" class="btn-action" style="background: #059669; color: white; padding: 6px 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 500;">
                    📊 Aktif (UP)
                </button>
                <button type="button" onclick="selectByStatusMulti('DOWN')" class="btn-action" style="background: #dc2626; color: white; padding: 6px 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 500;">
                    🔴 DOWN
                </button>
            </div>

            <!-- ================= LIST SERVICE ================= -->
            <div class="form-group">
                <label>
                    Pilih Service & Atur Periode
                    <span class="required">*</span>
                    <span style="font-weight: 400; font-size: 12px; color: var(--text-muted-service); margin-left: 8px;">
                        (pilih minimal 1)
                    </span>
                </label>
                
                <div id="multiServiceList" style="max-height: 350px; overflow-y: auto; border: 1px solid var(--border-service); border-radius: 8px; padding: 8px; background: var(--bg-hover-service);">
                    @foreach($services as $service)
                        @if(!($service->is_archived ?? false))
                            @php
                                $createdAt = \Carbon\Carbon::parse($service->created_at);
                                $now = \Carbon\Carbon::now();
                                $ageInDays = $createdAt->diffInDays($now);
                                $availableDays = $ageInDays + 1;
                                $statusLabel = $service->last_status ?? 'UNKNOWN';
                            @endphp
                            <div class="service-list-item" data-service-id="{{ $service->id }}" onclick="toggleCheckbox(this)">
                                <input type="checkbox" 
                                       class="multi-service-checkbox checkbox-custom" 
                                       data-id="{{ $service->id }}"
                                       data-name="{{ $service->name }}"
                                       data-created="{{ $service->created_at }}"
                                       data-age="{{ $ageInDays }}"
                                       data-available="{{ $availableDays }}"
                                       data-status="{{ $statusLabel }}"
                                       onchange="updateMultiSelection(event)"
                                       onclick="event.stopPropagation();">
                                
                                <div class="service-info-multi">
                                    <div class="name">
                                        {{ $service->name }}
                                        <span class="selected-check">✅</span>
                                    </div>
                                    <div class="meta">
                                        🕐 {{ $ageInDays }} hari | 
                                        📅 {{ $createdAt->format('d M Y') }} - {{ $now->format('d M Y') }}
                                        <span style="margin-left: 8px; background: var(--bg-card-service); padding: 1px 8px; border-radius: 10px; border: 1px solid var(--border-service);">
                                            📊 {{ $availableDays }} hari data
                                        </span>
                                    </div>
                                </div>
                                
                                <span class="status-badge {{ strtolower($statusLabel) }}" style="font-size: 10px; padding: 2px 10px;">
                                    {{ $statusLabel }}
                                </span>
                                
                                <select class="period-select-multi" data-id="{{ $service->id }}" onclick="event.stopPropagation();">
                                    <option value="all">📚 Semua Data ({{ $availableDays }} hari)</option>
                                    @php
                                        $periodOptions = [7, 14, 30, 60, 90];
                                    @endphp
                                    @foreach($periodOptions as $days)
                                        @if($availableDays >= $days)
                                            <option value="{{ $days }}">{{ $days }} Hari Terakhir</option>
                                        @endif
                                    @endforeach
                                    @foreach($periodOptions as $days)
                                        @if($availableDays < $days)
                                            <option value="{{ $days }}" disabled>❌ {{ $days }} Hari (tidak cukup)</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    @endforeach
                </div>
                
                <div class="helper-text" id="multiSelectionInfo">
                    <span id="multiSelectedCount">0</span> service dipilih
                </div>
            </div>

            <!-- ================= FORMAT LAPORAN ================= -->
            <div class="form-group">
                <label>Format Laporan <span class="required">*</span></label>
                <div class="format-group">
                    <label class="active-format" id="formatPdfLabel">
                        <input type="radio" name="multi_report_format" value="pdf" checked onchange="updateMultiFormat(this)">
                        📄 PDF
                    </label>
                    <label id="formatExcelLabel">
                        <input type="radio" name="multi_report_format" value="excel" onchange="updateMultiFormat(this)">
                        📊 Excel (CSV)
                    </label>
                </div>
                <div class="helper-text">
                    📄 PDF untuk laporan rapi dengan detail per service (1 file)<br>
                    📊 Excel untuk data mentah yang bisa diolah lebih lanjut (1 file, multiple sheets)
                </div>
            </div>

            <!-- ================= LOADING ================= -->
            <div id="multiDownloadLoading" style="display: none; text-align: center; padding: 20px;">
                <span style="font-size: 24px; display: block; margin-bottom: 8px;">⏳</span>
                <p style="color: var(--text-secondary-service);">Sedang memproses laporan...</p>
                <p style="font-size: 13px; color: var(--text-muted-service);" id="multiDownloadProgress">Mengumpulkan data...</p>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn-cancel-modal" onclick="closeMultiDownloadModal()">✕ Batal</button>
            <button class="btn-download-modal" id="btnMultiDownload" onclick="downloadMultiReport()" disabled>
                📥 Download Laporan
            </button>
        </div>
    </div>
</div>

<!-- ================= MODAL CREATE / EDIT ================= -->
<div class="modal-overlay" id="serviceModal" onclick="if(event.target === this) closeModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2>
                <span class="modal-icon" id="modalIcon">➕</span>
                <span id="modalTitle">Tambah Service</span>
            </h2>
            <button class="modal-close" id="modalCloseBtn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="serviceForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="service_id" id="serviceId" value="">

                <div class="form-group">
                    <label for="modal_name">Nama Service <span class="required">*</span></label>
                    <input type="text" name="name" id="modal_name" class="form-control" placeholder="Contoh: Website Utama, API Gateway" required>
                    <div class="helper-text">Nama yang mudah diingat untuk service ini</div>
                </div>

                <div class="form-group">
                    <label for="modal_target">Target URL / IP <span class="required">*</span></label>
                    <input type="text" name="target" id="modal_target" class="form-control" placeholder="Contoh: https://example.com atau 192.168.1.1" required>
                    <div class="helper-text">URL lengkap dengan protocol (http:// atau https://) atau alamat IP</div>
                </div>

                <div class="form-group">
                    <label for="modal_type">Tipe Monitoring <span class="required">*</span></label>
                    <select name="type" id="modal_type" class="form-control" required>
                        <option value="http">HTTP / HTTPS</option>
                        <option value="ping">PING</option>
                    </select>
                    <div class="helper-text">Jenis monitoring yang akan digunakan</div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel-modal" id="btnCancelModal" onclick="closeModal()">✕ Batal</button>
            <button class="btn-submit-modal" id="btnSubmitModal" onclick="submitForm()">💾 Simpan Service</button>
        </div>
    </div>
</div>

<script>
    // ================= SORTING TABLE =================
    let currentSort = 'name';
    let currentSortDirection = 'asc';

    function sortTable(column) {
        if (currentSort === column) {
            currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort = column;
            currentSortDirection = 'asc';
        }
        
        updateSortIndicators(column, currentSortDirection);
        
        const tbody = document.getElementById('tableBody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const dataRows = rows.filter(row => row.dataset.serviceId);
        
        if (dataRows.length === 0) {
            reloadWithSort();
            return;
        }
        
        const sortedRows = dataRows.sort((a, b) => {
            let aVal, bVal;
            let aNum, bNum;
            
            switch(column) {
                case 'no':
                    aNum = parseInt(a.querySelector('.service-no')?.textContent?.trim() || '0');
                    bNum = parseInt(b.querySelector('.service-no')?.textContent?.trim() || '0');
                    return currentSortDirection === 'asc' ? aNum - bNum : bNum - aNum;
                    
                case 'name':
                    aVal = a.querySelector('.service-name')?.textContent?.trim()?.toLowerCase() || '';
                    bVal = b.querySelector('.service-name')?.textContent?.trim()?.toLowerCase() || '';
                    return currentSortDirection === 'asc' 
                        ? aVal.localeCompare(bVal) 
                        : bVal.localeCompare(aVal);
                    
                case 'target':
                    aVal = a.querySelector('.service-target')?.textContent?.trim()?.toLowerCase() || '';
                    bVal = b.querySelector('.service-target')?.textContent?.trim()?.toLowerCase() || '';
                    return currentSortDirection === 'asc' 
                        ? aVal.localeCompare(bVal) 
                        : bVal.localeCompare(aVal);
                    
                case 'status':
                    const statusPriority = { 'UP': 1, 'WARNING': 2, 'DOWN': 3, 'UNKNOWN': 4, 'ARSIP': 5 };
                    const aStatus = a.querySelector('.status-badge')?.textContent?.trim()?.toUpperCase() || 'UNKNOWN';
                    const bStatus = b.querySelector('.status-badge')?.textContent?.trim()?.toUpperCase() || 'UNKNOWN';
                    aNum = statusPriority[aStatus] || 4;
                    bNum = statusPriority[bStatus] || 4;
                    return currentSortDirection === 'asc' ? aNum - bNum : bNum - aNum;
                    
                case 'uptime':
                    aNum = parseFloat(a.querySelector('.uptime-value')?.textContent?.replace('%', '')?.trim() || '0');
                    bNum = parseFloat(b.querySelector('.uptime-value')?.textContent?.replace('%', '')?.trim() || '0');
                    return currentSortDirection === 'asc' ? aNum - bNum : bNum - aNum;
                    
                case 'last_check':
                    aVal = a.querySelector('[id^="time-"]')?.textContent?.trim() || '';
                    bVal = b.querySelector('[id^="time-"]')?.textContent?.trim() || '';
                    return currentSortDirection === 'asc' 
                        ? aVal.localeCompare(bVal) 
                        : bVal.localeCompare(aVal);
                    
                default:
                    return 0;
            }
        });
        
        dataRows.forEach(row => row.remove());
        sortedRows.forEach(row => tbody.appendChild(row));
        updateRowNumbers();
    }

    function updateSortIndicators(column, direction) {
        const headers = document.querySelectorAll('.sortable-header');
        headers.forEach(th => {
            th.classList.remove('active-asc', 'active-desc');
            if (th.dataset.sort === column) {
                th.classList.add(direction === 'asc' ? 'active-asc' : 'active-desc');
            }
        });
    }

    function updateRowNumbers() {
        const rows = document.querySelectorAll('#tableBody tr[data-service-id]');
        rows.forEach((row, index) => {
            const noSpan = row.querySelector('.service-no');
            if (noSpan) {
                noSpan.textContent = index + 1;
            }
        });
    }

    function reloadWithSort() {
        let url = new URL(window.location.href);
        url.searchParams.set('sort', currentSort);
        url.searchParams.set('direction', currentSortDirection);
        url.searchParams.set('_', Date.now());
        window.location.href = url.toString();
    }

    // ================= TOGGLE ARCHIVE =================
    function toggleArchive() {
        let url = new URL(window.location.href);
        let showArchived = url.searchParams.get('show_archived');
        
        if (showArchived === '1') {
            url.searchParams.delete('show_archived');
        } else {
            url.searchParams.set('show_archived', '1');
        }
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    // ================= CONFIRM MODAL =================
    let confirmCallback = null;
    let confirmData = null;

    function showConfirmModal(icon, title, message, detail, btnText, btnClass, callback) {
        document.getElementById('confirmIcon').className = 'modal-icon ' + icon;
        document.getElementById('confirmIcon').textContent = icon === 'danger' ? '🗑️' : 
                                                           icon === 'archive' ? '📦' : 
                                                           icon === 'restore' ? '🔄' : '⚠️';
        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmMessage').textContent = message;
        document.getElementById('confirmDetail').innerHTML = detail;
        document.getElementById('confirmBtn').textContent = btnText;
        document.getElementById('confirmBtn').className = 'btn-modal ' + btnClass;
        document.getElementById('customConfirmModal').classList.add('active');
        document.body.style.overflow = 'hidden';
        confirmCallback = callback;
    }

    function closeConfirmModal() {
        document.getElementById('customConfirmModal').classList.remove('active');
        document.body.style.overflow = '';
        confirmCallback = null;
        confirmData = null;
    }

    function executeConfirm() {
        if (confirmCallback) {
            confirmCallback();
        }
        closeConfirmModal();
    }

    // ================= CONFIRM DELETE =================
    function confirmDelete(id, name) {
        showConfirmModal(
            'danger',
            'Hapus Service',
            'Apakah Anda yakin ingin menghapus service ini?',
            'Service <span class="highlight-name">' + name + '</span> akan dihapus secara permanen.',
            '🗑️ Ya, Hapus',
            'btn-confirm',
            function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/services/' + id;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        );
    }

    // ================= CONFIRM ARCHIVE =================
    function confirmArchive(id, name) {
        showConfirmModal(
            'archive',
            'Arsip Service',
            'Apakah Anda yakin ingin mengarsipkan service ini?',
            'Service <span class="highlight-name">' + name + '</span> akan diarsipkan.<br><br>' +
            '<div class="info-text">' +
            '📌 Service yang diarsipkan akan:<br>' +
            '❌ Tidak tampil di daftar service utama<br>' +
            '❌ Tidak dipantau (check service dihentikan)<br>' +
            '❌ Tidak terhitung di statistik (UP/DOWN)<br><br>' +
            '✅ Data log tetap tersimpan<br>' +
            '✅ Bisa dipulihkan kapan saja' +
            '</div>',
            '📦 Ya, Arsip',
            'btn-confirm-archive',
            function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/services/' + id + '/archive';
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        );
    }

    // ================= CONFIRM RESTORE =================
    function confirmRestore(id, name) {
        showConfirmModal(
            'restore',
            'Pulihkan Service',
            'Apakah Anda yakin ingin memulihkan service ini?',
            'Service <span class="highlight-name">' + name + '</span> akan dipulihkan.<br><br>' +
            '<div class="info-text">' +
            '✅ Service akan tampil kembali di daftar service utama<br>' +
            '✅ Akan dipantau kembali (check service aktif)<br>' +
            '✅ Terhitung di statistik (UP/DOWN)<br><br>' +
            '📌 Semua data log tetap tersimpan' +
            '</div>',
            '🔄 Ya, Pulihkan',
            'btn-confirm-restore',
            function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/services/' + id + '/restore';
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        );
    }

    // ================= CONFIRM DELETE PERMANENT =================
    function confirmDeletePermanent(id, name) {
        showConfirmModal(
            'danger',
            'Hapus Permanen',
            'Apakah Anda yakin ingin menghapus permanen service ini?',
            'Service <span class="highlight-name">' + name + '</span> akan dihapus <strong>permanen</strong>.<br><br>' +
            '<div class="info-text" style="color: #dc2626;">' +
            '⚠️ PERINGATAN: TINDAKAN INI TIDAK DAPAT DIURUNGKAN!<br><br>' +
            '📌 Yang akan dihapus:<br>' +
            '❌ Data service akan hilang selamanya<br>' +
            '❌ Semua log / history akan hilang<br>' +
            '❌ Tidak bisa dipulihkan kembali' +
            '</div>',
            '🗑️ Hapus Permanen',
            'btn-confirm',
            function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/services/' + id + '/permanent';
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        );
    }

    // ================= WA INTERVAL =================
    function changeWaInterval(value) {
        let url = new URL(window.location.href);
        url.searchParams.set('wa_interval', value);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    // ================= AUTO REFRESH TIMER =================
    (function() {
        'use strict';
        
        const REFRESH_INTERVAL = 30;
        let seconds = REFRESH_INTERVAL;
        let countdownElement = document.getElementById('countdownTimer');
        let refreshTimer = null;
        let isReloading = false;
        
        function updateCountdown() {
            if (isNaN(seconds) || seconds < 0) {
                seconds = REFRESH_INTERVAL;
            }
            
            seconds--;
            
            if (seconds < 0) {
                seconds = REFRESH_INTERVAL;
            }
            
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            
            if (countdownElement) {
                const timeString = `${mins}:${secs.toString().padStart(2, '0')}`;
                countdownElement.textContent = timeString;
                countdownElement.className = 'countdown';
                
                if (seconds < 10) {
                    countdownElement.classList.add('danger');
                } else if (seconds < 30) {
                    countdownElement.classList.add('warning');
                }
            }
            
            if (seconds <= 0 && !isReloading) {
                isReloading = true;
                window.location.href = window.location.pathname + '?_=' + Date.now();
            }
        }
        
        function startCountdown() {
            if (refreshTimer) {
                clearInterval(refreshTimer);
                refreshTimer = null;
            }
            
            seconds = REFRESH_INTERVAL;
            isReloading = false;
            updateCountdown();
            refreshTimer = setInterval(updateCountdown, 1000);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            startCountdown();
        });
        
        document.addEventListener('modalOpened', function() {
            if (refreshTimer) {
                clearInterval(refreshTimer);
                refreshTimer = null;
            }
        });
        
        document.addEventListener('modalClosed', function() {
            if (!refreshTimer) {
                startCountdown();
            }
        });
    })();

    // ================= AJAX POLLING =================
    (function() {
        'use strict';
        
        let pollingInterval = null;
        let isPolling = false;

        function fetchLatestStatus() {
            if (isPolling) return;
            isPolling = true;
            
            fetch('/api/services/status?_=' + Date.now(), {
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.services.forEach(service => {
                        const badge = document.getElementById('status-' + service.id);
                        if (badge) {
                            const status = service.last_status || 'UNKNOWN';
                            const statusLower = status.toLowerCase();
                            badge.className = `status-badge ${statusLower}`;
                            badge.innerHTML = `<span class="status-dot"></span> ${status}`;
                        }
                        
                        const timeEl = document.getElementById('time-' + service.id);
                        if (timeEl && service.last_check_at) {
                            timeEl.textContent = service.last_check_at;
                        }
                    });
                }
            })
            .catch(error => {
                console.log('Status poll error:', error);
            })
            .finally(() => {
                isPolling = false;
            });
        }

        function startPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
            pollingInterval = setInterval(fetchLatestStatus, 5000);
            setTimeout(fetchLatestStatus, 2000);
        }

        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            startPolling();
        });

        document.addEventListener('modalOpened', function() {
            stopPolling();
        });

        document.addEventListener('modalClosed', function() {
            startPolling();
        });
    })();

    // ================= SEARCH STATUS FUNCTIONS =================
    function showSearchStatus(text, showSpinner = true) {
        const status = document.getElementById('searchStatus');
        const textEl = document.getElementById('searchStatusText');
        const spinner = status.querySelector('.status-spinner');
        
        textEl.textContent = text;
        spinner.style.display = showSpinner ? 'block' : 'none';
        status.classList.add('active');
    }

    function hideSearchStatus() {
        const status = document.getElementById('searchStatus');
        status.classList.remove('active');
    }

    function cancelSearch() {
        clearTimeout(searchTimeout);
        hideSearchStatus();
        document.getElementById('searchService').value = '';
        resetSearch();
        showToast('info', 'Info', 'Pencarian dibatalkan');
    }

    // ================= VARIABEL GLOBAL =================
    let currentDetailId = null;
    let currentDownloadId = null;
    let selectedPeriod = 7;
    let searchTimeout = null;
    let isSearching = false;
    let downloadServiceData = null;
    let multiFormat = 'pdf';

    // ================= 🔥 MULTI DOWNLOAD FUNCTIONS =================
    let selectedServices = new Set();

    function toggleCheckbox(element) {
        const checkbox = element.querySelector('.multi-service-checkbox');
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            updateMultiSelection(event);
        }
    }

    function openMultiDownloadModal() {
        const modal = document.getElementById('multiDownloadModal');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        document.dispatchEvent(new Event('modalOpened'));
        
        // Reset semua checkbox
        document.querySelectorAll('.multi-service-checkbox').forEach(cb => {
            cb.checked = false;
            cb.closest('.service-list-item').classList.remove('selected');
        });
        updateMultiSelection();
        
        // Reset button
        document.getElementById('btnMultiDownload').disabled = true;
        document.getElementById('btnMultiDownload').textContent = '📥 Download Laporan';
        document.getElementById('multiDownloadLoading').style.display = 'none';
        
        // Reset format ke PDF
        document.getElementById('formatPdfLabel').classList.add('active-format');
        document.getElementById('formatExcelLabel').classList.remove('active-format');
        multiFormat = 'pdf';
        
        // Auto select active services
        setTimeout(() => {
            selectByStatusMulti('UP');
        }, 300);
    }

    function closeMultiDownloadModal() {
        document.getElementById('multiDownloadModal').classList.remove('active');
        document.body.style.overflow = '';
        document.dispatchEvent(new Event('modalClosed'));
    }

    function selectAllMulti(select) {
        document.querySelectorAll('.multi-service-checkbox').forEach(cb => {
            cb.checked = select;
            cb.closest('.service-list-item').classList.toggle('selected', select);
        });
        updateMultiSelection();
    }

    function selectByStatusMulti(status) {
        document.querySelectorAll('.multi-service-checkbox').forEach(cb => {
            const item = cb.closest('.service-list-item');
            const statusBadge = item?.querySelector('.status-badge');
            if (statusBadge) {
                const text = statusBadge.textContent.trim().toUpperCase();
                cb.checked = (text === status);
                item.classList.toggle('selected', cb.checked);
            }
        });
        updateMultiSelection();
    }

    function updateMultiSelection(event) {
        // Jika event ada, update class selected pada parent
        if (event && event.target) {
            const item = event.target.closest('.service-list-item');
            if (item) {
                const checkbox = item.querySelector('.multi-service-checkbox');
                if (checkbox) {
                    item.classList.toggle('selected', checkbox.checked);
                }
            }
        }

        // Hitung semua checkbox yang tercentang
        const checked = document.querySelectorAll('.multi-service-checkbox:checked');
        const count = checked.length;
        document.getElementById('multiSelectedCount').textContent = count;
        
        const btn = document.getElementById('btnMultiDownload');
        if (count === 0) {
            btn.disabled = true;
            btn.title = '❌ Pilih minimal 1 service';
        } else {
            btn.disabled = false;
            btn.title = '📥 Download laporan untuk ' + count + ' service';
        }
    }

    function updateMultiFormat(element) {
        multiFormat = element.value;
        const labels = document.querySelectorAll('.format-group label');
        labels.forEach(label => label.classList.remove('active-format'));
        if (multiFormat === 'pdf') {
            document.getElementById('formatPdfLabel').classList.add('active-format');
        } else {
            document.getElementById('formatExcelLabel').classList.add('active-format');
        }
    }

    function downloadMultiReport() {
        const checked = document.querySelectorAll('.multi-service-checkbox:checked');
        if (checked.length === 0) {
            showToast('warning', 'Peringatan!', 'Pilih minimal 1 service');
            return;
        }
        
        // Kumpulkan data per service
        const servicesData = [];
        checked.forEach(cb => {
            const item = cb.closest('.service-list-item');
            const periodSelect = item?.querySelector('.period-select-multi');
            
            servicesData.push({
                id: parseInt(cb.dataset.id),
                name: cb.dataset.name,
                period: periodSelect ? periodSelect.value : 'all',
                age: parseInt(cb.dataset.age),
                available: parseInt(cb.dataset.available)
            });
        });

        // 🔥 TAMPILKAN MODAL KONFIRMASI CUSTOM
        const names = servicesData.map(s => s.name).join(', ');
        const formatLabel = multiFormat === 'pdf' ? 'PDF' : 'Excel (CSV)';
        
        let detailMessage = '<strong>' + servicesData.length + ' service</strong> akan di-download:\n\n';
        servicesData.forEach((s, i) => {
            const periodLabel = s.period === 'all' ? '📚 Semua Data (' + s.available + ' hari)' : s.period + ' Hari Terakhir';
            detailMessage += (i+1) + '. ' + s.name + ' → ' + periodLabel + '\n';
        });
        detailMessage += '\n📄 Format: ' + formatLabel + ' (1 file)';

        // 🔥 GANTI CONFIRM BAWAAN BROWSER DENGAN CUSTOM MODAL
        showConfirmModal(
            'info',
            '📥 Download Laporan Service',
            'Yakin ingin mendownload laporan untuk ' + servicesData.length + ' service?',
            detailMessage.replace(/\n/g, '<br>'),
            '📥 Download',
            'btn-confirm',
            function() {
                // Proses download
                const btn = document.getElementById('btnMultiDownload');
                const loading = document.getElementById('multiDownloadLoading');
                const progress = document.getElementById('multiDownloadProgress');
                
                btn.disabled = true;
                btn.textContent = '⏳ Memproses...';
                loading.style.display = 'block';
                progress.textContent = 'Memproses ' + servicesData.length + ' service...';
                
                // Kirim request ke server
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('services', JSON.stringify(servicesData));
                formData.append('format', multiFormat);
                
                fetch('{{ route("services.download-multi-report") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Gagal download');
                        });
                    }
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    const ext = multiFormat === 'pdf' ? 'pdf' : 'csv';
                    a.download = 'laporan_service_' + new Date().toISOString().slice(0,10) + '.' + ext;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                    
                    showToast('success', 'Berhasil!', '📄 Laporan berhasil diunduh');
                    closeMultiDownloadModal();
                })
                .catch(error => {
                    showToast('error', 'Gagal!', 'Terjadi kesalahan: ' + error.message);
                    btn.disabled = false;
                    btn.textContent = '📥 Download Laporan';
                    loading.style.display = 'none';
                });
            }
        );
    }

    // ================= SEARCH SERVICES =================
    function searchServices() {
        const query = document.getElementById('searchService').value.trim();
        
        if (query.length === 0) {
            hideSearchStatus();
            showToast('warning', 'Peringatan!', 'Masukkan kata kunci pencarian');
            return;
        }
        
        if (isSearching) {
            showToast('info', 'Info', 'Pencarian sedang berlangsung...');
            return;
        }
        
        isSearching = true;
        const btnSearch = document.getElementById('btnSearch');
        
        showSearchStatus('🔍 Sedang mencari "' + query + '"...');
        btnSearch.disabled = true;
        btnSearch.textContent = '⏳';
        
        fetch(`/services/search?q=${encodeURIComponent(query)}&_=${Date.now()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Cache-Control': 'no-cache'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            isSearching = false;
            btnSearch.disabled = false;
            btnSearch.textContent = '🔍 Cari';
            
            if (data.success) {
                renderSearchResult(data.data, data.pagination, query);
                hideSearchStatus();
                showToast('success', 'Berhasil!', `Ditemukan ${data.data.length} data untuk "${query}"`);
            } else {
                hideSearchStatus();
                showToast('error', 'Gagal!', data.message || 'Gagal mencari data');
                window.location.reload();
            }
        })
        .catch(error => {
            isSearching = false;
            btnSearch.disabled = false;
            btnSearch.textContent = '🔍 Cari';
            hideSearchStatus();
            showToast('error', 'Error!', 'Terjadi kesalahan: ' + error.message);
            window.location.reload();
        });
    }

    function resetSearch() {
        document.getElementById('searchService').value = '';
        hideSearchStatus();
        window.location.href = window.location.pathname + '?_=' + Date.now();
    }

    function renderSearchResult(services, pagination, query) {
        const tbody = document.getElementById('tableBody');
        const info = document.getElementById('tableInfo');
        const paginationWrapper = document.getElementById('paginationWrapper');
        
        if (!tbody) return;
        
        if (services.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <span class="empty-icon">🔍</span>
                            <h3>Service Tidak Ditemukan</h3>
                            <p>Tidak ada service yang sesuai dengan "<strong>${query}</strong>"</p>
                            <button onclick="resetSearch()" class="btn-empty-primary">↺ Reset Pencarian</button>
                        </div>
                    </td>
                </tr>
            `;
            if (info) info.innerHTML = 'Menampilkan <strong>0</strong> - <strong>0</strong> dari <strong>0</strong> service';
            if (paginationWrapper) paginationWrapper.style.display = 'none';
            return;
        }
        
        let html = '';
        const colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5', 'color-6', 'color-7', 'color-8'];
        
        services.forEach((service, index) => {
            const colorClass = colors[index % colors.length];
            const initials = service.name.substring(0, 2).toUpperCase();
            const statusLabel = service.last_status || 'UNKNOWN';
            const uptime = service.uptime || 0;
            const uptimeColor = uptime >= 70 ? 'green' : (uptime >= 50 ? 'yellow' : 'red');
            const no = index + 1;
            const isArchived = service.is_archived || 0;
            
            let displayName = service.name;
            let displayTarget = service.target;
            
            if (query) {
                const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                displayName = service.name.replace(regex, '<mark>$1</mark>');
                displayTarget = service.target.replace(regex, '<mark>$1</mark>');
            }
            
            html += `
                <tr data-service-id="${service.id}" data-archived="${isArchived}">
                    <td><span class="service-no">${no}</span></td>
                    <td>
                        <div class="service-info">
                            <div class="service-avatar ${colorClass}">${initials}</div>
                            <div>
                                <div class="service-name">${displayName}</div>
                                <span class="service-type">${(service.type || 'HTTP').toUpperCase()}</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="service-target">${displayTarget}</span></td>
                    <td>
                        ${isArchived ? `<span class="status-badge archived" id="status-${service.id}"><span class="status-dot"></span> 📦 ARSIP</span>` :
                        statusLabel == 'UP' ? `<span class="status-badge up" id="status-${service.id}"><span class="status-dot"></span> UP</span>` : 
                        statusLabel == 'DOWN' ? `<span class="status-badge down" id="status-${service.id}"><span class="status-dot"></span> DOWN</span>` :
                        statusLabel == 'WARNING' ? `<span class="status-badge warning" id="status-${service.id}"><span class="status-dot"></span> WARNING</span>` :
                        `<span class="status-badge unknown" id="status-${service.id}"><span class="status-dot"></span> UNKNOWN</span>`}
                    </td>
                    <td>
                        ${isArchived ? 
                            `<div class="uptime-value" style="color: var(--text-muted-service);">-</div>
                             <div class="uptime-bar"><div class="uptime-fill" style="width: 0%; background: var(--text-muted-service);"></div></div>` :
                            `<div class="uptime-value ${uptimeColor}">${Number(uptime).toFixed(2)}%</div>
                             <div class="uptime-bar"><div class="uptime-fill ${uptimeColor}" style="width: ${uptime}%;"></div></div>`
                        }
                    </td>
                    <td>
                        <div style="font-size: 13px; color: var(--text-secondary-service); font-family: 'Courier New', monospace; font-weight: 600;" id="time-${service.id}">
                            ${service.last_check_at || '-'}
                        </div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="openDetailModal(${service.id})" class="btn-action btn-detail" title="Detail">👁️</button>
                            <button onclick="openDownloadModal(${service.id}, '${service.name.replace(/'/g, "\\'")}')" class="btn-action btn-download" title="Download PDF">📥</button>
                            ${isArchived ? 
                                `<button onclick="confirmRestore(${service.id}, '${service.name.replace(/'/g, "\\'")}')" class="btn-restore" title="Pulihkan">🔄 Pulihkan</button>
                                 <button onclick="confirmDeletePermanent(${service.id}, '${service.name.replace(/'/g, "\\'")}')" class="btn-delete-permanent" title="Hapus Permanen">🗑️ Hapus</button>` :
                                `<button onclick="confirmArchive(${service.id}, '${service.name.replace(/'/g, "\\'")}')" class="btn-archive" title="Arsipkan">📦 Arsip</button>
                                 <button onclick="openEditModal(${service.id})" class="btn-action btn-edit" title="Edit">✏️</button>
                                 <button onclick="confirmDelete(${service.id}, '${service.name.replace(/'/g, "\\'")}')" class="btn-action btn-delete" title="Hapus">🗑️</button>
                                 <button onclick="checkService(${service.id})" class="btn-check" title="Check Now" id="checkBtn${service.id}">🔄</button>`
                            }
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
        
        if (info && pagination) {
            info.innerHTML = `Menampilkan <strong>${pagination.from || 0}</strong> - <strong>${pagination.to || 0}</strong> dari <strong>${pagination.total || 0}</strong> service`;
        }
        
        if (paginationWrapper) {
            paginationWrapper.style.display = 'flex';
            paginationWrapper.innerHTML = `
                <div class="pagination-info">
                    Menampilkan <strong>${pagination.from || 0}</strong> - <strong>${pagination.to || 0}</strong> dari <strong>${pagination.total || 0}</strong> data
                </div>
                <div class="pagination-links">
                    ${pagination.prev_page_url ? `<a href="#" onclick="loadPage('${pagination.prev_page_url}')" class="page-link">‹</a>` : `<span class="page-link disabled">‹</span>`}
                    <span class="page-link active">${pagination.current_page || 1}</span>
                    ${pagination.next_page_url ? `<a href="#" onclick="loadPage('${pagination.next_page_url}')" class="page-link">›</a>` : `<span class="page-link disabled">›</span>`}
                </div>
            `;
        }
    }

    function loadPage(url) {
        showToast('info', 'Info', 'Fitur pagination pada hasil pencarian akan segera hadir');
    }

    // ================= EVENT LISTENER =================
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            showToast('success', 'Berhasil!', '{{ session('success') }}');
        @endif
        @if(session('error'))
            showToast('error', 'Gagal!', '{{ session('error') }}');
        @endif
        @if(session('warning'))
            showToast('warning', 'Peringatan!', '{{ session('warning') }}');
        @endif
        @if(session('info'))
            showToast('info', 'Info', '{{ session('info') }}');
        @endif

        const typeSelect = document.getElementById('modal_type');
        if (typeSelect) {
            typeSelect.addEventListener('change', function() {
                updateHelperText(this.value);
            });
            updateHelperText(typeSelect.value);
        }

        const searchInput = document.getElementById('searchService');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchServices();
                }
            });
            
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length === 0) {
                    hideSearchStatus();
                    resetSearch();
                    return;
                }
                
                if (query.length >= 2) {
                    showSearchStatus('✍️ Mengetik...', false);
                    searchTimeout = setTimeout(function() {
                        searchServices();
                    }, 800);
                } else {
                    hideSearchStatus();
                }
            });
        }

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.getElementById('searchService').focus();
                document.getElementById('searchService').select();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('searchService').focus();
                document.getElementById('searchService').select();
            }
            if (e.key === 'Escape') {
                closeModal();
                closeDetailModal();
                closeDownloadModal();
                closeMultiDownloadModal();
                closeConfirmModal();
                hideSearchStatus();
            }
        });
    });

    // ================= TOAST =================
    function showToast(type, title, message) {
        const container = document.getElementById('toastContainer');
        const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };

        const existingToasts = container.querySelectorAll('.toast');
        for (let toast of existingToasts) {
            const msgEl = toast.querySelector('.toast-message');
            if (msgEl && msgEl.textContent === message) {
                return;
            }
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || 'ℹ️'}</span>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.closest('.toast').remove()">&times;</button>
        `;

        container.appendChild(toast);
        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 400);
            }
        }, 5000);
    }

    // ================= HELPER TEXT =================
    function updateHelperText(type) {
        const targetInput = document.getElementById('modal_target');
        const helperText = targetInput?.parentElement?.querySelector('.helper-text');
        
        if (!targetInput || !helperText) return;
        
        if (type === 'ping') {
            targetInput.placeholder = 'Contoh: 192.168.1.1 atau 8.8.8.8';
            helperText.textContent = 'Masukkan alamat IP (contoh: 192.168.1.1)';
        } else {
            targetInput.placeholder = 'Contoh: https://example.com atau http://localhost';
            helperText.textContent = 'URL lengkap dengan protocol (http:// atau https://)';
        }
    }

    // ================= CHECK SERVICE =================
    function checkService(id) {
        const btn = document.getElementById('checkBtn' + id);
        
        if (!navigator.onLine) {
            showToast('error', 'Jaringan Terputus!', 'Tidak ada koneksi internet. Periksa router/modem Anda.');
            return;
        }
        
        if (btn) {
            btn.disabled = true;
            btn.textContent = '⏳';
        }
        
        showToast('info', 'Memproses...', '🔄 Sedang mengecek service...');
        
        fetch(`/services/${id}/check?_=${Date.now()}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Cache-Control': 'no-cache'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', '✅ Berhasil!', data.message || 'Service berhasil di-check');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('error', '❌ Gagal!', data.message || 'Gagal check service');
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = '🔄';
                }
            }
        })
        .catch(error => {
            showToast('error', '❌ Error!', 'Terjadi kesalahan: ' + error.message);
            if (btn) {
                btn.disabled = false;
                btn.textContent = '🔄';
            }
        });
    }

    // ================= PERPAGE =================
    function changePerPage(value) {
        let url = new URL(window.location.href);
        url.searchParams.set('perPage', value);
        url.searchParams.set('page', '1');
        window.location.href = url.toString() + '&_=' + Date.now();
    }

    // ================= DETAIL MODAL =================
    function openDetailModal(id) {
        currentDetailId = id;
        const modal = document.getElementById('detailModal');
        const body = document.getElementById('detailModalBody');
        body.innerHTML = `<div style="text-align: center; padding: 40px 0; color: var(--text-secondary-service);"><span style="font-size: 32px; display: block; margin-bottom: 8px;">⏳</span><p>Memuat data...</p></div>`;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        document.dispatchEvent(new Event('modalOpened'));
        fetchDetailData(id);
    }

    function fetchDetailData(id) {
        fetch(`/services/${id}/detail?_=${Date.now()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Cache-Control': 'no-cache'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderDetail(data.data);
            } else {
                document.getElementById('detailModalBody').innerHTML = `
                    <div style="text-align: center; padding: 40px 0; color: #dc2626;">
                        <span style="font-size: 32px; display: block; margin-bottom: 8px;">❌</span>
                        <p>Gagal memuat data service</p>
                        <p style="font-size: 13px; color: var(--text-secondary-service);">${data.message || 'Terjadi kesalahan'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('detailModalBody').innerHTML = `
                <div style="text-align: center; padding: 40px 0; color: #dc2626;">
                    <span style="font-size: 32px; display: block; margin-bottom: 8px;">❌</span>
                    <p>Gagal memuat data</p>
                    <p style="font-size: 13px; color: var(--text-secondary-service);">${error.message}</p>
                </div>
            `;
        });
    }

    function renderDetail(service) {
        const body = document.getElementById('detailModalBody');
        document.getElementById('detailModalTitle').textContent = `📊 Detail Service: ${service.name}`;

        const statusClass = service.last_status?.toLowerCase() || 'unknown';
        const statusBadge = service.last_status || 'UNKNOWN';
        
        const responseCode = service.last_code ?? '-';
        const responseTime = service.last_response_time ?? 0;
        
        const timeClass = responseTime < 1 ? 'fast' : (responseTime < 3 ? 'medium' : 'slow');
        const codeClass = responseCode < 400 ? 'success' : (responseCode < 500 ? 'warning' : 'error');
        
        const message = service.last_message || '-';
        const isEmptyPage = message.includes('konten kosong') || message.includes('EMPTY_RESPONSE');
        const messageClass = isEmptyPage ? 'empty-message' : '';

        body.innerHTML = `
            <div class="detail-grid">
                <div class="detail-item"><div class="detail-label">Nama Service</div><div class="detail-value">${service.name}</div></div>
                <div class="detail-item"><div class="detail-label">Tipe</div><div class="detail-value">${service.type?.toUpperCase() || '-'}</div></div>
                <div class="detail-item full-width"><div class="detail-label">Target URL / IP</div><div class="detail-value" style="font-family: 'SF Mono', 'Courier New', monospace; font-size: 14px; word-break: break-all;">${service.target}</div></div>
                <div class="detail-item"><div class="detail-label">Status</div><div class="detail-value"><span class="status-badge ${statusClass}"><span class="status-dot"></span> ${statusBadge}</span></div></div>
                <div class="detail-item"><div class="detail-label">Response Code</div><div class="detail-value"><span class="response-code ${codeClass}">${responseCode}</span></div></div>
                <div class="detail-item"><div class="detail-label">Response Time</div><div class="detail-value"><span class="response-time ${timeClass}">${Number(responseTime).toFixed(2)} <span style="font-size: 12px; color: var(--text-muted-service);">s</span></span></div></div>
                <div class="detail-item full-width"><div class="detail-label">Pesan</div><div class="detail-message ${messageClass}">${message}</div></div>
                <div class="detail-item full-width" style="background: var(--bg-detail-alt-service); border-color: var(--border-service);"><div class="detail-label">Informasi Tambahan</div><div style="display: flex; gap: 16px; flex-wrap: wrap; margin-top: 4px; font-size: 13px; color: var(--text-secondary-service);"><span><strong>ID:</strong> ${service.id}</span><span><strong>Terakhir Check:</strong> ${service.last_check_at || '-'}</span><span><strong>Dibuat:</strong> ${service.created_at || '-'}</span><span><strong>Diupdate:</strong> ${service.updated_at || '-'}</span></div></div>
            </div>
        `;
    }

    function refreshDetail() {
        if (currentDetailId) {
            const btn = document.getElementById('refreshDetailBtn');
            btn.innerHTML = `<span class="spin">🔄</span> Memuat...`;
            btn.disabled = true;
            
            fetchDetailData(currentDetailId);
            
            setTimeout(() => {
                btn.innerHTML = '🔄 Refresh';
                btn.disabled = false;
            }, 1000);
        }
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.remove('active');
        document.body.style.overflow = '';
        currentDetailId = null;
        document.dispatchEvent(new Event('modalClosed'));
    }

    // ================= FORMAT TANGGAL =================
    function formatDateLocal(date) {
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    function parseDateLocal(dateStr) {
        var parts = dateStr.split('-');
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    // ================= DOWNLOAD MODAL =================
    function openDownloadModal(id, name) {
        currentDownloadId = id;
        var modal = document.getElementById('downloadModal');
        document.getElementById('downloadModalTitle').textContent = '📥 Download Laporan PDF';
        document.getElementById('downloadServiceName').textContent = name;
        document.getElementById('downloadServiceTarget').textContent = '🎯 Memuat...';
        document.getElementById('downloadServiceType').textContent = '📌 Memuat...';
        
        var ageInfo = document.getElementById('serviceAgeInfo');
        var ageText = document.getElementById('serviceAgeText');
        ageInfo.style.display = 'block';
        ageText.innerHTML = '⏳ Memuat informasi service...';
        
        var notice = document.getElementById('downloadNotice');
        notice.innerHTML = '⏳ Memuat informasi service...';
        
        fetch('/services/' + id + '/detail?_=' + Date.now(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Cache-Control': 'no-cache'
            }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('downloadServiceTarget').textContent = '🎯 ' + data.data.target;
                document.getElementById('downloadServiceType').textContent = '📌 ' + (data.data.type?.toUpperCase() || 'HTTP');
                
                var createdAt = new Date(data.data.created_at);
                var today = new Date();

                function getDateDiffInDays(date1, date2) {
                    var d1 = new Date(date1);
                    d1.setHours(0, 0, 0, 0);
                    var d2 = new Date(date2);
                    d2.setHours(0, 0, 0, 0);
                    var diffTime = d2.getTime() - d1.getTime();
                    return Math.floor(diffTime / (1000 * 60 * 60 * 24));
                }

                var ageInDays = getDateDiffInDays(createdAt, today);
                var availableDays = ageInDays + 1;
                var maxDays = Math.min(availableDays, 90);
                
                var ageTextDisplay = '';
                if (ageInDays < 1) {
                    ageTextDisplay = '🆕 Baru ditambahkan <strong>hari ini</strong>';
                } else if (ageInDays === 1) {
                    ageTextDisplay = '📆 Berusia <strong>1 hari</strong> (dibuat kemarin)';
                } else {
                    ageTextDisplay = '📆 Berusia <strong>' + ageInDays + ' hari</strong>';
                }
                
                var ageInfo = document.getElementById('serviceAgeInfo');
                var ageTextEl = document.getElementById('serviceAgeText');
                ageTextEl.innerHTML = '📌 <strong>Info Service:</strong> ' + ageTextDisplay + 
                    ' | Dibuat: <strong>' + data.data.created_at + '</strong>' +
                    ' | Data tersedia: <strong>' + availableDays + ' hari</strong>';
                ageInfo.style.display = 'block';
                
                var dateFromInput = document.getElementById('dateFrom');
                var dateToInput = document.getElementById('dateTo');
                
                var minDate = formatDateLocal(createdAt);
                var maxDate = formatDateLocal(today);
                
                dateFromInput.setAttribute('min', minDate);
                dateFromInput.setAttribute('max', maxDate);
                dateToInput.setAttribute('min', minDate);
                dateToInput.setAttribute('max', maxDate);
                
                var defaultDays = Math.min(7, Math.max(1, maxDays));
                var periodStart = new Date(today);
                periodStart.setDate(periodStart.getDate() - defaultDays);
                
                periodStart.setHours(0, 0, 0, 0);
                var createdAtDate = new Date(createdAt);
                createdAtDate.setHours(0, 0, 0, 0);
                
                if (periodStart < createdAtDate) {
                    periodStart = new Date(createdAtDate);
                }
                
                dateFromInput.value = formatDateLocal(periodStart);
                dateToInput.value = maxDate;
                
                var periodBtns = document.querySelectorAll('.period-btn');
                var availablePeriods = [];
                
                periodBtns.forEach(function(btn) {
                    var days = parseInt(btn.getAttribute('data-period'));
                    var isAvailable = days <= availableDays;
                    
                    if (isAvailable) {
                        availablePeriods.push(days);
                        btn.style.opacity = '1';
                        btn.style.cursor = 'pointer';
                        btn.style.pointerEvents = 'auto';
                        btn.classList.remove('disabled');
                        btn.title = days + ' hari terakhir (✅ tersedia)';
                    } else {
                        btn.style.opacity = '0.4';
                        btn.style.cursor = 'not-allowed';
                        btn.style.pointerEvents = 'none';
                        btn.classList.add('disabled');
                        btn.title = '❌ Periode ' + days + ' hari belum tersedia (hanya ' + availableDays + ' hari data tersedia)';
                    }
                    
                    if (days === defaultDays) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
                
                var availableText = availablePeriods.length > 0 ? availablePeriods.join(', ') + ' hari' : 'Belum ada periode yang tersedia';
                notice.innerHTML = '📌 <strong>Info:</strong> Service ini memiliki data dari <strong>' + 
                    minDate + '</strong> sampai <strong>' + maxDate + '</strong>.<br>' +
                    '📊 Total <strong>' + availableDays + ' hari</strong> data tersedia.<br>' +
                    '✅ Periode tersedia: <strong>' + availableText + '</strong>';
                
                downloadServiceData = {
                    createdAt: createdAt,
                    ageInDays: ageInDays,
                    availableDays: availableDays,
                    maxDays: maxDays,
                    minDate: minDate,
                    maxDate: maxDate,
                    serviceName: name,
                    minDateObj: createdAtDate,
                    maxDateObj: today
                };
                
                setTimeout(validateDateRange, 100);
            }
        })
        .catch(function(error) {
            document.getElementById('downloadServiceTarget').textContent = '🎯 -';
            document.getElementById('downloadServiceType').textContent = '📌 -';
            notice.innerHTML = '❌ Gagal memuat informasi service: ' + error.message;
            var ageInfo = document.getElementById('serviceAgeInfo');
            var ageTextEl = document.getElementById('serviceAgeText');
            ageTextEl.innerHTML = '❌ Gagal memuat informasi service';
        });
        
        document.getElementById('downloadLoading').style.display = 'none';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        document.dispatchEvent(new Event('modalOpened'));
    }

    // ================= VALIDASI TANGGAL =================
    function validateDateRange() {
        var dateFrom = document.getElementById('dateFrom');
        var dateTo = document.getElementById('dateTo');
        var btnDownload = document.getElementById('btnDownloadNow');
        
        if (!downloadServiceData || !dateFrom.value || !dateTo.value) {
            if (btnDownload) {
                btnDownload.disabled = true;
                btnDownload.title = '❌ Isi tanggal terlebih dahulu';
            }
            return;
        }
        
        var fromDateStr = dateFrom.value;
        var toDateStr = dateTo.value;
        var minDateStr = downloadServiceData.minDate;
        var maxDateStr = downloadServiceData.maxDate;
        
        var hasError = false;
        var errorMessages = [];
        
        if (fromDateStr < minDateStr) {
            dateFrom.style.borderColor = '#dc2626';
            dateFrom.style.background = '#fef2f2';
            showDateError(dateFrom, '⚠️ Minimal tanggal: ' + minDateStr + ' (tanggal service dibuat)');
            hasError = true;
            errorMessages.push('Tanggal awal tidak boleh sebelum ' + minDateStr);
        } else if (fromDateStr > maxDateStr) {
            dateFrom.style.borderColor = '#dc2626';
            dateFrom.style.background = '#fef2f2';
            showDateError(dateFrom, '⚠️ Maksimal tanggal: ' + maxDateStr + ' (hari ini)');
            hasError = true;
            errorMessages.push('Tanggal awal tidak boleh melebihi ' + maxDateStr);
        } else {
            dateFrom.style.borderColor = '#10b981';
            dateFrom.style.background = '';
            removeDateError(dateFrom);
        }
        
        if (toDateStr < minDateStr) {
            dateTo.style.borderColor = '#dc2626';
            dateTo.style.background = '#fef2f2';
            showDateError(dateTo, '⚠️ Minimal tanggal: ' + minDateStr + ' (tanggal service dibuat)');
            hasError = true;
            errorMessages.push('Tanggal akhir tidak boleh sebelum ' + minDateStr);
        } else if (toDateStr > maxDateStr) {
            dateTo.style.borderColor = '#dc2626';
            dateTo.style.background = '#fef2f2';
            showDateError(dateTo, '⚠️ Maksimal tanggal: ' + maxDateStr + ' (hari ini)');
            hasError = true;
            errorMessages.push('Tanggal akhir tidak boleh melebihi ' + maxDateStr);
        } else if (toDateStr < fromDateStr) {
            dateTo.style.borderColor = '#dc2626';
            dateTo.style.background = '#fef2f2';
            showDateError(dateTo, '⚠️ Tidak boleh kurang dari tanggal awal');
            hasError = true;
            errorMessages.push('Tanggal akhir harus >= tanggal awal');
        } else {
            dateTo.style.borderColor = '#10b981';
            dateTo.style.background = '';
            removeDateError(dateTo);
        }
        
        if (btnDownload) {
            btnDownload.disabled = hasError;
            if (hasError) {
                btnDownload.title = '❌ ' + errorMessages.join('; ');
            } else {
                btnDownload.disabled = false;
                btnDownload.title = '📥 Download PDF';
                btnDownload.style.opacity = '1';
                btnDownload.style.cursor = 'pointer';
            }
        }
        
        if (!hasError && toDateStr >= fromDateStr) {
            var fromParts = fromDateStr.split('-');
            var toParts = toDateStr.split('-');
            var fromDateObj = new Date(fromParts[0], fromParts[1] - 1, fromParts[2]);
            var toDateObj = new Date(toParts[0], toParts[1] - 1, toParts[2]);
            var diffDays = Math.floor((toDateObj.getTime() - fromDateObj.getTime()) / (1000 * 60 * 60 * 24)) + 1;
            
            var notice = document.getElementById('downloadNotice');
            if (notice) {
                notice.innerHTML = '✅ <strong>Periode valid:</strong> ' + 
                    fromDateStr + ' sampai ' + toDateStr + 
                    ' (' + diffDays + ' hari)' +
                    ' | Service berusia ' + downloadServiceData.ageInDays + ' hari';
            }
        }
    }

    function showDateError(input, message) {
        var parent = input.parentElement;
        var errorDiv = parent.querySelector('.date-error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'date-error-message';
            errorDiv.style.cssText = 'color: #dc2626; font-size: 12px; margin-top: 4px;';
            parent.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }

    function removeDateError(input) {
        var parent = input.parentElement;
        var errorDiv = parent.querySelector('.date-error-message');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    }

    // ================= SELECT PERIODE =================
    function selectPeriod(element, days) {
        if (element.classList.contains('disabled') || element.style.pointerEvents === 'none') {
            var msg = '❌ Periode ' + days + ' hari belum tersedia.';
            if (downloadServiceData) {
                msg += ' Service ini baru memiliki ' + downloadServiceData.availableDays + ' hari data.';
            }
            showToast('warning', 'Periode Tidak Tersedia', msg);
            return;
        }
        
        var periodBtns = document.querySelectorAll('.period-btn');
        for (var i = 0; i < periodBtns.length; i++) {
            periodBtns[i].classList.remove('active');
        }
        element.classList.add('active');
        selectedPeriod = days;
        
        var today = new Date();
        var pastDate = new Date(today);
        pastDate.setDate(pastDate.getDate() - days);
        
        if (downloadServiceData && pastDate < downloadServiceData.createdAt) {
            pastDate = new Date(downloadServiceData.createdAt);
            showToast('info', 'Info', '📅 Tanggal awal disesuaikan dengan tanggal service dibuat (' + 
                formatDateLocal(pastDate) + ')');
        }
        
        document.getElementById('dateFrom').value = formatDateLocal(pastDate);
        document.getElementById('dateTo').value = formatDateLocal(today);
        
        validateDateRange();
    }

    // ================= DOWNLOAD REPORT =================
    function downloadReport() {
        var dateFrom = document.getElementById('dateFrom').value;
        var dateTo = document.getElementById('dateTo').value;
        
        if (!dateFrom || !dateTo) {
            showToast('warning', 'Peringatan!', 'Silakan pilih periode laporan terlebih dahulu');
            return;
        }
        
        var todayStr = formatDateLocal(new Date());
        
        if (dateFrom > dateTo) {
            showToast('warning', 'Peringatan!', '📅 Tanggal awal tidak boleh lebih besar dari tanggal akhir');
            return;
        }
        
        if (dateTo > todayStr) {
            showToast('info', 'Info', '📅 Data akan diproses sesuai yang tersedia.');
        }
        
        if (downloadServiceData) {
            var minDate = downloadServiceData.minDate;
            var maxDate = downloadServiceData.maxDate;
            
            if (dateFrom < minDate) {
                showToast('warning', 'Periode Tidak Valid', 
                    '❌ Service "' + downloadServiceData.serviceName + '" dibuat pada ' + minDate + 
                    '.\n📅 Tidak ada data sebelum tanggal tersebut.\n\n💡 Silakan pilih tanggal dari ' + minDate + ' sampai ' + maxDate);
                return;
            }
            
            if (dateTo > maxDate) {
                showToast('warning', 'Periode Tidak Valid', 
                    '❌ Service "' + downloadServiceData.serviceName + '" baru memiliki ' + downloadServiceData.availableDays + 
                    ' hari data.\n📅 Data hanya tersedia sampai ' + maxDate + 
                    '.\n\n💡 Silakan pilih tanggal dari ' + minDate + ' sampai ' + maxDate);
                return;
            }
        }
        
        var btn = document.getElementById('btnDownloadNow');
        var loading = document.getElementById('downloadLoading');
        
        btn.disabled = true;
        btn.textContent = '⏳ Memproses...';
        loading.style.display = 'block';
        
        var url = '/services/' + currentDownloadId + '/download-report?' + new URLSearchParams({
            date_from: dateFrom,
            date_to: dateTo,
            format: 'pdf',
            _: Date.now()
        });
        
        var newWindow = window.open(url, '_blank');
        if (!newWindow) {
            showToast('warning', 'Perhatian!', 'Browser Anda memblokir popup. Izinkan popup untuk mendownload PDF.');
            btn.disabled = false;
            btn.textContent = '📥 Download PDF';
            loading.style.display = 'none';
            return;
        }
        
        setTimeout(function() {
            btn.disabled = false;
            btn.textContent = '📥 Download PDF';
            loading.style.display = 'none';
            showToast('success', 'Berhasil!', '📄 Laporan PDF berhasil diunduh');
        }, 3000);
    }

    function closeDownloadModal() {
        document.getElementById('downloadModal').classList.remove('active');
        document.body.style.overflow = '';
        currentDownloadId = null;
        downloadServiceData = null;
        document.dispatchEvent(new Event('modalClosed'));
    }

    // ================= CREATE / EDIT MODAL =================
    function openCreateModal() {
        const modal = document.getElementById('serviceModal');
        const form = document.getElementById('serviceForm');
        const closeBtn = document.getElementById('modalCloseBtn');
        const cancelBtn = document.getElementById('btnCancelModal');
        const submitBtn = document.getElementById('btnSubmitModal');
        
        closeBtn.disabled = false;
        cancelBtn.disabled = false;
        submitBtn.disabled = false;
        submitBtn.textContent = '💾 Simpan Service';
        submitBtn.className = 'btn-submit-modal';
        
        form.reset();
        document.getElementById('serviceId').value = '';
        document.getElementById('formMethod').value = 'POST';
        form.action = '{{ route('services.store') }}';
        
        document.getElementById('modal_type').value = 'http';
        updateHelperText('http');
        
        document.getElementById('modalTitle').textContent = 'Tambah Service';
        document.getElementById('modalIcon').textContent = '➕';
        document.getElementById('modalIcon').style.background = 'linear-gradient(135deg, #4f46e5, #7c3aed)';
        
        document.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        document.dispatchEvent(new Event('modalOpened'));
        setTimeout(() => document.getElementById('modal_name').focus(), 100);
    }

    function openEditModal(id) {
        const modal = document.getElementById('serviceModal');
        const submitBtn = document.getElementById('btnSubmitModal');
        const cancelBtn = document.getElementById('btnCancelModal');
        const closeBtn = document.getElementById('modalCloseBtn');
        
        const service = servicesMap[id];
        
        if (!service) {
            showToast('error', 'Gagal!', 'Data service tidak ditemukan');
            return;
        }
        
        document.getElementById('modalTitle').textContent = 'Edit Service';
        document.getElementById('modalIcon').textContent = '✏️';
        document.getElementById('modalIcon').style.background = 'linear-gradient(135deg, #d97706, #b45309)';
        
        submitBtn.disabled = false;
        submitBtn.textContent = '💾 Update Service';
        submitBtn.className = 'btn-submit-modal edit-mode';
        cancelBtn.disabled = false;
        closeBtn.disabled = false;
        
        document.getElementById('modal_name').value = service.name;
        document.getElementById('modal_target').value = service.target;
        document.getElementById('modal_type').value = service.type || 'http';
        document.getElementById('serviceId').value = service.id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('serviceForm').action = `/services/${service.id}`;
        
        updateHelperText(service.type || 'http');
        
        document.querySelectorAll('.form-control.error').forEach(el => el.classList.remove('error'));
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        document.dispatchEvent(new Event('modalOpened'));
        
        setTimeout(() => document.getElementById('modal_name').focus(), 100);
    }

    function closeModal() {
        document.getElementById('modalCloseBtn').disabled = false;
        document.getElementById('btnCancelModal').disabled = false;
        document.getElementById('btnSubmitModal').disabled = false;
        
        document.getElementById('serviceModal').classList.remove('active');
        document.body.style.overflow = '';
        document.dispatchEvent(new Event('modalClosed'));
    }

    function submitForm() {
        const form = document.getElementById('serviceForm');
        const submitBtn = document.getElementById('btnSubmitModal');
        const cancelBtn = document.getElementById('btnCancelModal');
        const closeBtn = document.getElementById('modalCloseBtn');
        const name = document.getElementById('modal_name');
        const target = document.getElementById('modal_target');
        const type = document.getElementById('modal_type');
        const serviceId = document.getElementById('serviceId').value;
        
        let hasError = false;
        
        if (name.value.trim() === '') {
            showFieldError(name, 'Nama service wajib diisi');
            hasError = true;
        } else if (name.value.length < 3) {
            showFieldError(name, 'Nama minimal 3 karakter');
            hasError = true;
        } else {
            removeFieldError(name);
        }
        
        if (target.value.trim() === '') {
            showFieldError(target, 'Target wajib diisi');
            hasError = true;
        } else if (type.value !== 'ping') {
            if (!/^https?:\/\/.+/i.test(target.value.trim())) {
                showFieldError(target, 'URL harus diawali dengan http:// atau https://');
                hasError = true;
            } else {
                removeFieldError(target);
            }
        } else {
            removeFieldError(target);
        }
        
        if (hasError) {
            const firstError = document.querySelector('.form-control.error');
            if (firstError) {
                firstError.focus();
            }
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.textContent = '⏳ Menyimpan...';
        cancelBtn.disabled = true;
        closeBtn.disabled = true;
        
        form.submit();
    }

    function showFieldError(input, message) {
        input.classList.add('error');
        let errorDiv = input.parentElement.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.style.marginTop = '4px';
            input.parentElement.appendChild(errorDiv);
        }
        errorDiv.innerHTML = '⚠️ ' + message;
    }

    function removeFieldError(input) {
        input.classList.remove('error');
        const errorDiv = input.parentElement.querySelector('.error-message');
        if (errorDiv) errorDiv.remove();
    }

    // ================= KEYBOARD SHORTCUTS =================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
            closeDetailModal();
            closeDownloadModal();
            closeMultiDownloadModal();
            closeConfirmModal();
            hideSearchStatus();
        }
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
            const modal = document.getElementById('serviceModal');
            if (modal.classList.contains('active')) {
                e.preventDefault();
                submitForm();
            }
        }
    });

    // ================= REAL-TIME VALIDATION =================
    document.getElementById('modal_name').addEventListener('input', function() {
        if (this.value.trim() !== '' && this.value.length >= 3) {
            this.classList.remove('error');
            removeFieldError(this);
        }
    });

    document.getElementById('modal_target').addEventListener('input', function() {
        if (this.value.trim() !== '') {
            this.classList.remove('error');
            removeFieldError(this);
        }
    });

    // ================= EVENT LISTENER UNTUK VALIDASI DATE =================
    document.addEventListener('DOMContentLoaded', function() {
        var dateFrom = document.getElementById('dateFrom');
        var dateTo = document.getElementById('dateTo');
        
        if (dateFrom) {
            dateFrom.addEventListener('change', function() {
                validateDateRange();
            });
            dateFrom.addEventListener('input', function() {
                validateDateRange();
            });
        }
        
        if (dateTo) {
            dateTo.addEventListener('change', function() {
                validateDateRange();
            });
            dateTo.addEventListener('input', function() {
                validateDateRange();
            });
        }
    });
</script>
@endsection