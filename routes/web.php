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
use App\Models\Rental;


// use App\Http\Controllers\ProfileController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('client.home');
})->name('home');

// Public JSON endpoint used by the landing page to load cars dynamically
Route::get('/get-cars', [CarDataController::class, 'index'])->name('public.cars.index');

/*
|--------------------------------------------------------------------------
| Guest Routes (Redirected if authenticated)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Auth routes are included from auth.php
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
        
        /*
        |----------------------------------------------------------------------
        | Public Client Routes (No Verification Required)
        |----------------------------------------------------------------------
        */
        
        // Home Page
        Route::get('/home', function () {
            return view('client.home');
        })->name('home');

          Route::prefix('verification')->name('verification.')->group(function () {
            // Verification Routes
            Route::get('/', [clientVerificationController::class, 'index'])->name('index');
            Route::post('/verification', [clientVerificationController::class, 'store'])->name('verification.store');
            
            // Profile Routes
            Route::get('/profile/{id}', [clientProfileController::class, 'show'])->name('profile.show');
            Route::post('/profile', [clientProfileController::class, 'store'])->name('profile.store');
            Route::put('/profile/{id}', [clientProfileController::class, 'update'])->name('profile.update');
        });
      
        /*
        |----------------------------------------------------------------------
        | Protected Client Routes (Verification Required)
        |----------------------------------------------------------------------
        */
        
        Route::middleware('verified.client')->group(function () {

                Route::prefix('bookings')->name('bookings.')->group(function () {
                    Route::get('/', [clientBookingController::class, 'index'])->name('index');
                    Route::get('/data', [clientBookingController::class, 'list'])->name('data');
                    Route::post('/', [clientBookingController::class, 'store'])->name('store');
                    Route::get('/{booking}/payment', [clientBookingController::class, 'payment'])->name('payment');
                    Route::post('/{booking}/payment', [clientBookingController::class, 'storePayment'])->name('payment.store');
                });
                
               
                Route::prefix('car')->name('car.')->group(function () {
                    Route::get('/', [clientCarController::class, 'index'])->name('index');
                    Route::get('/{id}', [clientCarController::class, 'show'])->name('show');
                    // Add show route later
                    
                });
                
                // Contact
                Route::get('/contact', function () {
                    return view('client.contact');
                })->name('contact');
            });

            Route::prefix('rentals')->name('rentals.')->group(function () {
            // Route::post('/', [RentalController::class, 'store'])->name('store');
            // Route::get('/', [RentalController::class, 'index'])->name('index');
            // Route::get('/{id}', [RentalController::class, 'show'])->name('show');
            });
    });
    
    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Admin, Staff, Owner) - READ ONLY
    |--------------------------------------------------------------------------
    */
    
    Route::middleware(['role:admin,staff,owner'])->prefix('admin')->name('admin.')->group(function () {
        
        // Dashboard - Accessible by admin, staff, owner
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/revenue-data', [DashboardController::class, 'revenueData'])->name('dashboard.revenue-data');
        
        // Car Management - VIEW ONLY  staff
        // Route::prefix('cars')->name('cars.')->group(function () {
        //     Route::get('/',[carController:: class ,'index'])->name('index');
        // });
        
        // Booking Management - Accessible by admin, staff, owner
        Route::prefix('bookings')->name('bookings.')->group(function () {
           
        });
        
        // Client Verification Management - Accessible by admin, staff, owner
        Route::prefix('verification')->name('verification.')->group(function () {
           
        });
        
        // Reports - Accessible by admin, staff, owner
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/commissions', [ReportController::class, 'commissions'])->name('commissions');
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | Owner Only Routes - Car CRUD Operations
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Admin Only Routes
    |--------------------------------------------------------------------------
    */
    
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        
        // User Management - ADMIN ONLY
        Route::prefix('user')->name('user.')->group(function () {
            Route::get('/',[userController:: class ,'index'])->name('index');
            Route::post('/',[userController:: class ,'store'])->name('store');
            Route::get('/{id}',[userController:: class ,'show'])->name('show');
            Route::get('/{id}/edit',[userController:: class ,'edit'])->name('edit');
            Route::put('/{id}',[userController:: class ,'update'])->name('update');
            Route::delete('/{id}',[userController:: class ,'destroy'])->name('destroy');
        });

        Route::prefix('cars')->name('cars.')->group(function () {
            Route::get('/',[adminCarController:: class ,'index'])->name('index');
             Route::get('/{id}/edit',[adminCarController:: class ,'edit'])->name('edit');
            Route::get('/{id}',[adminCarController:: class ,'show'])->name('show');
           
            Route::put('/{id}',[adminCarController:: class ,'update'])->name('update');
            Route::post('/',[adminCarController:: class ,'store'])->name('store');
            Route::delete('/{id}',[adminCarController:: class ,'destroy'])->name('destroy');
        });

        Route::prefix('maintenance')->name('maintenance.')->group(function () {
            Route::get('/', [MaintenanceController::class, 'index'])->name('index');
            Route::post('/', [MaintenanceController::class, 'store'])->name('store');
            Route::get('/{id}', [MaintenanceController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [MaintenanceController::class, 'edit'])->name('edit');
            Route::put('/{id}', [MaintenanceController::class, 'update'])->name('update');
            Route::post('/{id}/complete', [MaintenanceController::class, 'markComplete'])->name('complete');
            Route::delete('/{id}', [MaintenanceController::class, 'destroy'])->name('destroy');
        });

        // Payment Management - ADMIN ONLY
        Route::prefix('payment')->name('payment.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::post('/', [PaymentController::class, 'store'])->name('store');
            Route::post('/{id}/complete', [PaymentController::class, 'markCompleted'])->name('markCompleted');
            Route::post('/{id}/failed', [PaymentController::class, 'markFailed'])->name('markFailed');
            Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('destroy');
        });
        
        // System Settings - ADMIN ONLY
        Route::prefix('verification')->name('verification.')->group(function () {
            Route::get('/', [adminVerificationController::class, 'index'])->name('index');
            Route::get('/{id}', [adminVerificationController::class, 'show'])->name('show');
            Route::post('/{id}/approve', [adminVerificationController::class, 'approve'])->name('approve');
            Route::post('/{id}/reject', [adminVerificationController::class, 'reject'])->name('reject');
            
        });


         Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/', [adminBookingController::class, 'index'])->name('index');
            Route::get('/{id}', [adminBookingController::class, 'show'])->name('show');
            Route::put('/{id}/approve', [adminBookingController::class, 'approve'])->name('approve');
            Route::put('/{id}/complete', [adminBookingController::class, 'complete'])->name('complete');
        });
        
        // Sales Reports - ADMIN ONLY
        Route::prefix('sales-reports')->name('sales.')->group(function () {
            Route::get('/', [SalesReportController::class, 'index'])->name('index');
            Route::get('/analytics', [SalesReportController::class, 'analytics'])->name('analytics');
            Route::post('/', [SalesReportController::class, 'store'])->name('store');
            Route::get('/{report}', [SalesReportController::class, 'show'])->name('show');
            Route::put('/{report}', [SalesReportController::class, 'update'])->name('update');
            Route::delete('/{report}', [SalesReportController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-generate', [SalesReportController::class, 'bulkGenerate'])->name('bulk-generate');
            Route::get('/export/csv', [SalesReportController::class, 'exportCsv'])->name('export.csv');
        });

        // Commissions - ADMIN ONLY
        Route::prefix('commissions')->name('commissions.')->group(function () {
            Route::get('/', [CommissionController::class, 'index'])->name('index');
            Route::get('/analytics', [CommissionController::class, 'analytics'])->name('analytics');
            Route::get('/my-summary', [CommissionController::class, 'mySummary'])->name('my-summary');
            Route::post('/', [CommissionController::class, 'store'])->name('store');
            Route::post('/bulk-mark-paid', [CommissionController::class, 'bulkMarkPaid'])->name('bulk-mark-paid');
            Route::put('/{commission}/mark-paid', [CommissionController::class, 'markAsPaid'])->name('mark-paid');
            Route::put('/{commission}/mark-earned', [CommissionController::class, 'markAsEarned'])->name('mark-earned');
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | Shared Authenticated Routes (All Users)
    |--------------------------------------------------------------------------
    */
    
    // Route::prefix('profile')->name('profile.')->group(function () {
    //     Route::get('/', [ProfileController::class, 'edit'])->name('edit');
    //     Route::patch('/', [ProfileController::class, 'update'])->name('update');
    //     Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    // });
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';