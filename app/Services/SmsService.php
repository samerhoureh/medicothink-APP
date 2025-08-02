<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private $twilio;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    public function sendOtp($phoneNumber, $code)
    {
        try {
            $message = "Your MedicoThink verification code is: {$code}. This code will expire in 10 minutes.";
            
            $this->twilio->messages->create($phoneNumber, [
                'from' => config('services.twilio.from'),
                'body' => $message
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('SMS Service Error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendWelcomeMessage($phoneNumber, $userName)
    {
        try {
            $message = "Welcome to MedicoThink, {$userName}! Your account has been successfully created. Start your AI-powered medical consultations now.";
            
            $this->twilio->messages->create($phoneNumber, [
                'from' => config('services.twilio.from'),
                'body' => $message
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Welcome SMS Error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendSubscriptionNotification($phoneNumber, $planName, $expiryDate)
    {
        try {
            $message = "Your MedicoThink {$planName} subscription will expire on {$expiryDate}. Renew now to continue enjoying premium features.";
            
            $this->twilio->messages->create($phoneNumber, [
                'from' => config('services.twilio.from'),
                'body' => $message
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Subscription SMS Error: ' . $e->getMessage());
            return false;
        }
    }
}