<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskReminderNotification;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendTaskReminders extends Command
{
    protected $signature = 'tasks:send-reminders';

    protected $description = 'Send due-date reminders (overdue, due today, due tomorrow) to task assignees. '
        . 'Contacts are reminded indirectly, through any task linked to them.';

    public function handle(ActivityLogService $activityLogService): int
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $tasks = Task::query()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('assigned_to')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $tomorrow->toDateString())
            ->with(['assignedTo', 'contact'])
            ->get();

        $sent = 0;

        foreach ($tasks as $task) {
            if (!$task->assignedTo) {
                continue;
            }

            $reminderType = match (true) {
                $task->due_date->lt($today) => 'overdue',
                $task->due_date->equalTo($today) => 'due_today',
                default => 'due_tomorrow',
            };

            // Idempotency guard: don't resend the same reminder type for the
            // same task twice in one day, in case the command runs more
            // than once (manual re-run, retried cron, etc).
            $alreadySentToday = $task->activityLogs()
                ->where('action', 'reminder_sent')
                ->where('meta->reminder_type', $reminderType)
                ->whereDate('created_at', $today)
                ->exists();

            if ($alreadySentToday) {
                continue;
            }

            $task->assignedTo->notify(new TaskReminderNotification($task, $reminderType));

            $activityLogService->record(
                model: $task,
                action: 'reminder_sent',
                meta: ['reminder_type' => $reminderType],
            );

            $sent++;
        }

        $this->info("Sent {$sent} task reminder(s).");

        return self::SUCCESS;
    }
}
