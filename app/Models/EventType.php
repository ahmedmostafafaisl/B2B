<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventType extends Model
{
    protected $fillable = [
        'name',
        'label',
        'status',
        'requires_approval',
        'affects_availability',
        'color',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'affects_availability' => 'boolean',
        'color' => 'string',
        'status' => 'string',
    ];

    public function userEvents(): HasMany
    {
        return $this->hasMany(UserEvent::class);
    }
}
