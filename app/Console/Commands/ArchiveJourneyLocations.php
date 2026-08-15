<?php

namespace App\Console\Commands;

use App\Models\Journey;
use App\Models\JourneyLocation;
use App\Models\JourneyLocationSummary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArchiveJourneyLocations extends Command
{
    protected $signature = 'fleet:archive-journey-locations
                            {--retention-days=14 : Journeys completed longer ago than this get their raw GPS pings archived}
                            {--chunk=200 : How many journeys to process per batch}';

    protected $description = 'Roll up raw GPS pings for old completed journeys into a summary row, then purge the raw pings — keeps journey_locations from growing unboundedly.';

    public function handle(): void
    {
        $retentionDays = (int) $this->option('retention-days');
        $chunkSize = (int) $this->option('chunk');
        $cutoff = now()->subDays($retentionDays);

        $totalArchived = 0;
        $totalPointsDeleted = 0;

        // withoutGlobalScopes: this runs outside an authenticated request
        // (cron), so there's no tenant context for CompanyScope to filter
        // by — deliberately processing across every company here.
        Journey::withoutGlobalScopes()
            ->where('status', 'completed')
            ->where('end_time', '<', $cutoff)
            ->whereDoesntHave('locationSummary') // skip journeys already archived
            ->whereHas('locations') // skip journeys with no raw pings to begin with
            ->chunkById($chunkSize, function ($journeys) use (&$totalArchived, &$totalPointsDeleted) {
                foreach ($journeys as $journey) {
                    if (! $journey instanceof Journey) {
                        Log::warning('Skipping non-Journey model while archiving journey locations.', [
                            'type' => is_object($journey) ? get_class($journey) : gettype($journey),
                        ]);

                        continue;
                    }

                    $result = $this->archiveJourney($journey);
                    if ($result) {
                        $totalArchived++;
                        $totalPointsDeleted += $result;
                    }
                }
            });

        $this->info("Archived {$totalArchived} journey(s), purged {$totalPointsDeleted} raw location point(s).");
    }

    protected function archiveJourney(Journey $journey): ?int
    {
        try {
            return DB::transaction(function () use ($journey) {
                // Aggregate directly in SQL rather than pulling every row into
                // PHP memory — this is exactly the kind of query that matters
                // once a journey has thousands of ping rows.
                $stats = JourneyLocation::where('journey_id', $journey->id)
                    ->selectRaw('
                        COUNT(*) as point_count,
                        MIN(lat) as min_lat, MAX(lat) as max_lat,
                        MIN(lng) as min_lng, MAX(lng) as max_lng,
                        MAX(speed_kmh) as max_speed_kmh,
                        AVG(speed_kmh) as avg_speed_kmh,
                        MIN(recorded_at) as first_recorded_at,
                        MAX(recorded_at) as last_recorded_at
                    ')
                    ->first();

                if (! $stats || $stats->point_count === 0) {
                    return null;
                }

                JourneyLocationSummary::create([
                    'journey_id' => $journey->id,
                    'point_count' => $stats->point_count,
                    'min_lat' => $stats->min_lat,
                    'max_lat' => $stats->max_lat,
                    'min_lng' => $stats->min_lng,
                    'max_lng' => $stats->max_lng,
                    'max_speed_kmh' => $stats->max_speed_kmh,
                    'avg_speed_kmh' => $stats->avg_speed_kmh,
                    'first_recorded_at' => $stats->first_recorded_at,
                    'last_recorded_at' => $stats->last_recorded_at,
                    'archived_at' => now(),
                ]);

                $deleted = JourneyLocation::where('journey_id', $journey->id)->delete();

                return $deleted;
            });
        } catch (\Throwable $e) {
            // One bad journey shouldn't abort the whole run — log and move on,
            // it'll be retried on the next scheduled execution.
            Log::error('Failed to archive journey locations', [
                'journey_id' => $journey->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}