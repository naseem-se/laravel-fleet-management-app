<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\User;
use App\Notifications\ReminderNotification;
use App\Models\Reminder;
use Illuminate\Console\Command;

class CheckLicenseExpiry extends Command
{
    protected $signature = 'fleet:check-license-expiry';

    protected $description = 'Find drivers whose license expires soon and notify company admins/dispatchers.';

    public function handle(): void
    {
        $drivers = Driver::withoutGlobalScopes()
            ->whereNotNull('license_expiry_date')
            ->whereDate('license_expiry_date', '<=', now()->addDays(30))
            ->where('status', 'active')
            ->get();

        foreach ($drivers as $driver) {
            $reminder = new Reminder([
                'company_id' => $driver->company_id,
                'reminder_type' => 'license',
                'due_date' => $driver->license_expiry_date,
            ]);

            $summary = "{$driver->name}'s driving license expires on {$driver->license_expiry_date->toFormattedDateString()}";

            $recipients = User::withoutGlobalScopes()
                ->where('company_id', $driver->company_id)
                ->role(['company_admin', 'dispatcher'])
                ->get();

            foreach ($recipients as $recipient) {
                $recipient->notify(new ReminderNotification($reminder, $summary));
            }
        }

        $this->info("Processed {$drivers->count()} license expiry check(s).");
    }
}