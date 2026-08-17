<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ContactController;
use App\Services\FonnteService;
use App\Http\Controllers\LogController;
use App\Http\Controllers\SmokeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\NetworkController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES - Monitoring System
|--------------------------------------------------------------------------
|
| Semua route di file ini mengembalikan HTML/View (Blade).
| Untuk API JSON, lihat routes/api.php
|
| Kategori:
| 1. Guest Routes - Login & Register
| 2. Protected Routes - Dashboard, Services, Contacts, Logs, Smoke
|
*/

// ============================================================
// GUEST ROUTES (Tanpa Login)
// ============================================================

Route::middleware('guest')->group(function () {
    
    // Login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    // Register
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ============================================================
// PROTECTED ROUTES (Wajib Login)
// ============================================================

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ==========================================================
    // SERVICES - CRUD Monitoring Service (PING / HTTP)
    // ==========================================================
    Route::get('/services', [ServiceController::class, 'index'])->name('services');
    Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{id}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');

    //  ROUTE ARCHIVE & RESTORE 
    Route::post('/services/{id}/archive', [ServiceController::class, 'archive'])->name('services.archive');
    Route::post('/services/{id}/restore', [ServiceController::class, 'restore'])->name('services.restore');
    Route::delete('/services/{id}/permanent', [ServiceController::class, 'destroyPermanent'])->name('services.destroy-permanent');

    // Search
    Route::get('/services/search', [ServiceController::class, 'search'])->name('services.search');

    // Detail & Laporan
    Route::get('/services/{id}/detail', [ServiceController::class, 'detail'])->name('services.detail');
    Route::get('/services/{id}/logs', [ServiceController::class, 'logs'])->name('services.logs');
    Route::post('/services/{id}/check', [ServiceController::class, 'check'])->name('services.check');
    Route::get('/services/overview', [ServiceController::class, 'overview'])->name('services.overview');
    Route::get('/services/export', [ServiceController::class, 'export'])->name('services.export');
    Route::delete('/services/bulk-delete', [ServiceController::class, 'bulkDelete'])->name('services.bulk-delete');
    Route::get('/services/{id}/health', [ServiceController::class, 'health'])->name('services.health');
    Route::get('/services/{id}/download-report', [ServiceController::class, 'downloadReport'])->name('services.download-report');
    Route::post('/services/download-multi-report', [ServiceController::class, 'downloadMultiReport'])->name('services.download-multi-report');

    // Interval WhatsApp
    Route::post('/services/{id}/wa-interval', [ServiceController::class, 'updateWaInterval'])->name('services.wa-interval');

    // ==========================================================
    // LOGS - Riwayat Monitoring
    // ==========================================================
    Route::get('/logs', [LogController::class, 'index'])->name('logs');
    Route::get('/logs/{id}', [LogController::class, 'show'])->name('logs.show');

    // ==========================================================
    // CONTACTS - Kontak WhatsApp
    // ==========================================================
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts');
    Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('/contacts/{id}/edit', [ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('/contacts/{id}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{id}', [ContactController::class, 'destroy'])->name('contacts.destroy');
    Route::get('/contacts/search', [ContactController::class, 'search'])->name('contacts.search');

    // ==========================================================
    // SMOKE DETECTOR - Monitoring ESP32 (Web)
    // ==========================================================
    Route::get('/smoke-detector', [SmokeController::class, 'index'])->name('smoke');

    // ==========================================================
    // SMOKE DETECTOR - API Receive (Untuk ESP32, Tanpa Auth)
    // ==========================================================
    // ⚠️ Route ini TIDAK menggunakan middleware auth karena ESP32 tidak punya session
    Route::post('/smoke/receive', [SmokeController::class, 'receiveData'])->name('smoke.receive');
    Route::get('/smoke/status', [SmokeController::class, 'getStatus'])->name('smoke.status');

});

// ============================================================
// TESTING - WhatsApp (Development Only)
// ============================================================
Route::get('/test-wa', function () {
    FonnteService::send(
        '6281234567890',
        'Test Laravel Monitoring berhasil 🚀'
    );
    return 'WhatsApp berhasil dikirim';
});