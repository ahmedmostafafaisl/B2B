<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubpartApplication extends Model
{
    protected $table = 'subpart_applications';

    protected $fillable = [
        'sub_part_id',
        'title',
        'items',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'items' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function subPart()
    {
        return $this->belongsTo(SubPart::class, 'sub_part_id');
    }
}
