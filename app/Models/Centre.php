<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Centre extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom',
        'region_id',
        'nombre_salles',
        'nombre_candidats',
        'numero_centre',
        'type_examen',
        'session'
    ];

    protected $casts = [
        'session' => 'integer',
        'nombre_salles' => 'integer',
        'nombre_candidats' => 'integer'
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function agents()
    {
        return $this->hasMany(Agent::class);
    }
} 