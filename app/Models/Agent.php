<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'annee',
        'centre_id',
        'situation',
        'role',
        'typeExamen',
        'im',
        'cin',
        'nom',
        'prenom',
        'sexe',
        'lieu_cin',
        'date_cin',
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

        // Supprimer les détails de l'agent lors de sa suppression
        static::deleting(function ($agent) {
            $agent->detail()->delete();
        });
    }

    // Nouvelle relation unique avec AgentDetail
    public function detail()
    {
        return $this->hasOne(AgentDetail::class);
    }

    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }
}