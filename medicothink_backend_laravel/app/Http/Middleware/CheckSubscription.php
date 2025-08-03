<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        // Check if user has active subscription
        $subscription = $user->activeSubscription;
        
        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Active subscription required',
                'error_code' => 'SUBSCRIPTION_REQUIRED',
                'data' => [
                    'has_subscription' => false,
                    'subscription_status' => null,
                ]
            ], 403);
        }

        // Check subscription limits based on endpoint
        $endpoint = $request->path();
        $limitCheck = $this->checkUsageLimits($user, $subscription, $endpoint);
        
        if (!$limitCheck['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $limitCheck['message'],
                'error_code' => $limitCheck['error_code'],
                'data' => [
                    'usage' => $limitCheck['usage'],
                    'limits' => $limitCheck['limits'],
                ]
            ], 403);
        }

        return $next($request);
    }

    protected function checkUsageLimits($user, $subscription, string $endpoint): array
    {
        $plan = $subscription->plan;
        
        // Define endpoint to resource mapping
        $resourceMap = [
            'api/ai/chat' => 'tokens',
            'api/ai/generate-flashcards' => 'tokens',
            'api/ai/analyze-image' => 'images',
            'api/ai/generate-image' => 'images',
            'api/ai/generate-video' => 'videos',
        ];

        $resource = null;
        foreach ($resourceMap as $pattern => $res) {
            if (str_contains($endpoint, $pattern)) {
                $resource = $res;
                break;
            }
        }

        if (!$resource) {
            // No limits for this endpoint
            return ['allowed' => true];
        }

        $usedField = $resource . '_used';
        $limitField = $resource . '_limit';
        
        $used = $subscription->$usedField;
        $limit = $plan->$limitField;

        // -1 means unlimited
        if ($limit === -1) {
            return ['allowed' => true];
        }

        $allowed = $used < $limit;
        $remaining = max(0, $limit - $used);

        $messages = [
            'tokens' => [
                'en' => 'Token limit exceeded. Please upgrade your subscription.',
                'ar' => 'تم تجاوز حد الرموز المميزة. يرجى ترقية اشتراكك.'
            ],
            'images' => [
                'en' => 'Image processing limit exceeded. Please upgrade your subscription.',
                'ar' => 'تم تجاوز حد معالجة الصور. يرجى ترقية اشتراكك.'
            ],
            'videos' => [
                'en' => 'Video generation limit exceeded. Please upgrade your subscription.',
                'ar' => 'تم تجاوز حد توليد الفيديو. يرجى ترقية اشتراكك.'
            ],
        ];

        return [
            'allowed' => $allowed,
            'message' => $allowed ? 'Usage within limits' : $messages[$resource]['en'],
            'error_code' => $allowed ? null : strtoupper($resource) . '_LIMIT_EXCEEDED',
            'usage' => [
                'used' => $used,
                'remaining' => $remaining,
                'total' => $limit,
                'resource' => $resource,
            ],
            'limits' => [
                'tokens' => [
                    'used' => $subscription->tokens_used,
                    'limit' => $plan->tokens_limit,
                    'remaining' => $plan->tokens_limit === -1 ? -1 : max(0, $plan->tokens_limit - $subscription->tokens_used),
                ],
                'images' => [
                    'used' => $subscription->images_used,
                    'limit' => $plan->images_limit,
                    'remaining' => $plan->images_limit === -1 ? -1 : max(0, $plan->images_limit - $subscription->images_used),
                ],
                'videos' => [
                    'used' => $subscription->videos_used,
                    'limit' => $plan->videos_limit,
                    'remaining' => $plan->videos_limit === -1 ? -1 : max(0, $plan->videos_limit - $subscription->videos_used),
                ],
            ]
        ];
    }
}