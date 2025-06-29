<?php

use App\Http\Controllers\AclController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CarTypeController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\DriverLicenseController;
use App\Http\Controllers\PlateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MonicreditPaymentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentScheduleController;
use App\Http\Controllers\KycController;
use App\Models\Car;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;


// use Illuminate\Support\Facades\Mail;

// Public authentication routes
Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register')->name('register');
    Route::post('login', 'login')->name('login');
    Route::post('login2', 'login2')->name('login2');

    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'reset']);

    // Protected authentication routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', 'logout')->name('logout');
        Route::post('logout2', 'logout2')->name('logout2');
        Route::post('refresh', 'refresh')->name('refresh');

        Route::post('/kyc', [KycController::class, 'store']);
        Route::get('/kyc', [KycController::class, 'index']);

        Route::prefix('licenses')->group(function () {
            Route::post('/apply', [DriverLicenseController::class, 'store']); // Apply for license
            Route::get('/', [DriverLicenseController::class, 'index']);       // List all licenses
            Route::get('/{id}', [DriverLicenseController::class, 'show']);    // Get a single license
        });

        Route::prefix('plate-number')->group(function () {
            Route::post('/apply', [PlateController::class, 'store']); // Apply for license
            Route::get('/', [PlateController::class, 'index']);       // List all licenses
            Route::get('/{id}', [PlateController::class, 'show']);    // Get a single license
        });


        Route::prefix('settings')->group(function () {
            Route::get('/profile', [ProfileController::class, 'show']);       // Get profile
            Route::put('/profile', [ProfileController::class, 'update']);     // Update profile
            Route::put('/change-password', [ProfileController::class, 'changePassword']);
            Route::delete('/delete-account', [ProfileController::class, 'deleteAccount']);
        });


        Route::prefix('car')->group(function () {
            Route::post('reg', [CarController::class, 'register']);
            Route::get('get-cars/', [CarController::class, 'getMyCars']);
            Route::get('cars/{id}', [CarController::class, 'show']);
            Route::put('cars/{id}', [CarController::class, 'update']);
            Route::delete('cars/{id}', [CarController::class, 'destroy']);
            Route::post('initiate', [CarController::class, 'InsertDetail']);
            Route::post('verify', [CarController::class, 'Verification']);
            Route::get('get-all-state', [CarController::class, 'getAllState']);
            Route::get('get-lga/{state_id}', [CarController::class, 'getLgaByState']);

        });


        Route::prefix('payment-schedule')->group(function () {
            Route::get('/', [PaymentScheduleController::class, 'getAllPaymentSchedule']);
            Route::post('/create', [PaymentScheduleController::class, 'store']);
            Route::get('/get-payment-head', [PaymentScheduleController::class, 'getAllPaymentHead']);
            Route::post('/get-payment-schedule', [PaymentScheduleController::class, 'getPaymentScheduleByPaymenthead']);

        });


        Route::prefix('payment')->group(function () {
            Route::post('/initialize', [PaymentController::class, 'initializePayment']);
            Route::post('/verify-payment/{transaction_id}', [PaymentController::class, 'verifyPayment']);
          

        });
      


        // Route::post('/payment/initialize', [MonicreditPaymentController::class, 'initializePayment']);
        // Route::get('/payment/verify', [MonicreditPaymentController::class, 'verifyPayment']);


        Route::get('/car-types', [CarTypeController::class, 'index']);

        Route::post('/restore-account', [ProfileController::class, 'restoreAccount']);

        Route::middleware('auth:sanctum')->prefix('2fa')->group(function () {
            Route::post('/enable-google', [TwoFactorController::class, 'enableGoogle2fa']);
            Route::post('/verify-google', [TwoFactorController::class, 'verifyGoogle2fa']);
            Route::post('/enable-email', [TwoFactorController::class, 'enableEmail2fa']);
            Route::post('/verify-email', [TwoFactorController::class, 'verifyEmail2fa']);
            Route::post('/disable', [TwoFactorController::class, 'disable2fa']);
            Route::post('/check-2fa-status', [TwoFactorController::class, 'check2faStatus']);
        });
    });

    // Social authentication routes
    Route::get('auth/{provider}', 'redirectToProvider');
    Route::get('auth/{provider}/callback', 'handleProviderCallback');
});

