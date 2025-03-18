<?php

namespace App\Http\Controllers;

use App\Services\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    protected $verificationService;

    public function __construct(VerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Send email verification code
     */
    public function sendEmailVerification()
    {
        $user = Auth::user();
        
        if (!$user->email) {
            return response()->json([
                'status' => 'error',
                'message' => 'No email address associated with this account'
            ], 400);
        }
        
        if ($user->email_verified_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email already verified'
            ], 400);
        }

        try {
            $this->verificationService->sendEmailVerification($user);
            return response()->json([
                'status' => 'success',
                'message' => 'Verification code sent to your email'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send verification code'
            ], 500);
        }
    }

    /**
     * Send phone verification code
     */
    public function sendPhoneVerification()
    {
        $user = Auth::user();
        
        if (!$user->phone_number) {
            return response()->json([
                'status' => 'error',
                'message' => 'No phone number associated with this account'
            ], 400);
        }
        
        if ($user->phone_verified_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Phone number already verified'
            ], 400);
        }

        try {
            $this->verificationService->sendPhoneVerification($user);
            return response()->json([
                'status' => 'success',
                'message' => 'Verification code sent to your phone'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send verification code'
            ], 500);
        }
    }

    /**
     * Verify email with code
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = Auth::user();
        
        if ($user->email_verified_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email already verified'
            ], 400);
        }

        if ($this->verificationService->verifyEmail($user, $request->code)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email verified successfully'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid verification code'
        ], 400);
    }

    /**
     * Verify phone with code
     */
    public function verifyPhone(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = Auth::user();
        
        if ($user->phone_verified_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Phone number already verified'
            ], 400);
        }

        if ($this->verificationService->verifyPhone($user, $request->code)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Phone number verified successfully'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid verification code'
        ], 400);
    }
}
