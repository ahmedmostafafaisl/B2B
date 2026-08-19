<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubpartModelSection extends Model
{
    protected $table = 'subpart_model_sections';

    protected $fillable = [
        'subpart_model_id','title','sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function model()
    {
        return $this->belongsTo(SubpartModel::class, 'subpart_model_id');
    }

    public function items()
    {
        return $this->hasMany(SubpartModelItem::class, 'subpart_model_section_id')
            ->orderBy('sort_order');
    }
}
