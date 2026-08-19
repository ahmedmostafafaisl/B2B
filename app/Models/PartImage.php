<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PartImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id', 'image', 'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'part_id');
    }
}
