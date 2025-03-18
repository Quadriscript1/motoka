<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register')->name('register');
    Route::post('login', 'login')->name('login');
    
    // Protected authentication routes
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', 'logout')->name('logout');
        Route::post('refresh', 'refresh')->name('refresh');
    });
    
    // Social authentication routes
    Route::get('auth/{provider}', 'redirectToProvider');
    Route::get('auth/{provider}/callback', 'handleProviderCallback');
});

// Verification routes
Route::controller(VerificationController::class)->middleware('auth:api')->group(function () {
    Route::post('email/verify/send', 'sendEmailVerification');
    Route::post('email/verify', 'verifyEmail');
    Route::post('phone/verify/send', 'sendPhoneVerification');
    Route::post('phone/verify', 'verifyPhone');
});
