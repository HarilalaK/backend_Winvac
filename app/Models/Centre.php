<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Centre extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nom', 'type', 'region_id'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function agents()
    {
        return $this->hasMany(Agent::class);
    }
} 