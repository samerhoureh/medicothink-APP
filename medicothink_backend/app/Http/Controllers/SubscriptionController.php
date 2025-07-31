<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getStatus()
    {
        return response()->json([
            'success' => true,
            'id' => 1,
            'plan_name' => 'Premium Plan',
            'plan_type' => 'premium',
            'expires_at' => now()->addDays(30),
            'is_active' => true,
            'days_remaining' => 30,
            'price' => 19.99,
            'currency' => 'USD',
            'auto_renew' => true,
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
            ]
        ];

        return response()->json([
            'success' => true,
            'plans' => $plans
        ]);
    }

    public function subscribe(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Subscription created successfully'
        ]);
    }

    public function cancel()
    {
        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully'
        ]);
    }
}