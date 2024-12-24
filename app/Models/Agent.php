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
        'jours_travaille',
        'im',
        'cin',
        'nom',
        'prenom',
        'sexe',
        'lieu_cin',
        'date_cin',
        'matiere',
        'nombre_copie',
        'jours_surveillance',
        'jours_encours',
        'jours_ensalles',
        'taux_brut',
        'irsa',
        'taux_net'
    ];

    protected $casts = [
        'annee' => 'integer',
        'centre_id' => 'integer',
        'jours_travaille' => 'integer',
        'nombre_copie' => 'integer',
        'jours_surveillance' => 'integer',
        'jours_encours' => 'integer',
        'jours_ensalles' => 'integer',
        'taux_brut' => 'decimal:2',
        'irsa' => 'decimal:2',
        'taux_net' => 'decimal:2',
        'date_cin' => 'date'
    ];

    // Les rôles possibles
    const ROLES = [
        'PDO' => 'Président',
        'VPDO' => 'Vice Président',
        'CDC' => 'Chef de Centre',
        'CDCA' => 'Chef de Centre Adjoint',
        'secretaire' => 'Secrétaire',
        'secOrg' => 'Secrétaire d\'Organisation',
        'surveillance' => 'Surveillant',
        'securite' => 'Sécurité',
        'correcteur' => 'Correcteur'
    ];

    // Les situations possibles
    const SITUATIONS = [
        'Permanent',
        'Non Permanent'
    ];

    // Taux IRSA
    const TAUX_IRSA = 0.02; // 2%

    // Relation avec le centre
    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }

    // Accesseur pour obtenir le libellé du rôle
    public function getRoleLibelleAttribute()
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    // Méthode pour calculer le taux brut en fonction du rôle
    public function calculerTauxBrut()
    {
        switch ($this->role) {
            case 'PDO':
            case 'VPDO':
                return 150000;
            case 'CDC':
            case 'CDCA':
                return 130000;
            case 'secretaire':
            case 'secOrg':  // Même taux que secrétaire
                return ($this->jours_travaille ?? 0) * 9000;
            case 'surveillance':
            case 'securite':
                return ($this->jours_surveillance ?? 0) * 8000;
            case 'correcteur':
                $baseRate = 30000;
                $surplusCopies = max(0, ($this->nombre_copie ?? 0) - 100);
                $surplusRate = $this->matiere === 'BEP' ? 300 : 200;
                return $baseRate + ($surplusCopies * $surplusRate);
            default:
                return 0;
        }
    }

    // Méthode pour calculer l'IRSA
    public function calculerIRSA($tauxBrut)
    {
        return round($tauxBrut * self::TAUX_IRSA, 2);
    }

    // Méthode pour calculer le taux net
    public function calculerTauxNet($tauxBrut, $irsa)
    {
        return round($tauxBrut - $irsa, 2);
    }

    // Boot method pour calculer automatiquement les taux avant la sauvegarde
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($agent) {
            $tauxBrut = $agent->calculerTauxBrut();
            $irsa = $agent->calculerIRSA($tauxBrut);
            $tauxNet = $agent->calculerTauxNet($tauxBrut, $irsa);

            $agent->taux_brut = $tauxBrut;
            $agent->irsa = $irsa;
            $agent->taux_net = $tauxNet;
        });

        static::updating(function ($agent) {
            $tauxBrut = $agent->calculerTauxBrut();
            $irsa = $agent->calculerIRSA($tauxBrut);
            $tauxNet = $agent->calculerTauxNet($tauxBrut, $irsa);

            $agent->taux_brut = $tauxBrut;
            $agent->irsa = $irsa;
            $agent->taux_net = $tauxNet;
        });
    }

    // Ajouter les champs calculés dans la réponse JSON
    protected $appends = ['role_libelle'];
}