// Verification routes
// Route::controller(VerificationController::class)->group(function () {
//     Route::post('email/verify/send', 'sendEmailVerification');
//     Route::post('email/verify/resend', 'resendEmailVerification');
//     Route::post('user/verify', 'verifyUser');
//     Route::post('phone/verify/send', 'sendPhoneVerification');
// });

Route::prefix('verify')->group(function () {
    Route::post('email/verify/send', [VerificationController::class, 'sendVerification']);
    Route::post('email/verify/resend', [VerificationController::class, 'resendEmailVerification']);
    Route::post('user/verify', [VerificationController::class, 'verifyUser']);
});

// Car management routes
// Route::controller(CarController::class)->group(function () {
//     Route::post('reg', 'register');
//     Route::get('cars', 'getMyCars');
//     Route::get('cars/{id}', 'show');
//     Route::put('cars/{id}', 'update');
//     Route::delete('cars/{id}', 'destroy');
//     Route::post('initiate', 'InsertDetail');
//     Route::post('verify', 'Verification');

// });

  



// Route::prefix('licenses')->group(function () {
//     Route::post('/apply', [DriverLicenseController::class, 'store']); // Apply for license
//     Route::get('/', [DriverLicenseController::class, 'index']);       // List all licenses
//     Route::get('/{id}', [DriverLicenseController::class, 'show']);    // Get a single license
// });

// Add this outside the auth:sanctum group, since user is not authenticated yet
Route::post('/2fa/verify-login', [TwoFactorController::class, 'verifyLogin2fa']);

Route::get('/get-expiration', function () {
    $getAllCars = Car::where('user_id', 'J89SPg')->get();
    $mtd = [];

    foreach ($getAllCars as $car) {
        $expiration = Carbon::parse($car->registration_date - 1);

        if ($expiration->greaterThan(Carbon::now())) {
            $mtd[] = [
                'car_id' => $car->id,
                'expiration_date' => $expiration->toDateTimeString(), // format as 'Y-m-d H:i:s'
                'days_until_expiration' => Carbon::now()->diffInDays($expiration),
                'expires_in' => Carbon::now()->diffForHumans($expiration, [
                    'parts' => 2,
                    'short' => true,
                    'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
                ])
            ];
        }
    }

    return response()->json($mtd);
});

Route::middleware('auth:sanctum')->get('/reminder', [ReminderController::class, 'index']);




Route::middleware('auth:sanctum')->get('/notifications', [NotificationController::class, 'index']);
Route::middleware('auth:sanctum')->post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);



Route::prefix('acl')->name('acl.')->group(function () {
    Route::prefix('user')->name('user.')->group(function () {
        Route::match(['GET', 'POST'], '/', [AclController::class, 'get_paginated_user'])->name('index');
        Route::put('/{user_id}/permission', [AclController::class, 'attach_permission_to_user'])->name('permission');
        Route::get('/{user_id}/roles', [AclController::class, 'role_with_user_has_role'])->name('roles');
        Route::put('/{user_id}/role', [AclController::class, 'attach_role_to_user'])->name('role');
    });

    Route::prefix('role')->name('role.')->group(function () {
        Route::get('/', [AclController::class, 'getAllRoles'])->name('index');
        Route::post('/create', [AclController::class, 'create_role'])->name('create');
        Route::put('/{role_id}/permission', [AclController::class, 'attach_permission_to_role'])->name('attach_permission');
        Route::put('/{role_id}', [AclController::class, 'update_role'])->name('update');
        Route::get('/{role_id}/permissions', [AclController::class, 'get_role_permissions'])->name('permissions');
    });

    Route::prefix('permission')->name('permission.')->group(function () {
        Route::get('/all', [AclController::class, 'get_all_permission'])->name('index');
        Route::get('/permission_with_perm_has_role/{role_id}', [AclController::class, 'permission_with_perm_has_role'])->name('permission_with_perm_has_role');
        Route::get('/permission_with_user_has_perm/{user_id}', [AclController::class, 'permission_with_user_has_perm'])->name('permission_with_user_has_perm');
    });
});

// Route::post('/payment/initialize', [MonicreditPaymentController::class, 'initializePayment']);
// Route::get('/payment/verify', [MonicreditPaymentController::class, 'verifyPayment']);
