<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\Concerns\HasPresence;

class SingleEmployeeResource extends JsonResource
{
    use HasPresence;

    public function toArray(Request $request): array
    {


        return [
            'id' => $this->id,
            'user_name' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image,
            'type' => $this->type ?? null,
            'role' => $this->getRoleNames()->first(),
            'status' => $this->status,
            'subjects' => $this->subjects->map(fn($subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
            ]),
            'events' => $this->events->map(fn($event) => [
                'id' => $event->id,
                'event_type' => $event->eventType ? [
                    'id' => $event->eventType->id,
                    'name' => $event->eventType->name,
                    'label' => $event->eventType->label,
                    'color' => $event->eventType->color,
                ] : null,
                'status' => $event->status,
                'note' => $event->note,
                'start_at' => optional($event->start_at)->toDateString(),
                'end_at' => optional($event->end_at)->toDateString(),
                'days_of_week' => $event->days_of_week,
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'created_at' => optional($event->created_at)->toISOString(),
            ]),
            'summary' => $this->taskSummary(),
            'last_login_at' => optional($this->last_login_at)->toISOString(),
            'presence' => $this->presence(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'permissions' => $this->groupedPermissions(),
        ];
    }




    public function groupedPermissions()
    {
        return $this->permissions->groupBy(function ($permission) {
            return explode(' ', $permission->name, 2)[1] ?? 'other';
        })->map(function ($group) {
            return $group->pluck('name')->values();
        });
    }

    /**
     * Task counts by status for this user's assigned tasks, plus how many
     * of their still-open tasks are past due_date.
     */
    private function taskSummary(): array
    {
        $counts = $this->assignedTasks()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $overdue = $this->assignedTasks()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->count();

        return [
            'total_tasks' => (int) $counts->sum(),
            'tasks_new' => (int) ($counts['new'] ?? 0),
            'tasks_in_progress' => (int) ($counts['in_progress'] ?? 0),
            'tasks_review' => (int) ($counts['review'] ?? 0),
            'tasks_completed' => (int) ($counts['completed'] ?? 0),
            'tasks_cancelled' => (int) ($counts['cancelled'] ?? 0),
            'tasks_overdue' => $overdue,
        ];
    }
}
