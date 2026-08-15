<?php

namespace App\Notifications;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Reminder $reminder, protected string $summary)
    {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        if ($notifiable->deviceTokens()->exists()) {
            $channels[] = 'fcm';
        }

        return $channels;
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

    public function toFcm(object $notifiable): array
    {
        return $notifiable->deviceTokens->pluck('token')->all();
    }
}