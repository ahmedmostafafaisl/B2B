<?php

namespace App\Notifications;

use App\Helper\FirebaseHelper;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
        private readonly ?string $assignedByUsername = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database']; // FCM handled manually in toDatabase(), same as TechNotification.
    }

    public function toDatabase($notifiable): array
    {
        $title = 'New task assigned to you';

        $message = sprintf(
            '"%s" (%s priority)%s',
            $this->task->title,
            $this->task->priority,
            $this->task->due_date ? ', due ' . $this->task->due_date->toDateString() : ''
        );

        $data = [
            'notification_type' => 'task_assigned',
            'task_id'    => $this->task->id,
            'contact_id' => $this->task->contact_id,
            'assigned_by' => $this->assignedByUsername,
        ];

        if (!empty($notifiable->fcm_token)) {
            FirebaseHelper::sendNotification($notifiable->fcm_token, $title, $message, $data);
        }

        return [
            'title' => $title,
            'message' => $message,
            'notification_type' => 'task_assigned',
            'data' => $data,
        ];
    }
}
