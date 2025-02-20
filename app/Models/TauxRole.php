<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TauxRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'role',
        'taux_forfaitaire',
        'taux_journalier',
        'taux_base_correcteur',
        'taux_surplus_bep',
        'taux_surplus_autres',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'taux_forfaitaire' => 'decimal:2',
        'taux_journalier' => 'decimal:2',
        'taux_base_correcteur' => 'decimal:2',
        'taux_surplus_bep' => 'decimal:2',
        'taux_surplus_autres' => 'decimal:2'
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

    public function calculateTaux($role, $params = [])
    {
        switch ($role) {
            case 'PDO':
            case 'VPDO':
            case 'CDC':
            case 'CDCA':
                return $this->taux_forfaitaire;

            case 'Secretaire':
            case 'SecOrg':
            case 'Surveillance':
            case 'Securite':
                return $this->taux_journalier * ($params['jours'] ?? 1);

            case 'Correcteur':
                $nombreCopies = $params['nombre_copie'] ?? 0;
                $typeExamen = $params['type_examen'] ?? 'Autres';
                
                if ($nombreCopies <= 100) {
                    return $this->taux_base_correcteur;
                } else {
                    $surplusCopies = $nombreCopies - 100;
                    $tauxSurplus = $typeExamen === 'BEP' ? $this->taux_surplus_bep : $this->taux_surplus_autres;
                    return $this->taux_base_correcteur + ($surplusCopies * $tauxSurplus);
                }

            default:
                return 0;
        }
    }
} 