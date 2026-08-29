<?php

namespace App\Notifications;

use App\Helper\FirebaseHelper;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
        private readonly array $changedFields,
        private readonly ?string $updatedByUsername = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $title = 'Task updated';

        $message = sprintf(
            '"%s" was updated (%s)',
            $this->task->title,
            implode(', ', $this->changedFields)
        );

        $data = [
            'notification_type' => 'task_updated',
            'task_id'    => $this->task->id,
            'contact_id' => $this->task->contact_id,
            'changed_fields' => $this->changedFields,
            'updated_by' => $this->updatedByUsername,
        ];

        if (!empty($notifiable->fcm_token)) {
            FirebaseHelper::sendNotification($notifiable->fcm_token, $title, $message, $data);
        }

        return [
            'title' => $title,
            'message' => $message,
            'notification_type' => 'task_updated',
            'data' => $data,
        ];
    }
}
