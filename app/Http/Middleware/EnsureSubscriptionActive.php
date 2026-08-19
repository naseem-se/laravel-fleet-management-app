<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->company_id !== null) {
            if ($user->company->status === 'suspended') {
                return response()->json([
                    'code' => 'account_suspended',
                    'message' => 'Your company account has been suspended. Please contact your administrator or our support team for assistance.',
                ], 403);
            }

            $subscription = $user->company->activeSubscription()->with('plan')->first();

            if (! $subscription || $subscription->ends_at->isPast()) {
                return response()->json([
                    'code' => 'subscription_expired',
                    'message' => 'Your subscription has expired.',
                    'detail' => 'To continue using Fleet Management, please renew your plan. Contact your account administrator or our support team to restore access.',
                    'expired_at' => $subscription?->ends_at?->toIso8601String(),
                ], 402);
            }
        }

        return $next($request);
    }
}