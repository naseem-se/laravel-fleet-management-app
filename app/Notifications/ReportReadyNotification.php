<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ReportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $filePath, protected string $reportType)
    {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'report_ready',
            'summary' => ucfirst(str_replace('-', ' ', $this->reportType))." report is ready to download",
            'download_url' => Storage::temporaryUrl($this->filePath, now()->addHours(24)),
        ];
    }
}