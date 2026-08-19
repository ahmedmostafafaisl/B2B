<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SubpartFeature extends Model
{
    // protected $table = 'subpart_features';

    protected $fillable = [
        'sub_part_id', 'title', 'image', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

     protected $appends = ['image_url'];

    public function subPart()
    {
        return $this->belongsTo(SubPart::class, 'sub_part_id');
    }

    public function types()
    {
        return $this->hasMany(SubpartFeatureType::class, 'subpart_feature_id')
            ->orderBy('sort_order');
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->attributes['image'] ?? null;
        return $path ? Storage::disk('s3')->url($path) : null;
    }
}
