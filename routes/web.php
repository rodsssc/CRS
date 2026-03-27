<?php

use App\Http\Controllers\admin\booking\bookingController as adminBookingController;
use App\Http\Controllers\CarDataController;
use App\Http\Controllers\admin\car\carController as adminCarController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\maintenance\MaintenanceController;
use App\Http\Controllers\admin\payment\PaymentController;
use App\Http\Controllers\admin\SalesReportController;
use App\Http\Controllers\admin\user\userController;
use App\Http\Controllers\admin\verification\verificationController as adminVerificationController;
use App\Http\Controllers\admin\report\ReportController;
use App\Http\Controllers\client\booking\bookingController as clientBookingController;
use App\Http\Controllers\client\car\carController as clientCarController;
use App\Http\Controllers\client\profile\clientProfileController;
use App\Http\Controllers\client\verification\verificationController as clientVerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('client.home'))->name('home');
Route::prefix('get-cars')->name('public.cars.')->group(function () {
    Route::get('/',      [CarDataController::class, 'index'])->name('index');
    Route::get('/stats', [CarDataController::class, 'stats'])->name('stats');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Client Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:client'])->prefix('client')->name('client.')->group(function () {

        Route::get('/home', fn () => view('client.home'))->name('home');

        Route::prefix('verification')->name('verification.')->group(function () {
        // Verification routes
        Route::get('/',[clientVerificationController::class, 'index']) ->name('index');
        Route::post('/submit', [clientVerificationController::class, 'store']);  // Changed from '/verification' to '/submit'
        
        // Profile routes (profiling)
        Route::get('/profile/{id}',[clientProfileController::class, 'show'])      ->name('profile.show');
        Route::post('/profile',[clientProfileController::class, 'store'])     ->name('profile.store');
        Route::put('/profile/{id}',[clientProfileController::class, 'update'])    ->name('profile.update');
    });

        Route::middleware('verified.client')->group(function () {

            Route::prefix('bookings')->name('bookings.')->group(function () {
                Route::get('/',                   [clientBookingController::class, 'index'])       ->name('index');
                Route::get('/data',               [clientBookingController::class, 'list'])        ->name('data');
                Route::post('/',                  [clientBookingController::class, 'store'])       ->name('store');
                Route::get('/{booking}/payment',  [clientBookingController::class, 'payment'])     ->name('payment');
                Route::post('/{booking}/payment', [clientBookingController::class, 'storePayment'])->name('payment.store');
            });

            Route::prefix('car')->name('car.')->group(function () {
                Route::get('/',     [clientCarController::class, 'index'])->name('index');
                Route::get('/data', [clientCarController::class, 'data']) ->name('data');
                Route::get('/{id}', [clientCarController::class, 'show']) ->name('show');
            });

            Route::get('/contact', fn () => view('client.contact'))->name('contact');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Shared Admin Routes — dashboard + read-only views (admin, staff, owner)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:admin,staff,owner'])->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales',       [ReportController::class, 'sales'])      ->name('sales');
            Route::get('/commissions', [ReportController::class, 'commissions'])->name('commissions');
        });

        // Bookings — /pending MUST come before /{id} to avoid wildcard conflict
        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/',        [adminBookingController::class, 'index'])        ->name('index');
            Route::get('/pending', [adminBookingController::class, 'pendingCount']) ->name('pending');
            Route::get('/{id}',    [adminBookingController::class, 'show'])         ->name('show');
        });

        // Verification
        Route::prefix('verification')->name('verification.')->group(function () {
            Route::get('/',        [adminVerificationController::class, 'index'])        ->name('index');
            Route::get('/pending', [adminVerificationController::class, 'pendingCount']) ->name('pending');
            Route::get('/{id}',    [adminVerificationController::class, 'show'])         ->name('show');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Admin-Only Routes — full write access
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard/revenue-data', [DashboardController::class, 'revenueData'])->name('dashboard.revenue-data');

        // ── Users ────────────────────────────────────────────────────────────
        Route::prefix('user')->name('user.')->group(function () {
            Route::get('/',          [userController::class, 'index'])  ->name('index');
            Route::post('/',         [userController::class, 'store'])  ->name('store');
            Route::get('/{id}',      [userController::class, 'show'])   ->name('show');
            Route::get('/{id}/edit', [userController::class, 'edit'])   ->name('edit');
            Route::put('/{id}',      [userController::class, 'update']) ->name('update');
            Route::delete('/{id}',   [userController::class, 'destroy'])->name('destroy');
        });

        // ── Cars ─────────────────────────────────────────────────────────────
        Route::prefix('cars')->name('cars.')->group(function () {
            Route::get('/',          [adminCarController::class, 'index'])  ->name('index');
            Route::post('/',         [adminCarController::class, 'store'])  ->name('store');
            Route::get('/{id}',      [adminCarController::class, 'show'])   ->name('show');
            Route::get('/{id}/edit', [adminCarController::class, 'edit'])   ->name('edit');
            Route::put('/{id}',      [adminCarController::class, 'update']) ->name('update');
            Route::delete('/{id}',   [adminCarController::class, 'destroy'])->name('destroy');
        });

        // ── Maintenance ──────────────────────────────────────────────────────
        Route::prefix('maintenance')->name('maintenance.')->group(function () {
            Route::get('/',               [MaintenanceController::class, 'index'])       ->name('index');
            Route::post('/',              [MaintenanceController::class, 'store'])       ->name('store');
            Route::get('/{id}',           [MaintenanceController::class, 'show'])        ->name('show');
            Route::get('/{id}/edit',      [MaintenanceController::class, 'edit'])        ->name('edit');
            Route::put('/{id}',           [MaintenanceController::class, 'update'])      ->name('update');
            Route::post('/{id}/complete', [MaintenanceController::class, 'markComplete'])->name('complete');
            Route::delete('/{id}',        [MaintenanceController::class, 'destroy'])     ->name('destroy');
        });

        // ── Payments ─────────────────────────────────────────────────────────
        Route::prefix('payment')->name('payment.')->group(function () {
            Route::get('/',               [PaymentController::class, 'index'])        ->name('index');
            Route::post('/',              [PaymentController::class, 'store'])        ->name('store');
            Route::post('/{id}/complete', [PaymentController::class, 'markCompleted'])->name('markCompleted');
            Route::post('/{id}/failed',   [PaymentController::class, 'markFailed'])  ->name('markFailed');
            Route::delete('/{id}',        [PaymentController::class, 'destroy'])     ->name('destroy');
        });

        // ── Verification actions ──────────────────────────────────────────────
        Route::prefix('verification')->name('verification.')->group(function () {
            Route::post('/{id}/approve', [adminVerificationController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject',  [adminVerificationController::class, 'reject']) ->name('reject');
        });

        // ── Booking actions ───────────────────────────────────────────────────
        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::put('/{id}/approve',  [adminBookingController::class, 'approve']) ->name('approve');
            Route::put('/{id}/complete', [adminBookingController::class, 'complete'])->name('complete');
            Route::put('/{id}/reject',   [adminBookingController::class, 'reject'])  ->name('reject');
        });

        // ── Sales Reports ─────────────────────────────────────────────────────
        Route::prefix('sales-reports')->name('sales.')->group(function () {
            Route::get('/',               [SalesReportController::class, 'index'])       ->name('index');
            Route::get('/analytics',      [SalesReportController::class, 'analytics'])   ->name('analytics');
            Route::post('/',              [SalesReportController::class, 'store'])        ->name('store');
            Route::get('/{report}',       [SalesReportController::class, 'show'])         ->name('show');
            Route::put('/{report}',       [SalesReportController::class, 'update'])       ->name('update');
            Route::delete('/{report}',    [SalesReportController::class, 'destroy'])      ->name('destroy');
            Route::post('/bulk-generate', [SalesReportController::class, 'bulkGenerate'])->name('bulk-generate');
            Route::get('/export/csv',     [SalesReportController::class, 'exportCsv'])   ->name('export.csv');
        });

    });

}); 

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';