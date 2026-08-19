<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SubServiceFeature extends Model
{
    protected $fillable = [
        'sub_service_id','title','image','sort_order','is_active'
    ];

     protected $appends = ['image_url'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subService()
    {
        return $this->belongsTo(SubService::class);
    }

    public function types()
    {
        return $this->hasMany(SubServiceFeatureType::class)->orderBy('sort_order');
    }

     public function getImageUrlAttribute(): ?string
    {
        if (!$this->attributes['image'] ?? null) return null;
        return Storage::disk('s3')->url($this->attributes['image']);
    }
}
