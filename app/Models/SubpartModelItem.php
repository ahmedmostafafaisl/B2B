<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubpartModelItem extends Model
{
    protected $table = 'subpart_model_items';

    protected $fillable = [
        'subpart_model_section_id','text','sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function section()
    {
        return $this->belongsTo(SubpartModelSection::class, 'subpart_model_section_id');
    }
}
