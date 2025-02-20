<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        // Champ pour les rôles fixes
        'taux_forfaitaire',
        // Champs communs
        'jours_travaille',
        'taux_journalier',
        // Champs surveillants
        'jours_surveillance',
        'jours_encours',
        'jours_ensalles',
        'taux_par_jour',
        // Champs correcteurs
        'matiere',
        'nombre_copie',
        'taux_par_copie',
        // Champs calculs
        'taux_brut',
        'irsa',
        'taux_net',
        // Champs audit
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'jours_travaille' => 'integer',
        'taux_journalier' => 'decimal:2',
        'jours_surveillance' => 'integer',
        'jours_encours' => 'integer',
        'jours_ensalles' => 'integer',
        'taux_par_jour' => 'decimal:2',
        'nombre_copie' => 'integer',
        'taux_par_copie' => 'decimal:2',
        'taux_brut' => 'decimal:2',
        'irsa' => 'decimal:2',
        'taux_net' => 'decimal:2',
        'taux_forfaitaire' => 'decimal:2'
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

    // Méthodes utilitaires pour les calculs
    public function calculateTauxBrut()
    {
        if ($this->agent->role === 'Surveillance') {
            $totalJours = $this->jours_surveillance + $this->jours_encours + $this->jours_ensalles;
            return $totalJours * $this->taux_par_jour;
        } elseif ($this->agent->role === 'Correcteur') {
            return $this->nombre_copie * $this->taux_par_copie;
        } elseif (in_array($this->agent->role, ['PDO', 'VPDO', 'CDC', 'CDCA'])) {
            return $this->taux_forfaitaire;
        } else {
            return $this->jours_travaille * $this->taux_journalier;
        }
    }

    public function calculateIRSA()
    {
        return $this->taux_brut * 0.2; // 20% IRSA
    }

    public function calculateTauxNet()
    {
        return $this->taux_brut - $this->irsa;
    }
}