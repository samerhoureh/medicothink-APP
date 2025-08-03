<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;

class SubscriptionService
{
    protected $payClickService;

    public function __construct(PayClickService $payClickService)
    {
        $this->payClickService = $payClickService;
    }

    public function createSubscription(User $user, int $planId, string $paymentMethod = 'payclick')
    {
        $plan = SubscriptionPlan::findOrFail($planId);

        // Create subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'starts_at' => now(),
            'ends_at' => $this->calculateEndDate($plan->duration),
            'auto_renewal' => true,
        ]);

        // Create payment
        if ($paymentMethod === 'payclick') {
            $paymentResult = $this->payClickService->createPayment([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'description' => "MedicoThink {$plan->display_name_en} Subscription",
                'metadata' => [
                    'subscription_id' => $subscription->id,
                    'plan_id' => $plan->id,
                ],
            ]);

            if ($paymentResult['success']) {
                return [
                    'success' => true,
                    'subscription' => $subscription,
                    'payment_url' => $paymentResult['checkout_url'],
                ];
            } else {
                $subscription->delete();
                return [
                    'success' => false,
                    'error' => $paymentResult['error'],
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'Unsupported payment method',
        ];
    }

    public function cancelSubscription(Subscription $subscription)
    {
        $subscription->update([
            'status' => 'cancelled',
            'auto_renewal' => false,
        ]);

        return $subscription;
    }

    public function renewSubscription(Subscription $subscription)
    {
        if (!$subscription->auto_renewal) {
            return false;
        }

        $plan = $subscription->plan;

        // Create new subscription period
        $subscription->update([
            'starts_at' => $subscription->ends_at,
            'ends_at' => $this->calculateEndDate($plan->duration, $subscription->ends_at),
            'status' => 'active',
        ]);

        // Reset usage counters
        $subscription->resetUsage();

        return $subscription;
    }

    public function checkExpiredSubscriptions()
    {
        $expiredSubscriptions = Subscription::where('status', 'active')
            ->where('ends_at', '<', now())
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            if ($subscription->auto_renewal) {
                // Try to renew
                $this->renewSubscription($subscription);
            } else {
                // Mark as expired
                $subscription->update(['status' => 'expired']);
            }
        }

        return $expiredSubscriptions->count();
    }

    public function getUserSubscriptionStatus(User $user)
    {
        $activeSubscription = $user->activeSubscription;

        if (!$activeSubscription) {
            return [
                'has_subscription' => false,
                'subscription' => null,
                'plan' => null,
                'usage' => null,
            ];
        }

        $plan = $activeSubscription->plan;

        return [
            'has_subscription' => true,
            'subscription' => $activeSubscription,
            'plan' => $plan,
            'usage' => [
                'tokens' => [
                    'used' => $activeSubscription->tokens_used,
                    'limit' => $plan->tokens_limit,
                    'remaining' => $activeSubscription->getRemainingTokens(),
                ],
                'images' => [
                    'used' => $activeSubscription->images_used,
                    'limit' => $plan->images_limit,
                    'remaining' => $activeSubscription->getRemainingImages(),
                ],
                'videos' => [
                    'used' => $activeSubscription->videos_used,
                    'limit' => $plan->videos_limit,
                    'remaining' => $activeSubscription->getRemainingVideos(),
                ],
                'conversations' => [
                    'used' => $activeSubscription->conversations_used,
                    'limit' => $plan->conversations_limit,
                    'remaining' => max(0, $plan->conversations_limit - $activeSubscription->conversations_used),
                ],
            ],
            'expires_at' => $activeSubscription->ends_at,
            'days_until_expiry' => $activeSubscription->daysUntilExpiry(),
        ];
    }

    protected function calculateEndDate(string $duration, Carbon $startDate = null)
    {
        $startDate = $startDate ?: now();

        switch ($duration) {
            case 'monthly':
                return $startDate->copy()->addMonth();
            case 'yearly':
                return $startDate->copy()->addYear();
            case 'weekly':
                return $startDate->copy()->addWeek();
            default:
                return $startDate->copy()->addMonth();
        }
    }
}