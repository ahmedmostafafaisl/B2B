<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTypeSpecification extends Model
{
    use HasFactory;

    protected $fillable = ['service_type_id', 'type', 'title', 'description'];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}
