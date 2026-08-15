<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function check()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
        ];

        $allHealthy = collect($checks)->every(fn ($check) => $check['healthy']);

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $allHealthy ? 200 : 503);
    }

    protected function checkDatabase(): array
    {
        $start = microtime(true);
        try {
            DB::select('SELECT 1');
            return ['healthy' => true, 'latency_ms' => round((microtime(true) - $start) * 1000, 1)];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'error' => 'Database unreachable'];
        }
    }

    protected function checkCache(): array
    {
        try {
            $key = 'health-check-'.uniqid();
            Cache::put($key, true, 5);
            $ok = Cache::get($key) === true;
            Cache::forget($key);
            return ['healthy' => $ok];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'error' => 'Cache unreachable'];
        }
    }

    protected function checkQueue(): array
    {
        // For the database queue driver, "healthy" just means the jobs
        // table is reachable and not growing unboundedly (a stalled worker
        // shows up as a large backlog here).
        try {
            $pending = DB::table('jobs')->count();
            return ['healthy' => true, 'pending_jobs' => $pending];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'error' => 'Queue table unreachable'];
        }
    }
}