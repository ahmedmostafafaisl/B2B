<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubPartImage extends Model
{
    use HasFactory;
    protected $table = 'sub_part_images';
    protected $fillable = [
        'sub_part_id',
        'image',
    ];
    public function subPart()
    {
        return $this->belongsTo(SubPart::class, 'sub_part_id');
    }
}
