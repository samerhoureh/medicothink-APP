<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayClickService
{
    protected $apiKey;
    protected $secretKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.payclick.api_key');
        $this->secretKey = config('services.payclick.secret_key');
        $this->baseUrl = config('services.payclick.base_url', 'https://api.payclick.com');
    }

    public function createPayment(array $data)
    {
        try {
            $payload = [
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'USD',
                'description' => $data['description'] ?? 'MedicoThink Subscription',
                'return_url' => $data['return_url'] ?? config('app.url') . '/payment/success',
                'cancel_url' => $data['cancel_url'] ?? config('app.url') . '/payment/cancel',
                'webhook_url' => config('services.payclick.webhook_url'),
                'metadata' => $data['metadata'] ?? [],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/payments', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Create payment record
                $payment = Payment::create([
                    'user_id' => $data['user_id'],
                    'subscription_id' => $data['subscription_id'] ?? null,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'] ?? 'USD',
                    'payment_method' => 'payclick',
                    'payment_id' => $responseData['id'],
                    'status' => 'pending',
                    'metadata' => $responseData,
                ]);

                return [
                    'success' => true,
                    'payment_id' => $payment->id,
                    'checkout_url' => $responseData['checkout_url'],
                    'payment_data' => $responseData,
                ];
            }

            throw new \Exception('PayClick API request failed: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('PayClick Payment Creation Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function handleWebhook(array $data)
    {
        try {
            // Verify webhook signature
            if (!$this->verifyWebhookSignature($data)) {
                throw new \Exception('Invalid webhook signature');
            }

            $paymentId = $data['payment_id'];
            $status = $data['status'];

            $payment = Payment::where('payment_id', $paymentId)->first();
            
            if (!$payment) {
                throw new \Exception('Payment not found');
            }

            // Update payment status
            $payment->update([
                'status' => $this->mapPayClickStatus($status),
                'metadata' => array_merge($payment->metadata ?? [], $data),
            ]);

            // If payment is completed, activate subscription
            if ($status === 'completed' && $payment->subscription_id) {
                $this->activateSubscription($payment->subscription_id);
            }

            return [
                'success' => true,
                'payment' => $payment,
            ];

        } catch (\Exception $e) {
            Log::error('PayClick Webhook Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function verifyWebhookSignature(array $data)
    {
        // Implement webhook signature verification
        // This depends on PayClick's webhook signature method
        return true; // Placeholder
    }

    protected function mapPayClickStatus(string $status)
    {
        $statusMap = [
            'pending' => 'pending',
            'completed' => 'completed',
            'failed' => 'failed',
            'cancelled' => 'failed',
            'refunded' => 'refunded',
        ];

        return $statusMap[$status] ?? 'pending';
    }

    protected function activateSubscription(int $subscriptionId)
    {
        $subscription = Subscription::find($subscriptionId);
        
        if ($subscription) {
            $subscription->update([
                'status' => 'active',
                'starts_at' => now(),
            ]);
        }
    }

    public function refundPayment(string $paymentId, float $amount = null)
    {
        try {
            $payload = [];
            if ($amount) {
                $payload['amount'] = $amount;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . "/payments/{$paymentId}/refund", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            throw new \Exception('PayClick refund request failed: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('PayClick Refund Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}