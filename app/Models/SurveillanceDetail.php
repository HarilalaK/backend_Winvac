<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveillanceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'jours_surveillance',
        'jours_encours',
        'jours_ensalles',
        'taux_par_jour',
        'taux_brut',
        'irsa',
        'taux_net',
        'created_by',
        'updated_by'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->created_by = auth()->user()->login ?? 'HarilalaK';
        });
        
        static::updating(function ($model) {
            $model->updated_by = auth()->user()->login ?? 'HarilalaK';
        });
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}