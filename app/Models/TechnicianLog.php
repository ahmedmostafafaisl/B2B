<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tech_id',
        'action_type',
        'description',
        'data',
        'action_by',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
