<?php

namespace App\Models;

use App\Models\SubService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SubservienceDoc extends Model
{
    use HasFactory;
    protected $table = 'subservience_docs';

     protected $appends = ['file_url'];
protected $fillable = [ 'sub_service_id', 'title', 'file_path', 'file_original_name', 'file_size', ];
public function subService()
 {
    return $this->belongsTo(SubService::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->attributes['file_path'] ?? null) return null;
        return Storage::disk('s3')->url($this->attributes['file_path']);
    }
}
