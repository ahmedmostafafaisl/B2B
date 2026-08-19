<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubpartFeatureItem extends Model
{
    protected $table = 'subpart_feature_items';

    protected $fillable = [
        'subpart_feature_type_id', 'text', 'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function type()
    {
        return $this->belongsTo(SubpartFeatureType::class, 'subpart_feature_type_id');
    }
}
