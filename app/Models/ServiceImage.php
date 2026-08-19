<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceImage extends Model
{
    use HasFactory;

     protected $fillable = [
        'service_id',
        'image',
       'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
     ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
