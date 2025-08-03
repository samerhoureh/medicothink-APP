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
        $message = "Your MedicoThink verification code is: {$code}. Valid for 10 minutes. | رمز التحقق الخاص بك في MedicoThink هو: {$code}. صالح لمدة 10 دقائق.";
        
        return $this->sendSms($phoneNumber, $message);
    }

    public function sendWelcomeMessage(string $phoneNumber, string $userName)
    {
        $message = "Welcome {$userName}! Your MedicoThink account has been created successfully. | مرحباً {$userName}! تم إنشاء حسابك في MedicoThink بنجاح.";
        
        return $this->sendSms($phoneNumber, $message);
    }

    public function sendSubscriptionNotification(string $phoneNumber, string $planName, string $expiryDate)
    {
        $message = "Your {$planName} subscription expires on {$expiryDate}. Renew to continue enjoying premium features. | تنتهي صلاحية اشتراكك في باقة {$planName} في {$expiryDate}.";
        
        return $this->sendSms($phoneNumber, $message);
    }

    protected function sendSms(string $phoneNumber, string $message)
    {
        try {
            // Support multiple SMS providers
            if (config('app.env') === 'testing') {
                Log::info("SMS Test Mode - Would send to {$phoneNumber}: {$message}");
                return true;
            }

            // Try Twilio first if configured
            if (config('services.twilio.sid')) {
                return $this->sendViaTwilio($phoneNumber, $message);
            }

            // Fallback to generic SMS API
            return $this->sendViaGenericApi($phoneNumber, $message);

        } catch (\Exception $e) {
            Log::error("SMS Service Error: " . $e->getMessage());
            return false;
        }
    }

    protected function sendViaTwilio(string $phoneNumber, string $message)
    {
        try {
            $response = Http::withBasicAuth(
                config('services.twilio.sid'),
                config('services.twilio.token')
            )->asForm()->post("https://api.twilio.com/2010-04-01/Accounts/" . config('services.twilio.sid') . "/Messages.json", [
                'From' => config('services.twilio.from'),
                'To' => $phoneNumber,
                'Body' => $message,
            ]);

            if ($response->successful()) {
                Log::info("SMS sent successfully via Twilio to {$phoneNumber}");
                return true;
            }

            Log::error("Twilio SMS failed: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("Twilio SMS Error: " . $e->getMessage());
            return false;
        }
    }

    protected function sendViaGenericApi(string $phoneNumber, string $message)
    {
        try {
            if (!$this->apiUrl || !$this->apiKey) {
                Log::warning("SMS API not configured, using test mode");
                Log::info("SMS Test - To: {$phoneNumber}, Message: {$message}");
                return true;
            }
            
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
            Log::error("Generic SMS API Error: " . $e->getMessage());
            return false;
        }
    }
}