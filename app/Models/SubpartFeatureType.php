<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubpartFeatureType extends Model
{
    protected $table = 'subpart_feature_types';

    protected $fillable = [
        'subpart_feature_id', 'name', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function feature()
    {
        return $this->belongsTo(SubpartFeature::class, 'subpart_feature_id');
    }

    public function items()
    {
        return $this->hasMany(SubpartFeatureItem::class, 'subpart_feature_type_id')
            ->orderBy('sort_order');
    }
}
