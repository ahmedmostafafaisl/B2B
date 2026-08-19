<?php

namespace App\Services\ActivityLog;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Single place responsible for writing activity_logs rows, so every model
 * (Contact today, others tomorrow) gets identical, consistent audit
 * entries instead of each repository hand-rolling its own meta block.
 */
class ActivityLogService
{
    /**
     * Write a log entry for any loggable model.
     */
    public function record(
        Model $model,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $note = null,
        array $meta = [],
    ): ActivityLog {
        $user = Auth::user();

        $standardMeta = [
            'performed_by_user_id'   => $user?->id,
            'performed_by_user_name' => $user?->username,
            'performed_by_email'     => $user?->email,
            'performed_at'           => now()->toISOString(),
        ];

        return $model->activityLogs()->create([
            'user_id'    => $user?->id,
            'action'     => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'note'       => $note,
            'meta'       => array_merge($standardMeta, $meta),
        ]);
    }

    /**
     * Convenience wrapper for the common "diff of dirty attributes" case,
     * e.g. after $model->fill($data); $changes = $model->getDirty();
     */
    public function recordChanges(
        Model $model,
        array $oldValues,
        array $newValues,
        ?string $note = null,
        array $meta = [],
    ): ActivityLog {
        return $this->record(
            model: $model,
            action: 'update',
            oldValues: $oldValues,
            newValues: $newValues,
            note: $note,
            meta: array_merge([
                'changed_fields' => array_keys($newValues),
            ], $meta),
        );
    }
}
