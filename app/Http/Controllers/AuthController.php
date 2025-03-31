<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\VerificationController;
//use App\Mail\SendEmailVerification;
use App\Mail\SendPhoneVerification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\VerificationService;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $verificationController;

    public function __construct(VerificationController $verificationController)
    {
        $this->verificationController = $verificationController;
    }
    /**
     * Create a new user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required_without:phone_number|string|email|max:255|unique:users,email,NULL,id,deleted_at,NULL',
            'phone_number' => 'required_without:email|string|unique:users,phone_number,NULL,id,deleted_at,NULL',
            'password' => 'required|string|min:6',
        ]);

        // Custom message for either email or phone required
        if (!$request->email && !$request->phone_number) {
            return response()->json([
                'status' => 'error',
                'message' => 'Either email or phone number is required'
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

         // Check if email or phone number already exists in the database (excluding soft deleted users)
    $existingUser = User::where(function ($query) use ($request) {
        if ($request->email) {
            $query->orWhere('email', $request->email);
        }
        if ($request->phone_number) {
            $query->orWhere('phone_number', $request->phone_number);
        }
    })->whereNull('deleted_at')->first();

    if ($existingUser) {
        return response()->json([
            'status' => 'error',
            'message' => 'Email or phone number is already registered.'
        ], 422);
    }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);
        

        // Send verification code using VerificationController
        try {
            $verificationRequest = new Request();
            if ($request->email) {
                $verificationRequest->merge(['email' => $request->email]);
            }
            if ($request->phone_number) {
                $verificationRequest->merge(['phone_number' => $request->phone_number]);
            }
            $this->verificationController->sendVerification($verificationRequest);
        } catch (\Exception $e) {
            // Log the error but don't stop the registration process
            Log::error('Failed to send verification code: ' . $e->getMessage());
        }


        $token = $user->createToken("API TOKEN")->plainTextToken;

        $message = 'User created successfully. ';
        if ($request->email) {
            $message .= 'Please check your email for verification code. ';
        }
        if ($request->phone_number) {
            $message .= 'Please check your phone for verification code.';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Login user and create token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

     public function login2(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'email' => 'nullable|string|email|max:255|exists:users,email',
             'phone_number' => 'nullable|string|exists:users,phone_number',
             'password' => 'required|string|min:6',
         ]);
     
         if ($validator->fails()) {
             return response()->json($validator->errors(), 422);
         }
     
         $userQuery = User::query();
     
         if ($request->email) {
             $userQuery->where('email', $request->email);
         }
     
         if ($request->phone_number) {
             $userQuery->where('phone_number', $request->phone_number);
         }
     
         $user = $userQuery->first();
     
         if (!$user || !Hash::check($request->password, $user->password)) {
             return response()->json(['message' => 'Invalid credentials'], 401);
         }
     
         // **Check if email is verified**
         if ($request->email && is_null($user->email_verified_at)) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'Please verify your email before logging in.'
             ], 403);
         }
     
         $token = $user->createToken('API TOKEN')->plainTextToken;
     
         return response()->json([
             'status' => 'success',
             'message' => 'Login successful',
             'user' => $user,
             'authorization' => [
                 'token' => $token,
                 'type' => 'bearer',
             ]
         ]);
     }
     





    /**
     * Logout user (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function logout2(Request $request)
    {
        // dd('here');
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }


    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => auth('api')->user(),
            'authorization' => [
                'token' => auth('api')->refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Redirect the user to the provider authentication page.
     *
     * @param string $provider
     * @return \Illuminate\Http\JsonResponse
     */
    public function redirectToProvider($provider)
    {
        try {
            $url = Socialite::driver($provider)->redirect()->getTargetUrl();
            return response()->json(['url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unsupported provider'], 422);
        }
    }

    /**
     * Handle provider callback and authenticate user.
     *
     * @param string $provider
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();

            // Find existing user or create new
            $user = User::where('social_id', $socialUser->getId())
                ->where('social_type', $provider)
                ->first();

            if (!$user) {
                // Check if user exists with same email
                $user = User::where('email', $socialUser->getEmail())->first();

                if (!$user) {
                    // Create new user
                    $user = User::create([
                        'email_verified_at' => now(), // Social login users are pre-verified
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'social_id' => $socialUser->getId(),
                        'social_type' => $provider,
                        'avatar' => $socialUser->getAvatar(),
                        'password' => Hash::make(Str::random(16)), // Random password for social users
                    ]);
                } else {
                    // Update existing user with social info
                    $user->update([
                        'social_id' => $socialUser->getId(),
                        'social_type' => $provider,
                        'avatar' => $socialUser->getAvatar(),
                    ]);
                }
            }

            $token = Auth::login($user);

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to authenticate'], 422);
        }
    }
}
