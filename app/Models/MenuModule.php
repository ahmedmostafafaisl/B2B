<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuModule extends Model
{
    protected $table = 'menu_modules';

    protected $fillable = [
        'title', 'slug', 'icon', 'link', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_module_id');
    }
}
