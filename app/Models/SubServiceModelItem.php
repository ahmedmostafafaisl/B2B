<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubServiceModelItem extends Model
{
    protected $fillable = ['sub_service_model_section_id','text','sort_order'];

    public function section()
    {
        return $this->belongsTo(SubServiceModelSection::class, 'sub_service_model_section_id');
    }
}
