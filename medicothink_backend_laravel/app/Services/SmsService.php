<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $apiKey;
    protected $apiUrl;
    protected $fromNumber;

    public function __construct()
    {
        $this->apiKey = config('services.sms.api_key');
        $this->apiUrl = config('services.sms.api_url');
        $this->fromNumber = config('services.sms.from_number');
    }

    public function sendOtp(string $phoneNumber, string $code)
    {
        $message = "رمز التحقق الخاص بك في MedicoThink هو: {$code}. صالح لمدة 10 دقائق.";
        
        return $this->sendSms($phoneNumber, $message);
    }

    public function sendWelcomeMessage(string $phoneNumber, string $userName)
    {
        $message = "مرحباً {$userName}! تم إنشاء حسابك في MedicoThink بنجاح. ابدأ رحلتك مع الذكاء الاصطناعي الطبي الآن.";
        
        return $this->sendSms($phoneNumber, $message);
    }

    public function sendSubscriptionNotification(string $phoneNumber, string $planName, string $expiryDate)
    {
        $message = "تنتهي صلاحية اشتراكك في باقة {$planName} في {$expiryDate}. جدد اشتراكك للاستمرار في الاستفادة من الميزات المتقدمة.";
        
        return $this->sendSms($phoneNumber, $message);
    }

    protected function sendSms(string $phoneNumber, string $message)
    {
        try {
            // This is a generic SMS implementation
            // Replace with your actual SMS provider's API
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/send', [
                'from' => $this->fromNumber,
                'to' => $phoneNumber,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("SMS sent successfully to {$phoneNumber}");
                return true;
            }

            Log::error("SMS sending failed: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("SMS Service Error: " . $e->getMessage());
            return false;
        }
    }
}