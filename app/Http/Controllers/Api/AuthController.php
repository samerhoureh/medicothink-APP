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
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'is_active' => true
            ]);

            $token = JWTAuth::fromUser($user);

            // Send welcome SMS if phone number provided
            if ($request->phone_number) {
                $this->smsService->sendWelcomeMessage($request->phone_number, $request->name);
            }

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = auth()->user();
            $user->update(['last_login_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token'
            ], 500);
        }
    }

    public function otpLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Generate 6-digit OTP
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Delete any existing OTP for this phone number
            OtpCode::where('phone_number', $request->phone_number)->delete();
            
            // Create new OTP
            OtpCode::create([
                'phone_number' => $request->phone_number,
                'code' => $code,
                'expires_at' => now()->addMinutes(10),
                'is_verified' => false
            ]);

            // Send OTP via SMS
            $smsSent = $this->smsService->sendOtp($request->phone_number, $code);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'data' => [
                    'phone_number' => $request->phone_number,
                    'expires_in' => 600, // 10 minutes
                    'sms_sent' => $smsSent
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|max:20',
            'code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $otpCode = OtpCode::where('phone_number', $request->phone_number)
                ->where('code', $request->code)
                ->where('is_verified', false)
                ->first();

            if (!$otpCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP code'
                ], 401);
            }

            if ($otpCode->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP code has expired'
                ], 401);
            }

            // Mark OTP as verified
            $otpCode->update(['is_verified' => true]);

            // Find or create user
            $user = User::where('phone_number', $request->phone_number)->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => 'User ' . substr($request->phone_number, -4),
                    'email' => $request->phone_number . '@medicothink.com',
                    'phone_number' => $request->phone_number,
                    'password' => Hash::make(str_random(16)),
                    'is_active' => true
                ]);
            }

            $user->update(['last_login_at' => now()]);
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'OTP verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            
            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout'
            ], 500);
        }
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            
            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token could not be refreshed'
            ], 401);
        }
    }

    public function me()
    {
        try {
            $user = auth()->user();
            $user->load(['activeSubscription']);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user data'
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . auth()->id(),
            'phone_number' => 'sometimes|string|max:20',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female,other',
            'medical_history' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();
            $user->update($request->only([
                'name', 'email', 'phone_number', 'date_of_birth', 'gender', 'medical_history'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => $user->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}