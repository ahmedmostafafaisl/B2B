<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubServiceFeatureItem extends Model
{
    protected $fillable = ['sub_service_feature_type_id','text','sort_order'];

    public function type()
    {
        return $this->belongsTo(SubServiceFeatureType::class, 'sub_service_feature_type_id');
    }
}
