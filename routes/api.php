<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CarTypeController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\DriverLicenseController;
use App\Http\Controllers\PlateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

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
        });

        Route::get('/car-types', [CarTypeController::class, 'index']);

        Route::post('/restore-account', [ProfileController::class, 'restoreAccount']);

        Route::middleware('auth:sanctum')->prefix('2fa')->group(function () {
            Route::post('/enable-google', [TwoFactorController::class, 'enableGoogle2fa']);
            Route::post('/verify-google', [TwoFactorController::class, 'verifyGoogle2fa']);
            Route::post('/enable-email', [TwoFactorController::class, 'enableEmail2fa']);
            Route::post('/verify-email', [TwoFactorController::class, 'verifyEmail2fa']);
            Route::post('/disable', [TwoFactorController::class, 'disable2fa']);
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