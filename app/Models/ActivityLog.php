<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'note',
        'meta',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'meta'       => 'array',
    ];

    /**
     * The model this log entry belongs to (Contact, Part, Service, etc.).
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The authenticated user who triggered the logged action, if any.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
