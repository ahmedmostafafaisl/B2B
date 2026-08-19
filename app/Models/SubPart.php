<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class SubPart extends Model
{
    protected $fillable = [
        'part_id',
        'title',
        'slug',
        'description',
        'is_active',
        'sort_order',
        'primary_image',
        'image_365',
        'description_365',
        'parent_id',
        'banner',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['primary_image_url', 'image_365_url'];

    public function part()
    {
        return $this->belongsTo(\App\Models\Part::class);
    }

    // images
    public function images()
    {
        return $this->hasMany(SubPartImage::class, 'sub_part_id');
    }
    // features(), models(), applications(), specs(), reviews(), docs()

    public function features()
    {
        return $this->hasMany(SubpartFeature::class, 'sub_part_id');
    }

    public function specifications()
    {
        return $this->hasMany(SubpartSpecification::class, 'sub_part_id');
    }

    public function reviews()
    {
        return $this->hasMany(SubpartReview::class, 'sub_part_id');
    }

    public function docs()
    {
        return $this->hasMany(SubpartDoc::class, 'sub_part_id');
    }

    public function models()
    {
        return $this->hasMany(SubpartModel::class, 'sub_part_id');
    }

    public function applications()
    {
        return $this->hasMany(SubpartApplication::class, 'sub_part_id');
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        $path = $this->attributes['primary_image'] ?? null;

        return $path ? Storage::disk('s3')->url($path) : null;
    }

    public function getImage365UrlAttribute(): ?string
    {
        $path = $this->attributes['image_365'] ?? null;

        return $path ? Storage::disk('s3')->url($path) : null;
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort_order');
    }

    public function banners(): MorphMany
    {
        return $this->morphMany(Banner::class, 'bannerable');
    }

    // Direct children
    public function children(): HasMany
    {
        return $this->hasMany(SubPart::class, 'parent_id');
    }

    // All nested children (recursive)
    public function allChildren(): HasMany
    {
        return $this->hasMany(SubPart::class, 'parent_id')->with('allChildren');
    }

    // Parent sub_part
    public function parent(): BelongsTo
    {
        return $this->belongsTo(SubPart::class, 'parent_id');
    }
}
