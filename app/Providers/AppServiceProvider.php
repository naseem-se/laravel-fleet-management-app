<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('login', function ($request) {
            return [
                Limit::perMinute(5)->by('login-ip:'.$request->ip()),
                Limit::perMinute(5)->by('login-email:'.strtolower((string) $request->input('email'))),
            ];
        });

        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('journey-ping', function ($request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('reports', function ($request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('uploads', function ($request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        if (env('DB_QUERY_LOG', false)) {
            \Illuminate\Support\Facades\DB::listen(function ($query) {
                if ($query->time > 100) { // only log genuinely slow queries (>100ms), not every SELECT
                    \Illuminate\Support\Facades\Log::channel('slow_queries')->warning('Slow query', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time_ms' => $query->time,
                    ]);
                }
            });
        }

        \Illuminate\Support\Facades\Notification::extend('fcm', function ($app) {
            return new \App\Notifications\Channels\FcmChannel($app->make(\Kreait\Firebase\Contract\Messaging::class));
        });



    }
}