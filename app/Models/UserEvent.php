<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEvent extends Model
{
    protected $fillable = [
        'user_id',
        'event_type_id',
        'status',
        'note',
        'start_at',
        'end_at',
        'days_of_week',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
        'days_of_week' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }
}
