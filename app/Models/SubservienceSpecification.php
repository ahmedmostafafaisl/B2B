<?php

namespace App\Models;

use App\Models\SubService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubservienceSpecification extends Model
{
    use HasFactory;
protected $fillable = [ 'sub_service_id', 'type', 'title', 'description', ];
public function subService() { return $this->belongsTo(SubService::class); }

}
