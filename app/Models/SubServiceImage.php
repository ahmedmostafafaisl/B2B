<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubServiceImage extends Model
{
    protected $table = 'sub_service_images';

    protected $fillable = [
        'sub_service_id',
        'image',
    ];

    public function subService(): BelongsTo
    {
        return $this->belongsTo(SubService::class, 'sub_service_id');
    }
}
