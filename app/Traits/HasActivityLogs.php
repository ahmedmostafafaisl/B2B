<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Add to any model that needs an audit trail (Contact, Part, Service, ...).
 * Pairs with App\Services\ActivityLog\ActivityLogService, which is
 * responsible for actually writing entries — this trait only exposes the
 * read relation so controllers/resources can eager-load a model's history.
 */
trait HasActivityLogs
{
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'loggable')
            ->latest('id');
    }
}
