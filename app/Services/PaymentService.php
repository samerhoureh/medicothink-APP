<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\Subscription;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

class PaymentService
{
    protected $stripeKey;
    protected $paypalContext;

    public function __construct()
    {
        $this->stripeKey = config('services.stripe.secret');
        
        // PayPal setup
        $this->paypalContext = new ApiContext(
            new OAuthTokenCredential(
                config('services.paypal.client_id'),
                config('services.paypal.secret')
            )
        );
        $this->paypalContext->setConfig(config('services.paypal.settings'));
    }

    public function processStripePayment($amount, $currency, $paymentMethodId, $userId)
    {
        try {
            Stripe::setApiKey($this->stripeKey);

            $paymentIntent = PaymentIntent::create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => $currency,
                'payment_method' => $paymentMethodId,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'metadata' => [
                    'user_id' => $userId,
                ],
            ]);

            $transaction = PaymentTransaction::create([
                'user_id' => $userId,
                'transaction_id' => $paymentIntent->id,
                'payment_gateway' => 'stripe',
                'amount' => $amount,
                'currency' => $currency,
                'status' => $this->mapStripeStatus($paymentIntent->status),
                'gateway_response' => $paymentIntent->toArray(),
                'processed_at' => now(),
            ]);

            return [
                'success' => $paymentIntent->status === 'succeeded',
                'transaction' => $transaction,
                'payment_intent' => $paymentIntent,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function processPayPalPayment($paymentId, $payerId, $userId)
    {
        try {
            $payment = Payment::get($paymentId, $this->paypalContext);
            
            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);

            $result = $payment->execute($execution, $this->paypalContext);

            $transaction = PaymentTransaction::create([
                'user_id' => $userId,
                'transaction_id' => $paymentId,
                'payment_gateway' => 'paypal',
                'amount' => $result->transactions[0]->amount->total,
                'currency' => $result->transactions[0]->amount->currency,
                'status' => $this->mapPayPalStatus($result->state),
                'gateway_response' => $result->toArray(),
                'processed_at' => now(),
            ]);

            return [
                'success' => $result->state === 'approved',
                'transaction' => $transaction,
                'payment' => $result,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function createSubscription($userId, $planId, $paymentResult)
    {
        if (!$paymentResult['success']) {
            return false;
        }

        $planPrices = [
            'basic' => 9.99,
            'premium' => 19.99,
            'professional' => 49.99,
        ];

        $planNames = [
            'basic' => 'Basic Plan',
            'premium' => 'Premium Plan',
            'professional' => 'Professional Plan',
        ];

        $subscription = Subscription::create([
            'user_id' => $userId,
            'plan_name' => $planNames[$planId],
            'plan_type' => $planId,
            'price' => $planPrices[$planId],
            'currency' => 'USD',
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'payment_method' => $paymentResult['transaction']->payment_gateway,
            'transaction_id' => $paymentResult['transaction']->transaction_id,
        ]);

        // Update transaction with subscription ID
        $paymentResult['transaction']->update([
            'subscription_id' => $subscription->id,
        ]);

        return $subscription;
    }

    private function mapStripeStatus($status)
    {
        switch ($status) {
            case 'succeeded':
                return PaymentTransaction::STATUS_COMPLETED;
            case 'processing':
                return PaymentTransaction::STATUS_PENDING;
            case 'requires_payment_method':
            case 'requires_confirmation':
                return PaymentTransaction::STATUS_PENDING;
            default:
                return PaymentTransaction::STATUS_FAILED;
        }
    }

    private function mapPayPalStatus($status)
    {
        switch ($status) {
            case 'approved':
                return PaymentTransaction::STATUS_COMPLETED;
            case 'created':
                return PaymentTransaction::STATUS_PENDING;
            default:
                return PaymentTransaction::STATUS_FAILED;
        }
    }
}