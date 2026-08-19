<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'is_active',
        'sort_order',
        'primary_image',
        'subtitle',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        // create
        static::creating(function (Service $model) {
            if (empty($model->slug) && ! empty($model->title)) {
                $model->slug = static::generateUniqueSlug($model->title);
            } elseif (! empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->slug);
            }
        });

        // update (only if slug not sent AND title changed)
        static::updating(function (Service $model) {
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

    public function images(): HasMany
    {
        return $this->hasMany(ServiceImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ServiceImage::class)->where('is_primary', true);
    }

    public function serviceTypes(): HasMany
    {
        return $this->hasMany(ServiceType::class);
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort_order');
    }
}
