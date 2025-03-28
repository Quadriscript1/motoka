<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register')->name('register');
    Route::post('login', 'login')->name('login');
    Route::post('login2', 'login2')->name('login2');

    // Protected authentication routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', 'logout')->name('logout');
        Route::post('logout2', 'logout2')->name('logout2');
        Route::post('refresh', 'refresh')->name('refresh');
    });

    // Social authentication routes
    Route::get('auth/{provider}', 'redirectToProvider');
    Route::get('auth/{provider}/callback', 'handleProviderCallback');
});

// Verification routes
Route::controller(VerificationController::class)->group(function () {
    Route::post('email/verify/send', 'sendEmailVerification');
    Route::post('user/verify', 'verifyUser');
    Route::post('phone/verify/send', 'sendPhoneVerification');
    
});

// Car management routes
Route::controller(CarController::class)->middleware('auth:sanctum')->group(function () {
    Route::post('reg', 'register');
    Route::get('cars', 'getMyCars');
    Route::get('cars/{id}', 'show');
    Route::put('cars/{id}', 'update');
    Route::delete('cars/{id}', 'destroy');
});
