<?php

namespace App\Services;

use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'] ?? null,
            'password' => Hash::make($data['password']),
            'age' => $data['age'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'region' => $data['region'] ?? null,
            'specialization' => $data['specialization'] ?? null,
            'education_level' => $data['education_level'] ?? null,
            'is_active' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $credentials)
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $user->update(['last_login_at' => now()]);
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function sendOtp(string $phoneNumber)
    {
        // Delete any existing OTP for this phone number
        OtpCode::where('phone_number', $phoneNumber)->delete();

        // Generate 6-digit OTP
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Create OTP record
        $otpCode = OtpCode::create([
            'phone_number' => $phoneNumber,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send SMS
        $this->smsService->sendOtp($phoneNumber, $code);

        return $otpCode;
    }

    public function verifyOtp(string $phoneNumber, string $code)
    {
        $otpCode = OtpCode::where('phone_number', $phoneNumber)
            ->where('code', $code)
            ->valid()
            ->first();

        if (!$otpCode) {
            throw ValidationException::withMessages([
                'code' => ['Invalid or expired OTP code.'],
            ]);
        }

        // Mark OTP as used
        $otpCode->markAsUsed();

        // Find or create user
        $user = User::where('phone_number', $phoneNumber)->first();
        
        if (!$user) {
            $user = User::create([
                'name' => 'User ' . substr($phoneNumber, -4),
                'email' => $phoneNumber . '@medicothink.com',
                'phone_number' => $phoneNumber,
                'password' => Hash::make(str()->random(16)),
                'phone_verified_at' => now(),
                'is_active' => true,
            ]);
        } else {
            $user->update([
                'phone_verified_at' => now(),
                'last_login_at' => now(),
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user)
    {
        $user->currentAccessToken()->delete();
    }

    public function updateProfile(User $user, array $data)
    {
        $user->update(array_filter($data));
        
        return $user->fresh();
    }
}