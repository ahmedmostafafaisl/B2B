<?php

namespace App\Notifications;

use App\Helper\FirebaseHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BulkDataNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly array $data,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Stores $this->data as-is as the notification's data column. If the
     * caller happened to include 'title' and ('body' or 'message') inside
     * that data, an FCM push is also attempted — same opportunistic pattern
     * as the other Task notifications — but it's not required.
     */
    public function toDatabase($notifiable): array
    {
        $title = $this->data['title'] ?? null;
        $body  = $this->data['body'] ?? $this->data['message'] ?? null;

        if (!empty($notifiable->fcm_token) && $title && $body) {
            FirebaseHelper::sendNotification($notifiable->fcm_token, $title, $body, $this->data);
        }

        return $this->data;
    }
}
