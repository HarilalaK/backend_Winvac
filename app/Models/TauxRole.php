<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle TauxRole - Gestion des taux par rôle
 * 
 * Ce modèle gère les différents taux applicables selon les rôles des agents.
 * Les taux sont uniformes pour toutes les régions et peuvent être modifiés
 * dynamiquement par les administrateurs.
 */
class TauxRole extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Les attributs qui peuvent être assignés en masse
     * @var array
     */
    protected $fillable = [
        'role',
        'taux_forfaitaire',
        'taux_journalier',
        'taux_base_correcteur',
        'taux_surplus_bep',
        'taux_surplus_autres',
        'created_by',
        'updated_by',
        'is_active' // Pour désactiver un rôle sans le supprimer
    ];

    /**
     * Les attributs à caster en types spécifiques
     * @var array
     */
    protected $casts = [
        'taux_forfaitaire' => 'decimal:2',
        'taux_journalier' => 'decimal:2',
        'taux_base_correcteur' => 'decimal:2',
        'taux_surplus_bep' => 'decimal:2',
        'taux_surplus_autres' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Boot du modèle - Gestion automatique des champs d'audit
     */
    protected static function boot()
    {
        parent::boot();
        
        // Assigner automatiquement le créateur lors de la création
        static::creating(function ($model) {
            $model->created_by = auth()->user()->nom_prenom ?? 'System';
            $model->is_active = true; // Actif par défaut
        });
        
        // Assigner automatiquement le modificateur lors de la mise à jour
        static::updating(function ($model) {
            $model->updated_by = auth()->user()->nom_prenom ?? 'System';
        });
    }

    /**
     * Scope pour récupérer uniquement les rôles actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Vérifier si le rôle est de type forfaitaire
     */
    public function isForfaitaire()
    {
        return in_array($this->role, ['PDO', 'VPDO', 'CDC', 'CDCA']);
    }

    /**
     * Vérifier si le rôle est de type journalier
     */
    public function isJournalier()
    {
        return in_array($this->role, ['Secretaire', 'SecOrg', 'Surveillance', 'Securite']);
    }

    /**
     * Vérifier si le rôle est un correcteur
     */
    public function isCorrecteur()
    {
        return $this->role === 'Correcteur';
    }

    /**
     * Calculer le taux brut selon le rôle et les paramètres
     * 
     * @param string $role Le rôle de l'agent
     * @param array $params Paramètres spécifiques au rôle
     * @return float Le taux calculé
     */
    public function calculateTauxBrut($params = [])
    {
        try {
            switch ($this->role) {
                case 'PDO':
                case 'VPDO':
                case 'CDC':
                case 'CDCA':
                    // Rôles forfaitaires
                    return $this->taux_forfaitaire ?? 0;

                case 'Secretaire':
                case 'SecOrg':
                case 'Securite':
                    // Rôles journaliers simples
                    $jours = max(1, $params['jours_travaille'] ?? 1);
                    return ($this->taux_journalier ?? 0) * $jours;

                case 'Surveillance':
                    // Rôle surveillance avec différents types de jours
                    $joursSurveillance = $params['jours_surveillance'] ?? 0;
                    $joursEncours = $params['jours_encours'] ?? 0;
                    $joursEnsalles = $params['jours_ensalles'] ?? 0;
                    $totalJours = $joursSurveillance + $joursEncours + $joursEnsalles;
                    return ($this->taux_journalier ?? 0) * max(1, $totalJours);

                case 'Correcteur':
                    // Rôle correcteur avec calcul par copies
                    return $this->calculateTauxCorrecteur($params);

                default:
                    \Log::warning("Rôle non reconnu pour le calcul de taux: {$this->role}");
                    return 0;
            }
        } catch (\Exception $e) {
            \Log::error("Erreur lors du calcul du taux pour le rôle {$this->role}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculer le taux pour un correcteur
     * 
     * @param array $params Doit contenir 'nombre_copie' et 'type_examen'
     * @return float Le taux calculé
     */
    private function calculateTauxCorrecteur($params)
    {
        $nombreCopies = max(0, $params['nombre_copie'] ?? 0);
        $typeExamen = $params['type_examen'] ?? 'Autres';
        
        if ($nombreCopies <= 100) {
            return $this->taux_base_correcteur ?? 0;
        } else {
            $surplusCopies = $nombreCopies - 100;
            $tauxSurplus = ($typeExamen === 'BEP') 
                ? ($this->taux_surplus_bep ?? 0) 
                : ($this->taux_surplus_autres ?? 0);
            
            return ($this->taux_base_correcteur ?? 0) + ($surplusCopies * $tauxSurplus);
        }
    }

    /**
     * Obtenir les rôles disponibles avec leurs types
     */
    public static function getAvailableRoles()
    {
        return [
            'forfaitaires' => ['PDO', 'VPDO', 'CDC', 'CDCA'],
            'journaliers' => ['Secretaire', 'SecOrg', 'Surveillance', 'Securite'],
            'correcteurs' => ['Correcteur']
        ];
    }

    /**
     * Vérifier si ce taux est utilisé par des agents
     */
    public function isUsedByAgents()
    {
        return \App\Models\Agent::where('role', $this->role)->exists();
    }

    /**
     * Désactiver un rôle au lieu de le supprimer
     */
    public function deactivate()
    {
        if ($this->isUsedByAgents()) {
            throw new \Exception('Impossible de désactiver un rôle utilisé par des agents');
        }
        
        $this->update(['is_active' => false]);
        return true;
    }
}