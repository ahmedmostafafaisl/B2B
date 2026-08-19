<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubServiceFeatureType extends Model
{
    protected $fillable = ['sub_service_feature_id','name','sort_order'];

    public function feature()
    {
        return $this->belongsTo(SubServiceFeature::class, 'sub_service_feature_id');
    }

    public function items()
    {
        return $this->hasMany(SubServiceFeatureItem::class)->orderBy('sort_order');
    }
}
