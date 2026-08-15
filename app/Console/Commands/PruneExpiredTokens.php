<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneExpiredTokens extends Command
{
    protected $signature = 'fleet:prune-expired-tokens';

    protected $description = 'Delete Sanctum tokens past their expiration window.';

    public function handle(): void
    {
        $expirationMinutes = config('sanctum.expiration');

        if (! $expirationMinutes) {
            $this->info('No expiration configured — nothing to prune.');
            return;
        }

        $cutoff = now()->subMinutes($expirationMinutes);

        $deleted = DB::table('personal_access_tokens')
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Pruned {$deleted} expired token(s).");
    }
}