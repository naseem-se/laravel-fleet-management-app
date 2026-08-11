<?php

namespace App\Console\Commands;

use App\Models\MaintenanceRecord;
use App\Models\Reminder;
use App\Models\User;
use App\Notifications\ReminderNotification;
use Illuminate\Console\Command;

class CheckMaintenanceDue extends Command
{
    protected $signature = 'fleet:check-maintenance-due';

    protected $description = 'Find maintenance reminders due soon and notify company admins/dispatchers.';

    public function handle(): void
    {
        $reminders = Reminder::withoutGlobalScopes()
            ->where('reminder_type', 'maintenance')
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', now()->addDays(7))
            ->with('company')
            ->get();

        foreach ($reminders as $reminder) {
            /** @var MaintenanceRecord|null $record */
            $record = $reminder->reference;

            if (! $record) {
                continue;
            }

            $vehicle = $record->vehicle;
            $summary = "Maintenance due for {$vehicle->registration_number} on {$reminder->due_date?->toFormattedDateString()}";

            $recipients = User::withoutGlobalScopes()
                ->where('company_id', $reminder->company_id)
                ->role(['company_admin', 'dispatcher'])
                ->get();

            foreach ($recipients as $recipient) {
                $recipient->notify(new ReminderNotification($reminder, $summary));
            }

            $reminder->update(['status' => 'sent']);
        }

        $this->info("Processed {$reminders->count()} maintenance reminder(s).");
    }
}