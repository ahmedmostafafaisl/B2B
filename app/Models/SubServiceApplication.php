<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubServiceApplication extends Model
{
    protected $fillable = [
        'sub_service_id',
        'title',
        'items',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'items' => 'array',
        'is_active' => 'boolean',
    ];

    public function subService()
    {
        return $this->belongsTo(SubService::class);
    }
}
