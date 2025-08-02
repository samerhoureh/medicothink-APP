<?php

namespace App\Services;

use Twilio\Rest\Client;

class SmsService
{
    protected $twilio;
    protected $from;

    public function __construct()
    {
        $this->twilio = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
        $this->from = config('services.twilio.from');
    }

    public function sendOtp($phoneNumber, $code)
    {
        $message = "Your MedicoThink verification code is: {$code}. This code will expire in 5 minutes.";

        try {
            $this->twilio->messages->create($phoneNumber, [
                'from' => $this->from,
                'body' => $message
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('SMS sending failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function sendNotification($phoneNumber, $message)
    {
        try {
            $this->twilio->messages->create($phoneNumber, [
                'from' => $this->from,
                'body' => $message
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('SMS notification failed: ' . $e->getMessage());
            throw $e;
        }
    }
}