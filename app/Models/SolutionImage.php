<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolutionImage extends Model
{
    protected $fillable = [
        'solution_id', 'image', 'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function solution(): BelongsTo
    {
        return $this->belongsTo(Solution::class, 'solution_id');
    }
}
