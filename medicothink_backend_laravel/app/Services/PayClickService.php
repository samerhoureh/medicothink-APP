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

            // Handle different payment statuses
            switch ($status) {
                case 'completed':
                    if ($payment->subscription_id) {
                        $this->activateSubscription($payment->subscription_id);
                    }
                    $this->sendPaymentConfirmation($payment);
                    break;
                    
                case 'failed':
                case 'cancelled':
                    if ($payment->subscription_id) {
                        $this->cancelSubscription($payment->subscription_id);
                    }
                    $this->sendPaymentFailureNotification($payment);
                    break;
                    
                case 'refunded':
                    if ($payment->subscription_id) {
                        $this->handleRefund($payment->subscription_id);
                    }
                    $this->sendRefundNotification($payment);
                    break;
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
        // PayClick webhook signature verification
        if (!isset($data['signature'])) {
            return false;
        }

        // Remove signature from data for verification
        $signature = $data['signature'];
        unset($data['signature']);

        // Sort data by keys
        ksort($data);

        // Create signature string
        $signatureString = '';
        foreach ($data as $key => $value) {
            $signatureString .= $key . '=' . $value . '&';
        }
        $signatureString = rtrim($signatureString, '&');

        // Generate expected signature
        $expectedSignature = hash_hmac('sha256', $signatureString, $this->secretKey);

        return hash_equals($expectedSignature, $signature);
    }

    protected function mapPayClickStatus(string $status)
    {
        $statusMap = [
            'pending' => 'pending',
            'completed' => 'completed',
            'success' => 'completed',
            'failed' => 'failed',
            'cancelled' => 'failed',
            'expired' => 'failed',
            'refunded' => 'refunded',
            'partially_refunded' => 'refunded',
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
            
            Log::info("Subscription {$subscriptionId} activated successfully");
        }
    }

    protected function cancelSubscription(int $subscriptionId)
    {
        $subscription = Subscription::find($subscriptionId);
        
        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
            ]);
            
            Log::info("Subscription {$subscriptionId} cancelled due to payment failure");
        }
    }

    protected function handleRefund(int $subscriptionId)
    {
        $subscription = Subscription::find($subscriptionId);
        
        if ($subscription) {
            // Calculate refund period
            $daysUsed = $subscription->starts_at->diffInDays(now());
            $totalDays = $subscription->starts_at->diffInDays($subscription->ends_at);
            
            if ($daysUsed < $totalDays) {
                // Partial refund - adjust end date
                $subscription->update([
                    'ends_at' => now(),
                    'status' => 'cancelled',
                ]);
            }
            
            Log::info("Refund processed for subscription {$subscriptionId}");
        }
    }

    protected function sendPaymentConfirmation(Payment $payment)
    {
        // Send confirmation email/SMS to user
        Log::info("Payment confirmation sent for payment {$payment->id}");
    }

    protected function sendPaymentFailureNotification(Payment $payment)
    {
        // Send failure notification to user
        Log::info("Payment failure notification sent for payment {$payment->id}");
    }

    protected function sendRefundNotification(Payment $payment)
    {
        // Send refund notification to user
        Log::info("Refund notification sent for payment {$payment->id}");
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
            ])->timeout(30)->post($this->baseUrl . "/payments/{$paymentId}/refund", $payload);

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

    public function testConnection(): array
    {
        try {
            if (!$this->apiKey) {
                return [
                    'success' => false,
                    'message' => 'PayClick API key not configured'
                ];
            }

            // Test API connection
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(10)->get($this->baseUrl . '/test');

            return [
                'success' => $response->successful(),
                'message' => $response->successful() ? 'PayClick connection successful' : 'PayClick connection failed',
                'status_code' => $response->status()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'PayClick connection error: ' . $e->getMessage()
            ];
        }
    }
}