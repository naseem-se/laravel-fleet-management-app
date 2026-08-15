<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Driver;
use App\Models\FuelEntry;
use App\Models\Journey;
use App\Models\JourneyLocation;
use App\Models\MaintenanceRecord;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Generates production-scale fake data so query plans can be profiled
 * against realistic volume, not the handful of test rows used during
 * feature development. Run with: php artisan db:seed --class=PerformanceTestSeeder
 *
 * Defaults aim for a "medium-large" single company — adjust the constants
 * to model whatever shape you actually expect (many small companies vs.
 * a few huge ones changes which indexes matter most).
 */
class PerformanceTestSeeder extends Seeder
{
    protected int $vehicleCount = 500;
    protected int $driverCount = 400;
    protected int $journeysPerVehicle = 300;   // ~500 * 300 = 150k journeys
    protected int $pingsPerJourney = 20;        // only for the most recent 5% — the rest get archived-style (none), matching real steady-state
    protected int $fuelEntriesPerVehicle = 150;
    protected int $maintenancePerVehicle = 10;

    public function run(): void
    {
        $this->command->info('Seeding performance-test company...');

        $plan = SubscriptionPlan::create([
            'name' => 'Enterprise', 'max_vehicles' => 1000, 'max_users' => 1000,
            'price' => 999, 'billing_cycle' => 'monthly', 'is_active' => true,
        ]);

        $company = Company::create([
            'name' => 'Perf Test Co', 'slug' => 'perf-test-'.Str::random(6), 'status' => 'active',
        ]);

        Subscription::create([
            'company_id' => $company->id, 'subscription_plan_id' => $plan->id,
            'status' => 'active', 'starts_at' => now()->subYear(), 'ends_at' => now()->addYear(),
        ]);

        $admin = User::create([
            'company_id' => $company->id, 'name' => 'Perf Admin',
            'email' => 'perf-admin-'.Str::random(6).'@test.com',
            'password' => Hash::make('password'), 'status' => 'active',
        ]);
        $admin->assignRole('company_admin');

        $this->command->info("Seeding {$this->driverCount} drivers...");
        $driverIds = $this->seedDrivers($company);

        $this->command->info("Seeding {$this->vehicleCount} vehicles...");
        $vehicleIds = $this->seedVehicles($company, $driverIds);

        $this->command->info('Seeding journeys, fuel entries, maintenance records (this is the slow part)...');
        $this->seedActivity($company, $vehicleIds, $driverIds);

        $this->command->info('Done. Company slug: '.$company->slug);
    }

    protected function seedDrivers(Company $company): array
    {
        $ids = [];
        $rows = [];

        for ($i = 0; $i < $this->driverCount; $i++) {
            $rows[] = [
                'company_id' => $company->id,
                'name' => 'Driver '.$i,
                'phone' => '0300'.str_pad((string) $i, 7, '0', STR_PAD_LEFT),
                'status' => 'active',
                'created_at' => now(), 'updated_at' => now(),
            ];

            // Batch insert every 500 rows — a single 400-row insert is fine,
            // but this pattern scales to seeding 10x more without change.
            if (count($rows) >= 500) {
                DB::table('drivers')->insert($rows);
                $rows = [];
            }
        }
        if ($rows) DB::table('drivers')->insert($rows);

        return DB::table('drivers')->where('company_id', $company->id)->pluck('id')->all();
    }

    protected function seedVehicles(Company $company, array $driverIds): array
    {
        $rows = [];

        for ($i = 0; $i < $this->vehicleCount; $i++) {
            $rows[] = [
                'company_id' => $company->id,
                'assigned_driver_id' => $driverIds[array_rand($driverIds)] ?? null,
                'registration_number' => 'PERF-'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'qr_code_value' => Str::uuid(),
                'make' => 'Toyota', 'model' => 'Hilux', 'year' => 2020,
                'current_odometer' => rand(10000, 150000),
                'status' => ['active', 'active', 'active', 'maintenance', 'inactive'][array_rand([0,1,2,3,4])],
                'created_at' => now(), 'updated_at' => now(),
            ];

            if (count($rows) >= 500) {
                DB::table('vehicles')->insert($rows);
                $rows = [];
            }
        }
        if ($rows) DB::table('vehicles')->insert($rows);

        return DB::table('vehicles')->where('company_id', $company->id)->pluck('id')->all();
    }

