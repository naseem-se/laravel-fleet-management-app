<?php

namespace App\Notifications;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReminderNotification extends Notification
{
    use Queueable;

    public function __construct(protected Reminder $reminder, protected string $summary)
    {
    }

    /**
     * database → bell icon in the dashboard; mail → email. FCM push can be
     * added here later (e.g. 'fcm') once a push channel package is wired up
     * — nothing else about this class needs to change.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'reminder_id' => $this->reminder->id,
            'type' => $this->reminder->reminder_type,
            'summary' => $this->summary,
            'due_date' => $this->reminder->due_date,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Fleet Alert: '.$this->summary)
            ->line($this->summary)
            ->line('Due date: '.($this->reminder->due_date?->toFormattedDateString() ?? 'N/A'));
    }
}