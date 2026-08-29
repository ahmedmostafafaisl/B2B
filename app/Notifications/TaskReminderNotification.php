<?php

namespace App\Notifications;

use App\Helper\FirebaseHelper;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskReminderNotification extends Notification
{
    use Queueable;

    public const LABELS = [
        'overdue'      => 'is overdue',
        'due_today'    => 'is due today',
        'due_tomorrow' => 'is due tomorrow',
    ];

    /**
     * @param string $reminderType One of: overdue, due_today, due_tomorrow.
     */
    public function __construct(
        private readonly Task $task,
        private readonly string $reminderType,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $title = 'Task reminder';

        // Piggybacks the contact into the message when the task has one —
        // this is how contact reminders are delivered, via their linked task.
        $contactPart = $this->task->contact ? " for {$this->task->contact->name}" : '';
        $label = self::LABELS[$this->reminderType] ?? 'needs attention';

        $message = sprintf(
            '"%s"%s %s (due %s)',
            $this->task->title,
            $contactPart,
            $label,
            optional($this->task->due_date)->toDateString()
        );

        $data = [
            'notification_type' => 'task_reminder',
            'task_id'    => $this->task->id,
            'contact_id' => $this->task->contact_id,
            'reminder_type' => $this->reminderType,
        ];

        if (!empty($notifiable->fcm_token)) {
            FirebaseHelper::sendNotification($notifiable->fcm_token, $title, $message, $data);
        }

        return [
            'title' => $title,
            'message' => $message,
            'notification_type' => 'task_reminder',
            'data' => $data,
        ];
    }
}
