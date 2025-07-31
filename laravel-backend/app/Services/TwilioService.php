<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;
    protected $from;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
        $this->from = config('services.twilio.from');
    }

    public function sendOtp(string $phoneNumber, string $code)
    {
        $message = "Your MedicoThink verification code is: {$code}. This code will expire in 5 minutes.";

        return $this->client->messages->create($phoneNumber, [
            'from' => $this->from,
            'body' => $message
        ]);
    }

    public function sendSms(string $phoneNumber, string $message)
    {
        return $this->client->messages->create($phoneNumber, [
            'from' => $this->from,
            'body' => $message
        ]);
    }
}