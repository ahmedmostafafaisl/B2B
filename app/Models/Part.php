<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Part extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'slug', 'description', 'is_active', 'sort_order', 'primary_image', 'banner',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['primary_image_url'];

    protected static function booted(): void
    {
        static::creating(function (Part $model) {
            if (empty($model->slug) && ! empty($model->title)) {
                $model->slug = static::generateUniqueSlug($model->title);
            } elseif (! empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->slug);
            }
        });

        static::updating(function (Part $model) {
            if ($model->isDirty('title') && empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->title, $model->id);
            } elseif ($model->isDirty('slug') && ! empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->slug, $model->id);
            }
        });
    }

    private static function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $slug = $base ?: Str::random(8);

        $query = static::query()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $counter = 2;
        while ($query->exists()) {
            $slugTry = "{$base}-{$counter}";
            $query = static::query()->where('slug', $slugTry);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            $slug = $slugTry;
            $counter++;
        }

        return $slug;
    }

    public function subParts()
    {
        return $this->hasMany(SubPart::class);
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        $path = $this->attributes['primary_image'] ?? null;

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

    // images
    // public function images(): MorphMany
    // {
    //     return $this->morphMany(Image::class, 'imageable');
    // }
}
