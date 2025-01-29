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
    }

    // Relations avec tous les types de détails
    public function pdoDetails()
    {
        return $this->hasOne(PdoDetail::class);
    }

    public function vpdoDetails()
    {
        return $this->hasOne(VpdoDetail::class);
    }

    public function cdcDetails()
    {
        return $this->hasOne(CdcDetail::class);
    }

    public function cdcaDetails()
    {
        return $this->hasOne(CdcaDetail::class);
    }

    public function secretaireDetails()
    {
        return $this->hasOne(SecretaireDetail::class);
    }

    public function secOrgDetails()
    {
        return $this->hasOne(SecOrgDetail::class);
    }

    public function surveillanceDetails()
    {
        return $this->hasOne(SurveillanceDetail::class);
    }

    public function securiteDetails()
    {
        return $this->hasOne(SecuriteDetail::class);
    }

    public function correcteurDetails()
    {
        return $this->hasOne(CorrecteurDetail::class);
    }

    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }

    // Méthode helper pour obtenir les détails selon le rôle
    public function getRoleDetails()
    {
        return match($this->role) {
            'PDO' => $this->pdoDetails,
            'VPDO' => $this->vpdoDetails,
            'CDC' => $this->cdcDetails,
            'CDCA' => $this->cdcaDetails,
            'secretaire' => $this->secretaireDetails,
            'secOrg' => $this->secOrgDetails,
            'surveillance' => $this->surveillanceDetails,
            'securite' => $this->securiteDetails,
            'correcteur' => $this->correcteurDetails,
            default => null
        };
    }
}