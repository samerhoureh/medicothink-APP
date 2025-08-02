<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpCode;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
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
        
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'access_token' => $token,
            'refresh_token' => $this->generateRefreshToken($user),
            'user' => $user->load('subscription')
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|unique:users',
            'password' => 'required|string|min:8',
            'age' => 'nullable|integer|min:13|max:120',
            'city' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'education_level' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
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

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'access_token' => $token,
            'refresh_token' => $this->generateRefreshToken($user),
            'user' => $user
        ], 201);
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $phoneNumber = $request->phone_number;
        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        // Create or update OTP code
        OtpCode::updateOrCreate(
            ['phone_number' => $phoneNumber, 'is_used' => false],
            [
                'code' => $code,
                'expires_at' => now()->addMinutes(5),
                'attempts' => 0,
            ]
        );

        // Send SMS
        try {
            $this->smsService->sendOtp($phoneNumber, $code);
            
            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP'
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'otp' => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $otpCode = OtpCode::where('phone_number', $request->phone_number)
            ->where('code', $request->otp)
            ->valid()
            ->first();

        if (!$otpCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        $otpCode->markAsUsed();

        // Find or create user
        $user = User::where('phone_number', $request->phone_number)->first();
        
        if (!$user) {
            $user = User::create([
                'username' => 'User_' . substr($request->phone_number, -4),
                'phone_number' => $request->phone_number,
                'password' => Hash::make(str_random(16)),
                'is_active' => true,
                'phone_verified_at' => now(),
            ]);
        } else {
            $user->update(['phone_verified_at' => now()]);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'access_token' => $token,
            'refresh_token' => $this->generateRefreshToken($user),
            'user' => $user->load('subscription')
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    public function refresh()
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());

        return response()->json([
            'success' => true,
            'access_token' => $token
        ]);
    }

    private function generateRefreshToken($user)
    {
        return base64_encode($user->id . '|' . now()->addDays(30)->timestamp);
    }
}