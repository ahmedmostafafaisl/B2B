<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogSection extends Model
{
    protected $fillable = [
        'blog_id',
        'type',
        'content',
        'sort_order',
    ];

    protected $casts = [
        'content' => 'array',
        'sort_order' => 'integer',
    ];

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }
}
