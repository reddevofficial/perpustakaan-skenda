<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LibraryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $message,
        public string $type,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'read_at' => null,
        ];
    }
}
