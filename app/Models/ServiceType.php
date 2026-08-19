<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ServiceType extends Model
{
    protected $fillable = [
        'service_id',
        'code',
        'name',
        'title',
        'subtitle',
        'description',
        'primary_image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'service_id' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ServiceType $model) {
            if (empty($model->code)) {
                $model->code = static::generateUniqueCode($model);
            }
        });
    }

    private static function generateUniqueCode(ServiceType $model): string
    {
        $prefix = $model->name
            ? strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $model->name), 0, 3))
            : 'ST';

        do {
            $code = $prefix.'-'.strtoupper(substr(uniqid(), -6));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function subServices(): HasMany
    {
        return $this->hasMany(SubService::class);
    }

    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable');
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(ServiceTypeSpecification::class);
    }
}
