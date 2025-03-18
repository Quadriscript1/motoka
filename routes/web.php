<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use App\Services\VerificationService;

Route::get('/', function () {
    return view('welcome');
});

// Debug route to check mail configuration
Route::get('/debug-mail', function () {
    $mailConfig = [
        'env_mail_mailer' => env('MAIL_MAILER'),
        'env_mail_host' => env('MAIL_HOST'),
        'env_mail_port' => env('MAIL_PORT'),
        'env_mail_username' => env('MAIL_USERNAME'),
        'env_mail_password' => env('MAIL_PASSWORD'),
        'env_mail_encryption' => env('MAIL_ENCRYPTION'),
        'env_mail_from_address' => env('MAIL_FROM_ADDRESS'),
        'env_mail_from_name' => env('MAIL_FROM_NAME'),
        'config_mail_default' => config('mail.default'),
        'config_mail_from' => config('mail.from'),
        'config_mail_mailers_smtp' => config('mail.mailers.smtp')
    ];
    
    return response()->json($mailConfig);
});

// Test route for email verification
Route::get('/test-verification', function () {
    try {
        // Create a test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'phone_number' => '+1234567890'
            ]
        );

        // Initialize verification service
        $verificationService = new VerificationService();

        // Send verification code
        $code = $verificationService->sendEmailVerification($user);

        return response()->json([
            'message' => 'Test verification email sent successfully!',
            'user' => $user,
            'verification_code' => $code
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Test route to verify email
Route::get('/test-verify/{code}', function ($code) {
    try {
        $user = User::where('email', 'test@example.com')->first();
        if (!$user) {
            return response()->json(['error' => 'Test user not found'], 404);
        }

        $verificationService = new VerificationService();
        $result = $verificationService->verifyEmail($user, $code);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Email verified successfully!' : 'Invalid verification code',
            'user' => $user
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});
