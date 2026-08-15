<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class FcmChannel
{
    public function __construct(protected Messaging $messaging)
    {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        $tokens = $notification->toFcm($notifiable);

        if (empty($tokens)) {
            return;
        }

        $data = method_exists($notification, 'toDatabase') ? $notification->toDatabase($notifiable) : [];

        $message = CloudMessage::new()->withNotification(
            FcmNotification::create($data['summary'] ?? 'Fleet Alert', '')
        );

        $report = $this->messaging->sendMulticast($message, $tokens);

        $this->pruneInvalidTokens($notifiable, $report);
    }

    protected function pruneInvalidTokens(object $notifiable, MulticastSendReport $report): void
    {
        foreach ($report->invalidTokens() as $invalidToken) {
            $notifiable->deviceTokens()->where('token', $invalidToken)->delete();
        }
    }
}