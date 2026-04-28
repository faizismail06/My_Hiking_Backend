<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DssApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $trailName
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'dss_approved',
            'title' => 'Data DSS Disetujui',
            'message' => "Pengajuan DSS untuk jalur {$this->trailName} telah disetujui admin.",
            'trail_name' => $this->trailName,
        ];
    }
}
