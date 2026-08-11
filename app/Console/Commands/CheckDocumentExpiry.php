<?php

namespace App\Console\Commands;

use App\Models\Reminder;
use App\Models\User;
use App\Models\VehicleDocument;
use App\Notifications\ReminderNotification;
use Illuminate\Console\Command;

class CheckDocumentExpiry extends Command
{
    protected $signature = 'fleet:check-document-expiry';

    protected $description = 'Find vehicle document reminders due soon and notify company admins/dispatchers.';

    public function handle(): void
    {
        $reminders = Reminder::withoutGlobalScopes()
            ->where('reminder_type', 'document')
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', now()->addDays(30))
            ->get();

        foreach ($reminders as $reminder) {
            /** @var VehicleDocument|null $document */
            $document = $reminder->reference;

            if (! $document) {
                continue;
            }

            $vehicle = $document->vehicle;
            $summary = "{$document->document_type} document for {$vehicle->registration_number} expires on {$reminder->due_date?->toFormattedDateString()}";

            $recipients = User::withoutGlobalScopes()
                ->where('company_id', $reminder->company_id)
                ->role(['company_admin', 'dispatcher'])
                ->get();

            foreach ($recipients as $recipient) {
                $recipient->notify(new ReminderNotification($reminder, $summary));
            }

            $reminder->update(['status' => 'sent']);
        }

        $this->info("Processed {$reminders->count()} document reminder(s).");
    }
}