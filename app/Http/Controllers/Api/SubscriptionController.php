<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function status()
    {
        $user = auth()->user();
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'subscription' => [
                'id' => $subscription->id,
                'plan_name' => $subscription->plan_name,
                'plan_type' => $subscription->plan_type,
                'expires_at' => $subscription->expires_at,
                'started_at' => $subscription->starts_at,
                'is_active' => $subscription->is_active,
                'days_remaining' => $subscription->days_remaining,
                'price' => $subscription->price,
                'currency' => $subscription->currency,
                'auto_renew' => $subscription->auto_renew,
                'is_expired' => $subscription->is_expired,
                'is_expiring_soon' => $subscription->is_expiring_soon,
                'status_text' => $subscription->status_text,
            ]
        ]);
    }

    public function plans()
    {
        $plans = [
            [
                'id' => 'basic',
                'name' => 'Basic Plan',
                'price' => 9.99,
                'currency' => 'USD',
                'duration' => 'monthly',
                'features' => [
                    '50 AI conversations per month',
                    '10 image analyses per month',
                    'Basic medical insights',
                    'Email support'
                ]
            ],
            [
                'id' => 'premium',
                'name' => 'Premium Plan',
                'price' => 19.99,
                'currency' => 'USD',
                'duration' => 'monthly',
                'features' => [
                    'Unlimited AI conversations',
                    'Unlimited image analyses',
                    'Advanced medical insights',
                    'Conversation summaries',
                    'Priority support',
                    'Export conversations'
                ]
            ],
            [
                'id' => 'professional',
                'name' => 'Professional Plan',
                'price' => 49.99,
                'currency' => 'USD',
                'duration' => 'monthly',
                'features' => [
                    'Everything in Premium',
                    'API access',
                    'Custom integrations',
                    'Dedicated support',
                    'Advanced analytics'
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'plans' => $plans
        ]);
    }

    public function subscribe(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'plan_id' => 'required|string|in:basic,premium,professional',
            'payment_method' => 'required|string',
            'transaction_id' => 'required|string',
        ]);

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

        // Create or update subscription
        $subscription = Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_name' => $planNames[$request->plan_id],
                'plan_type' => $request->plan_id,
                'price' => $planPrices[$request->plan_id],
                'currency' => 'USD',
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
                'is_active' => true,
                'auto_renew' => $request->auto_renew ?? false,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Subscription activated successfully',
            'subscription' => $subscription
        ]);
    }
}