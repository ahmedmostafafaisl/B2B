<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactLog extends Model
{
    protected $fillable = [
        'contact_id',
        'user_id',
        'old_values',
        'new_values',
        'note',
        'action',
        'meta',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'meta'       => 'array',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
