<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckActiveSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        if (!$user->hasActiveSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'Your subscription has expired. Please renew to continue using the service.',
                'subscription_expired' => true
            ], 403);
        }

        return $next($request);
    }
}