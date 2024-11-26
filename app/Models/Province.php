<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Province extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nom'];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }
} 