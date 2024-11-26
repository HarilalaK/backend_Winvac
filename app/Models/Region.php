<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nom', 'province_id'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function centres()
    {
        return $this->hasMany(Centre::class);
    }
} 