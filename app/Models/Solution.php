<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Solution extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'details',
        'organizations',
        'banner',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'organizations' => 'array',
        'details' => 'array',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(SolutionImage::class, 'solution_id');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(SolutionImage::class, 'solution_id')->where('is_primary', true);
    }
}
