<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubpartSpecification extends Model
{
    protected $table = 'subpart_specifications';

    protected $fillable = [
        'sub_part_id', 'type', 'title', 'description'
    ];

    public function subPart()
    {
        return $this->belongsTo(SubPart::class, 'sub_part_id');
    }
}
