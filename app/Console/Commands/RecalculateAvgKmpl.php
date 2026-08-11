<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use Illuminate\Console\Command;

class RecalculateAvgKmpl extends Command
{
    protected $signature = 'fleet:recalculate-avg-kmpl';

    protected $description = 'Refresh the cached average KMPL for every vehicle based on the last 90 days.';

    public function handle(): void
    {
        $vehicles = Vehicle::withoutGlobalScopes()->get();

        foreach ($vehicles as $vehicle) {
            $totalDistance = $vehicle->journeys()
                ->where('status', 'completed')
                ->where('start_time', '>=', now()->subDays(90))
                ->sum('total_distance');

            $totalFuel = $vehicle->fuelEntries()
                ->where('entry_time', '>=', now()->subDays(90))
                ->sum('quantity_litres');

            $avgKmpl = $totalFuel > 0 ? round($totalDistance / $totalFuel, 2) : null;

            $vehicle->update(['avg_kmpl_cached' => $avgKmpl]);
        }

        $this->info("Recalculated average KMPL for {$vehicles->count()} vehicle(s).");
    }
}