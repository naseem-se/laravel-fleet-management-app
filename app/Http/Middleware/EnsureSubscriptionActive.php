<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    /**
     * Blocks company-tenant users once their subscription has lapsed.
     * Super admins (company_id === null) always pass through — they're
     * the ones managing subscriptions, not bound by them.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->company_id !== null) {
            if ($user->company->status === 'suspended') {
                return response()->json([
                    'message' => 'Your company account has been suspended. Contact support.',
                ], 403);
            }

            if (! $user->company->isSubscriptionActive()) {
                return response()->json([
                    'message' => 'Your subscription has expired. Please renew to continue.',
                ], 402);
            }
        }

        return $next($request);
    }
}