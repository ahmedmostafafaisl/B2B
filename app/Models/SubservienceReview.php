<?php

namespace App\Models;

use App\Models\SubService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubservienceReview extends Model
{
    use HasFactory;
protected $fillable = [ 'sub_service_id', 'rate', 'reviewer_name', 'subject', 'comment', ]; public function subService() { return $this->belongsTo(SubService::class); } }
