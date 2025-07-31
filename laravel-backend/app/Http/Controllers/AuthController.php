<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\TwilioService;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
        $this->middleware('auth:api', ['except' => ['login', 'register', 'otpLogin', 'verifyOtp']]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:3|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'age' => 'nullable|integer|min:13|max:120',
            'city' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|in:Medical,Dentist,Pharmacy,X-ray',
            'education_level' => 'nullable|string|in:High School,Bachelor,Master,PhD,Other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'age' => $request->age,
            'city' => $request->city,
            'nationality' => $request->nationality,
            'specialization' => $request->specialization,
            'education_level' => $request->education_level,
            'is_active' => true,
        ]);

        $token = JWTAuth::fromUser($user);
        $refreshToken = $this->createRefreshToken($user);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = auth()->user();
        $user->update(['last_login_at' => now()]);

        // Check subscription status
        if (!$user->hasActiveSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'Your subscription has expired. Please renew to continue.',
                'subscription_expired' => true
            ], 403);
        }

        $refreshToken = $this->createRefreshToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ]);
    }

    public function otpLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number not registered'
            ], 404);
        }

        if (!$user->hasActiveSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'Your subscription has expired. Please renew to continue.',
                'subscription_expired' => true
            ], 403);
        }

        $otpCode = OtpCode::createForUser($user->id, $request->phone_number);

        // Send OTP via Twilio
        try {
            $this->twilioService->sendOtp($request->phone_number, $otpCode->code);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
            'expires_in' => OtpCode::EXPIRY_MINUTES * 60
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|max:20',
            'otp' => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number not registered'
            ], 404);
        }

        $otpCode = OtpCode::where('user_id', $user->id)
                         ->where('phone_number', $request->phone_number)
                         ->where('code', $request->otp)
                         ->valid()
                         ->first();

        if (!$otpCode) {
            // Increment attempts for existing codes
            OtpCode::where('user_id', $user->id)
                   ->where('phone_number', $request->phone_number)
                   ->whereNull('verified_at')
                   ->increment('attempts');

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        $otpCode->verify();
        $user->markPhoneAsVerified();
        $user->update(['last_login_at' => now()]);

        $token = JWTAuth::fromUser($user);
        $refreshToken = $this->createRefreshToken($user);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'user' => new UserResource($user),
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ]);
    }

    public function refresh(Request $request)
    {
        try {
            $token = JWTAuth::refresh();
            $user = auth()->user();
            $refreshToken = $this->createRefreshToken($user);

            return response()->json([
                'success' => true,
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed'
            ], 401);
        }
    }

    public function logout()
    {
        JWTAuth::invalidate();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    public function profile()
    {
        return response()->json([
            'success' => true,
            'user' => new UserResource(auth()->user())
        ]);
    }

    private function createRefreshToken($user)
    {
        return JWTAuth::customClaims(['type' => 'refresh'])->fromUser($user);
    }
}