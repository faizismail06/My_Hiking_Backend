<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DssRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $trailName,
        private readonly string $reason
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'dss_rejected',
            'title' => 'Data DSS Ditolak',
            'message' => "Pengajuan DSS untuk jalur {$this->trailName} ditolak admin.",
            'trail_name' => $this->trailName,
            'reason' => $this->reason,
        ];
    }
}
