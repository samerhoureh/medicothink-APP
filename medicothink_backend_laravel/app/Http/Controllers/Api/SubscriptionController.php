<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use App\Http\Resources\SubscriptionPlanResource;
use App\Http\Resources\SubscriptionResource;
use App\Http\Requests\SubscribeRequest;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->middleware('auth:sanctum');
    }

    public function status(Request $request)
    {
        $user = $request->user();
        $status = $this->subscriptionService->getUserSubscriptionStatus($user);

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    public function plans(Request $request)
    {
        $locale = $request->header('Accept-Language', 'en');
        
        $plans = SubscriptionPlan::active()
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'plans' => SubscriptionPlanResource::collection($plans),
            ],
        ]);
    }

    public function subscribe(SubscribeRequest $request)
    {
        try {
            $user = $request->user();
            
            $result = $this->subscriptionService->createSubscription(
                $user,
                $request->plan_id,
                $request->payment_method ?? 'payclick'
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subscription created successfully',
                    'data' => [
                        'subscription' => new SubscriptionResource($result['subscription']),
                        'payment_url' => $result['payment_url'],
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription creation failed',
                    'error' => $result['error'],
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancel(Request $request)
    {
        try {
            $user = $request->user();
            $subscription = $user->activeSubscription;

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found',
                ], 404);
            }

            $this->subscriptionService->cancelSubscription($subscription);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully',
                'data' => [
                    'subscription' => new SubscriptionResource($subscription->fresh()),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription cancellation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}