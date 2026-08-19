<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubpartReview extends Model
{
    protected $table = 'subpart_reviews';

    protected $fillable = [
        'sub_part_id',
        'rate',
        'reviewer_name',
        'subject',
        'comment',
    ];

    protected $casts = [
        'rate' => 'integer',
    ];

    public function subPart()
    {
        return $this->belongsTo(SubPart::class, 'sub_part_id');
    }
}
