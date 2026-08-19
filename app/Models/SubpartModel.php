<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SubpartModel extends Model
{
    protected $table = 'subpart_models';

    protected $fillable = [
        'sub_part_id','title','image','sort_order','is_active'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_url'];

    public function subPart()
    {
        return $this->belongsTo(SubPart::class, 'sub_part_id');
    }

    public function sections()
    {
        return $this->hasMany(SubpartModelSection::class, 'subpart_model_id')
            ->orderBy('sort_order');
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->attributes['image'] ?? null;
        return $path ? Storage::disk('s3')->url($path) : null;
    }
}
