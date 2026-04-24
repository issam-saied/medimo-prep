<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PrescriptionsExpiredNotification extends Notification
{
    use Queueable;

    public function __construct(public int $count) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message'         => $this->count . ' prescriptions has been expired',
            'count' => $this->count,
        ];
    }
}
