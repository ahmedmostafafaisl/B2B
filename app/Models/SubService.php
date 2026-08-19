<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class SubService extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type_id',
        'title',
        'slug',
        'description',
        'is_active',
        'sort_order',
        'primary_image',
        'image_365',
        'description_365',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        // create
        static::creating(function (SubService $model) {
            if (empty($model->slug) && ! empty($model->title)) {
                $model->slug = static::generateUniqueSlug($model->title);
            } elseif (! empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->slug);
            }
        });

        // update (only if slug not sent AND title changed)
        static::updating(function (SubService $model) {
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

    // public function service(): BelongsTo
    // {
    //     return $this->belongsTo(Service::class);
    // }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(SubServiceImage::class, 'sub_service_id');
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort_order');
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(SubservienceSpecification::class, 'sub_service_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SubservienceReview::class, 'sub_service_id');
    }

    public function docs(): HasMany
    {
        return $this->hasMany(SubservienceDoc::class, 'sub_service_id');
    }

    public function features(): HasMany
    {
        return $this->hasMany(SubServiceFeature::class, 'sub_service_id');
    }

    public function applications()
    {
        return $this->hasMany(SubServiceApplication::class, 'sub_service_id');
    }

    public function models()
    {
        return $this->hasMany(SubServiceModel::class, 'sub_service_id');
    }
}
