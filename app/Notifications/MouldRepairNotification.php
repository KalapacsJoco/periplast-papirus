<?php

namespace App\Notifications;

use App\Models\Mould;
use Illuminate\Notifications\Notification;

class MouldRepairNotification extends Notification
{
    public function __construct(
        private readonly Mould $mould,
        private readonly string $event,
        private readonly string $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'mould_id' => $this->mould->id,
            'mould' => $this->mould->name,
            'event' => $this->event,
            'actor' => $this->actor,
        ];
    }
}
