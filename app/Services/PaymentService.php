<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Subscription as StripeSubscription;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private $paypalApiContext;

    public function __construct()
    {
        // Initialize Stripe
        Stripe::setApiKey(config('services.stripe.secret'));

        // Initialize PayPal
        $this->paypalApiContext = new ApiContext(
            new OAuthTokenCredential(
                config('services.paypal.client_id'),
                config('services.paypal.secret')
            )
        );
        $this->paypalApiContext->setConfig([
            'mode' => config('services.paypal.mode'),
        ]);
    }

    public function createStripePayment($user, $amount, $currency = 'usd', $subscriptionId = null)
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => $currency,
                'customer' => $user->stripe_customer_id,
                'metadata' => [
                    'user_id' => $user->id,
                    'subscription_id' => $subscriptionId
                ]
            ]);

            // Create transaction record
            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'subscription_id' => $subscriptionId,
                'amount' => $amount,
                'currency' => $currency,
                'payment_method' => 'stripe',
                'payment_status' => 'pending',
                'stripe_payment_intent_id' => $paymentIntent->id,
                'metadata' => [
                    'client_secret' => $paymentIntent->client_secret
                ]
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'transaction_id' => $transaction->id
            ];

        } catch (\Exception $e) {
            Log::error('Stripe Payment Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function confirmStripePayment($paymentIntentId)
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            
            $transaction = PaymentTransaction::where('stripe_payment_intent_id', $paymentIntentId)->first();
            
            if ($transaction) {
                $transaction->update([
                    'payment_status' => $paymentIntent->status === 'succeeded' ? 'completed' : 'failed',
                    'transaction_id' => $paymentIntent->id
                ]);

                if ($paymentIntent->status === 'succeeded' && $transaction->subscription_id) {
                    $this->activateSubscription($transaction->subscription_id);
                }
            }

            return [
                'success' => $paymentIntent->status === 'succeeded',
                'status' => $paymentIntent->status
            ];

        } catch (\Exception $e) {
            Log::error('Stripe Payment Confirmation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function createPayPalPayment($user, $amount, $currency = 'USD', $subscriptionId = null)
    {
        try {
            // PayPal payment creation logic would go here
            // This is a simplified version
            
            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'subscription_id' => $subscriptionId,
                'amount' => $amount,
                'currency' => $currency,
                'payment_method' => 'paypal',
                'payment_status' => 'pending'
            ]);

            return [
                'success' => true,
                'transaction_id' => $transaction->id,
                'approval_url' => 'https://paypal.com/approval-url' // This would be the actual PayPal approval URL
            ];

        } catch (\Exception $e) {
            Log::error('PayPal Payment Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function createSubscription($user, $planName, $planType, $price, $currency = 'usd')
    {
        try {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_name' => $planName,
                'plan_type' => $planType,
                'price' => $price,
                'currency' => $currency,
                'status' => 'pending',
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'auto_renewal' => true,
                'features' => $this->getPlanFeatures($planType)
            ]);

            return $subscription;

        } catch (\Exception $e) {
            Log::error('Subscription Creation Error: ' . $e->getMessage());
            return null;
        }
    }

    private function activateSubscription($subscriptionId)
    {
        $subscription = Subscription::find($subscriptionId);
        if ($subscription) {
            $subscription->update([
                'status' => 'active',
                'starts_at' => now()
            ]);
        }
    }

    private function getPlanFeatures($planType)
    {
        $features = [
            'basic' => [
                'ai_conversations' => 50,
                'image_analysis' => 10,
                'conversation_history' => true,
                'priority_support' => false
            ],
            'premium' => [
                'ai_conversations' => 200,
                'image_analysis' => 50,
                'conversation_history' => true,
                'priority_support' => true,
                'advanced_features' => true
            ],
            'pro' => [
                'ai_conversations' => -1, // Unlimited
                'image_analysis' => -1, // Unlimited
                'conversation_history' => true,
                'priority_support' => true,
                'advanced_features' => true,
                'api_access' => true
            ]
        ];

        return $features[$planType] ?? $features['basic'];
    }

    public function getPaymentHistory($userId)
    {
        return PaymentTransaction::where('user_id', $userId)
            ->with('subscription')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}