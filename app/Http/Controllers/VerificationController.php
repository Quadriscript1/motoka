<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\OTP;
use App\Models\User;
use App\Mail\OTPMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Services\VerificationService;


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
    // public function sendEmailVerification()
    // {
    //     $user = auth('api')->user();

    //     if (!$user->email) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'No email address associated with this account'
    //         ], 400);
    //     }

    //     if ($user->email_verified_at) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Email already verified'
    //         ], 400);
    //     }

    //     try {
    //         $otp = $this->generateAndStoreOTP($user->email);

    //         Mail::to($user->email)->send(new OTPMail($otp));

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Verification code sent to your email'
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Failed to send verification code',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // private function generateAndStoreOTP($email)
    // {
    //     $otp = rand(100000, 999999);
    //     OTP::updateOrCreate(
    //         ['email' => $email],
    //         [
    //             'otp' => Hash::make($otp),
    //             'expires_at' => Carbon::now()->addMinutes(10)
    //         ]
    //     );

    //     return $otp;
    // }
    public function sendVerification(Request $request)
     {
         $request->validate([
             'email' => 'nullable|string|email|exists:users,email',
             'phone_number' => 'nullable|string|exists:users,phone_number',
         ]);
 
         if ($request->email) {
             $user = User::where('email', $request->email)->first();
             $verificationColumn = 'email_verification_code';
             $contact = $user->email;
         } elseif ($request->phone_number) {
             $user = User::where('phone_number', $request->phone_number)->first();
             $verificationColumn = 'phone_verification_code';
             $contact = $user->phone_number;
         } else {
             return response()->json([
                 'status' => 'error',
                 'message' => 'Either email or phone number is required'
             ], 422);
         }
 
         // Generate OTP
         $verificationCode = rand(100000, 999999);
         $user->$verificationColumn = $verificationCode;
         $user->save();
 
         if ($request->email) {
             // Send OTP via Email
             Mail::raw("Your verification code is: $verificationCode", function ($message) use ($user) {
                 $message->to($user->email)->subject('Email Verification Code');
             });
         } elseif ($request->phone_number) {
             // Send OTP via SMS (using Twilio or any SMS gateway)
             try {
                 // Example: Using Twilio (Replace with actual implementation)
                 $this->sendSms($contact, "Your verification code is: $verificationCode");
             } catch (\Exception $e) {
                 Log::error('SMS sending failed: ' . $e->getMessage());
                 return response()->json([
                     'status' => 'error',
                     'message' => 'Failed to send SMS'
                 ], 500);
             }
         }
 
         return response()->json([
             'status' => 'success',
             'message' => 'Verification code sent successfully'
         ]);
     }
 
     // Example SMS sending method (Replace with actual SMS provider logic)
     private function sendSms($phoneNumber, $message)
     {
         // Implement SMS API (e.g., Twilio, Nexmo, etc.)
         Log::info("Sending SMS to $phoneNumber: $message");
     }
    

    /**
     * Send phone verification code
     */
    // public function sendPhoneVerification()
    // {
    //     $user = Auth::user();

    //     if (!$user->phone_number) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'No phone number associated with this account'
    //         ], 400);
    //     }

    //     if ($user->phone_verified_at) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Phone number already verified'
    //         ], 400);
    //     }

    //     try {
    //         $this->verificationService->sendPhoneVerification($user);
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Verification code sent to your phone'
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Failed to send verification code'
    //         ], 500);
    //     }
    // }



    public function verifyUser(Request $request)
{
    $request->validate([
        'email' => 'nullable|string|exists:users,email',
        'phone_number' => 'nullable|string|exists:users,phone_number',
        'code' => 'required|string|size:6'
    ]);

    // Determine whether we're verifying by email or phone
    if ($request->email) {
        $user = User::where('email', $request->email)->first();
        $verificationColumn = 'email_verification_code';
        $verifiedColumn = 'email_verified_at';
    } elseif ($request->phone_number) {
        $user = User::where('phone_number', $request->phone_number)->first();
        $verificationColumn = 'phone_verification_code';
        $verifiedColumn = 'phone_verified_at';
    } else {
        return response()->json([
            'status' => 'error',
            'message' => 'Either email or phone number is required'
        ], 422);
    }

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found'
        ], 404);
    }

    // Check if already verified
    if ($user->$verifiedColumn) {
        return response()->json([
            'status' => 'error',
            'message' => ucfirst(str_replace('_', ' ', $verifiedColumn)) . ' already verified'
        ], 400);
    }

    // Verify the code
    if ($user->$verificationColumn === $request->code) {
        $user->$verifiedColumn = now();
        $user->$verificationColumn = null; // Clear the verification code
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => ucfirst(str_replace('_', ' ', $verifiedColumn)) . ' verified successfully'
        ]);
    }

    return response()->json([
        'status' => 'error',
        'message' => 'Invalid verification code'
    ], 400);
}


    /**
     * Verify email with code
     */
//     public function verifyEmail(Request $request)
//     {
//         $request->validate([
//             'email' => 'required|string|exists:users,email',
//             'code' => 'required|string|size:6'
//         ]);

//         $otpRecord = OTP::where('email', $request->email)->first();

//         if (!$otpRecord || !Hash::check($request->code, $otpRecord->otp)) {
//             return response()->json(['status' => 'error', 'message' => 'Invalid OTP'], 400);
//         }

//         if (Carbon::now()->greaterThan($otpRecord->expires_at)) {
//             return response()->json(['status' => 'error', 'message' => 'OTP expired'], 400);
//         }

//         // Mark user email as verified
//         $user = User::where('email', $request->email)->first();
//         $user->email_verified_at = now();
//         $user->save();

//         // Delete OTP after successful verification
//         $otpRecord->delete();

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Email verified successfully'
//         ]);
//     }

//     public function verifyEmail2(Request $request)
//     {
//         $request->validate([
//             'email' => 'required|string|exists:users,email',
//             'code' => 'required|string|size:6'
//         ]);

//         $getUser = User::where('email', $request->email)->first();

//         if (!$getUser) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'User not found'
//             ], 404);
//         }

//         if ($getUser->email_verification_code === $request->code) {
//             $getUser->email_verified_at = now();
//             $getUser->email_verification_code = null;
//             $getUser->save();
//         }

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Email verified successfully'
//         ]);
//     }

//     public function verifyPhone2(Request $request)
//     {
//         $request->validate([
//             'phone_number' => 'required|string|exists:users,phone_number',
//             'code' => 'required|string|size:6'
//         ]);

//         $getUser = User::where('phone_number', $request->phone_number)->first();


//         if (!$getUser) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'User not found'
//             ], 404);
//         }

//         if ($getUser->phone_verification_code === $request->code) {
//             $getUser->phone_verified_at = now();
//             $getUser->phone_verification_code = null;
//             $getUser->save();
//         }

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Phone number verified successfully'
//         ]);
//     }

//     /**
//      * Verify phone with code
//      */
//     public function verifyPhone(Request $request)
//     {
//         $request->validate([
//             'code' => 'required|string|size:6'
//         ]);

//         $user = Auth::user();

//         if ($user->phone_verified_at) {
//             return response()->json([
//                 'status' => 'error',
//                 'message' => 'Phone number already verified'
//             ], 400);
//         }

//         if ($this->verificationService->verifyPhone($user, $request->code)) {
//             return response()->json([
//                 'status' => 'success',
//                 'message' => 'Phone number verified successfully',
//                 'user' => $user
//             ]);
//         }

//         return response()->json([
//             'status' => 'error',
//             'message' => 'Invalid verification code'
//         ], 400);
//     }
 }
  

 