    protected function seedActivity(Company $company, array $vehicleIds, array $driverIds): void
    {
        $bar = $this->command->getOutput()->createProgressBar(count($vehicleIds));

        foreach ($vehicleIds as $index => $vehicleId) {
            $driverId = $driverIds[array_rand($driverIds)];
            $journeyRows = [];
            $km = rand(10000, 50000);

            for ($j = 0; $j < $this->journeysPerVehicle; $j++) {
                $startTime = now()->subDays(rand(1, 365))->subHours(rand(0, 23));
                $distance = rand(5, 300);
                $endKm = $km + $distance;

                $journeyRows[] = [
                    'company_id' => $company->id,
                    'vehicle_id' => $vehicleId,
                    'driver_id' => $driverId,
                    'status' => 'completed',
                    'start_km' => $km,
                    'start_lat' => 33.5 + (rand(-100, 100) / 1000),
                    'start_lng' => 73.0 + (rand(-100, 100) / 1000),
                    'start_time' => $startTime,
                    'end_km' => $endKm,
                    'end_lat' => 33.5 + (rand(-100, 100) / 1000),
                    'end_lng' => 73.0 + (rand(-100, 100) / 1000),
                    'end_time' => (clone $startTime)->addMinutes(rand(20, 240)),
                    'total_distance' => $distance,
                    'duration_minutes' => rand(20, 240),
                    'created_at' => $startTime, 'updated_at' => $startTime,
                ];

                $km = $endKm;

                if (count($journeyRows) >= 500) {
                    DB::table('journeys')->insert($journeyRows);
                    $journeyRows = [];
                }
            }
            if ($journeyRows) DB::table('journeys')->insert($journeyRows);

            // Fuel entries — independent of journey rows, same batching pattern.
            $fuelRows = [];
            for ($f = 0; $f < $this->fuelEntriesPerVehicle; $f++) {
                $litres = rand(20, 80);
                $rate = rand(250, 320);
                $fuelRows[] = [
                    'company_id' => $company->id, 'vehicle_id' => $vehicleId, 'driver_id' => $driverId,
                    'quantity_litres' => $litres, 'rate_per_litre' => $rate, 'total_cost' => $litres * $rate,
                    'odometer_reading' => rand(10000, 150000),
                    'entry_time' => now()->subDays(rand(1, 365)),
                    'created_at' => now(), 'updated_at' => now(),
                ];
            }
            DB::table('fuel_entries')->insert($fuelRows);

            // Maintenance records.
            $maintRows = [];
            for ($m = 0; $m < $this->maintenancePerVehicle; $m++) {
                $maintRows[] = [
                    'company_id' => $company->id, 'vehicle_id' => $vehicleId,
                    'type' => ['oil_change', 'service', 'repair'][array_rand([0,1,2])],
                    'cost' => rand(2000, 20000),
                    'service_date' => now()->subDays(rand(1, 365))->toDateString(),
                    'created_at' => now(), 'updated_at' => now(),
                ];
            }
            DB::table('maintenance_records')->insert($maintRows);

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();

        // Only give raw GPS pings to a small recent slice of journeys — in
        // real steady-state, most journeys are already past the archival
        // job's retention window and have zero raw rows. Seeding pings for
        // ALL 150k journeys would model a state your app will never
        // actually be in (post-archival-job), and waste seeding time.
        $this->command->info('Seeding GPS pings for recent journeys only...');
        $recentJourneyIds = DB::table('journeys')
            ->where('company_id', $company->id)
            ->where('start_time', '>=', now()->subDays(14))
            ->pluck('id');

        foreach ($recentJourneyIds as $journeyId) {
            $pingRows = [];
            for ($p = 0; $p < $this->pingsPerJourney; $p++) {
                $pingRows[] = [
                    'journey_id' => $journeyId,
                    'lat' => 33.5 + (rand(-100, 100) / 1000),
                    'lng' => 73.0 + (rand(-100, 100) / 1000),
                    'speed_kmh' => rand(0, 100),
                    'recorded_at' => now()->subMinutes(rand(0, 20000)),
                ];
            }
            DB::table('journey_locations')->insert($pingRows);
        }
    }
}