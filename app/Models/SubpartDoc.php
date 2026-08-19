<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SubpartDoc extends Model
{
    protected $table = 'subpart_docs';

    protected $fillable = [
        'sub_part_id',
        'title',
        'file_path',
        'file_original_name',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    protected $appends = ['file_url'];

    public function subPart()
    {
        return $this->belongsTo(\App\Models\SubPart::class, 'sub_part_id');
    }

    public function getFileUrlAttribute(): ?string
    {
        $path = $this->attributes['file_path'] ?? null;
        return $path ? Storage::disk('s3')->url($path) : null;
    }
}
