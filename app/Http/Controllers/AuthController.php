<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\SendEmailVerification;
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
    protected $verificationService;

    public function __construct(VerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
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

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);

        // Send verification code based on registration method
        try {
            if ($request->email) {
                $code = Str::random(6);
                $user->email_verification_code = $code;

                if ($user->save()) {
                    Mail::to($request->email)->queue(new SendEmailVerification($user, $code));
                }

                // $this->verificationService->sendEmailVerification($user);
            }
            if ($request->phone_number) {

                $code = rand(100000, 999999);
                $user->phone_verification_code = $code;

                if ($user->save()) {
                    Mail::to($request->email)->queue(new SendPhoneVerification($user, $code));
                }

                // $this->verificationService->sendPhoneVerification($user);
            }
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
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string', // This can be either email or phone
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check if login is email or phone number
        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';
        $credentials = [
            $loginField => $request->login,
            'password' => $request->password
        ];

        // if (!Auth::attempt($credentials)) {
        //     return response([
        //         "status" => false,
        //         "message" => "Email & Password does not match with our record"
        //     ], 404);
        // }
        // $token = $user->createToken("API TOKEN")->plainTextToken;

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = auth('api')->user();

        if (!$user) {
            auth('api')->logout();
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 401);
        }

        // Check if user needs to verify their credentials
        $verificationNeeded = [];
        if ($loginField === 'email' && !$user->email_verified_at) {
            $verificationNeeded[] = 'email';
        } elseif ($loginField === 'phone_number' && !$user->phone_verified_at) {
            $verificationNeeded[] = 'phone';
        }

        if (!empty($verificationNeeded)) {
            // Send new verification code
            try {
                if (in_array('email', $verificationNeeded)) {
                    $this->verificationService->sendEmailVerification($user);
                } elseif (in_array('phone', $verificationNeeded)) {
                    $this->verificationService->sendPhoneVerification($user);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send verification code: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'verification_required',
                'message' => 'Please verify your ' . implode(' and ', $verificationNeeded) . ' first',
                'verification_needed' => $verificationNeeded,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    // public function login2(Request $request)
    // {
    //     // dd('here');

    //     $credentials = $request->validate([
    //         'email' => 'nullable|string|email|max:255|exists:users,email',
    //         'phone_number' => 'nullable|string|exists:users,phone_number',
    //         'password' => 'required|string|min:6',
    //     ]);

    //     if ($request->email) {
    //         if (!Auth::attempt($request->only(['email', 'password']))) {
    //             return response()->json(['message' => 'Invalid email or password'], 401);
    //         }
    //     }

    //     if ($request->phone_number) {
    //         if (!Auth::attempt($request->only(['phone_number', 'password']))) {
    //             return response()->json(['message' => 'Invalid phone number or password'], 401);
    //         }
    //     }

    //     $user = Auth::user();
    //     $token = $user->createToken("API TOKEN")->plainTextToken;

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Login successful',
    //         'user' => $user,
    //         'authorization' => [
    //             'token' => $token,
    //             'type' => 'bearer',
    //         ]
    //     ]);
    // }

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


        $user = User::where('email', $request->email)
            ->orWhere('phone_number', $request->phone_number)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid credentials'], 401);
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
