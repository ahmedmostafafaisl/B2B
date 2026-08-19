<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubServiceModelSection extends Model
{
    protected $fillable = ['sub_service_model_id','title','sort_order'];

    public function model()
    {
        return $this->belongsTo(SubServiceModel::class, 'sub_service_model_id');
    }

    public function items()
    {
        return $this->hasMany(SubServiceModelItem::class)->orderBy('sort_order');
    }
}
