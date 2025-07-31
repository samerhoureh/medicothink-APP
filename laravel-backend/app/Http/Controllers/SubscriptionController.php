<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Http\Resources\SubscriptionResource;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getStatus()
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
            'id' => $subscription->id,
            'plan_name' => $subscription->plan_name,
            'plan_type' => $subscription->plan_type,
            'expires_at' => $subscription->expires_at,
            'started_at' => $subscription->starts_at,
            'is_active' => $subscription->is_active && !$subscription->is_expired,
            'days_remaining' => $subscription->days_remaining,
            'price' => $subscription->price,
            'currency' => $subscription->currency,
            'auto_renew' => $subscription->auto_renew,
        ]);
    }

    public function getPlans()
    {
        $plans = [
            [
                'id' => 'basic',
                'name' => 'Basic Plan',
                'price' => 9.99,
                'currency' => 'USD',
                'duration' => 30,
                'features' => [
                    '50 AI conversations per month',
                    '10 image analyses per month',
                    'Basic medical advice',
                    'Email support'
                ]
            ],
            [
                'id' => 'premium',
                'name' => 'Premium Plan',
                'price' => 19.99,
                'currency' => 'USD',
                'duration' => 30,
                'features' => [
                    'Unlimited AI conversations',
                    'Unlimited image analyses',
                    'Advanced medical insights',
                    'Conversation summaries',
                    'Priority support'
                ]
            ],
            [
                'id' => 'professional',
                'name' => 'Professional Plan',
                'price' => 39.99,
                'currency' => 'USD',
                'duration' => 30,
                'features' => [
                    'Everything in Premium',
                    'Multi-language support',
                    'API access',
                    'Custom integrations',
                    '24/7 phone support'
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
        $validator = \Validator::make($request->all(), [
            'plan_id' => 'required|string|in:basic,premium,professional',
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        // Plan details
        $plans = [
            'basic' => ['name' => 'Basic Plan', 'price' => 9.99],
            'premium' => ['name' => 'Premium Plan', 'price' => 19.99],
            'professional' => ['name' => 'Professional Plan', 'price' => 39.99],
        ];

        $planDetails = $plans[$request->plan_id];

        try {
            // Here you would integrate with Stripe or other payment processor
            // For now, we'll create a subscription directly

            // Cancel existing subscription
            if ($user->subscription) {
                $user->subscription->cancel();
            }

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_name' => $planDetails['name'],
                'plan_type' => $request->plan_id,
                'price' => $planDetails['price'],
                'currency' => 'USD',
                'starts_at' => now(),
                'expires_at' => now()->addDays(30),
                'is_active' => true,
                'auto_renew' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully',
                'subscription' => new SubscriptionResource($subscription)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel()
    {
        $user = auth()->user();
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found'
            ], 404);
        }

        $subscription->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully'
        ]);
    }
}