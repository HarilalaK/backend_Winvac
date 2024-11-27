<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Taux extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'taux';

    protected $fillable = [
        'annee',
        'secretariat',
        'surveillance_securite',
        'correction_max_copies',
        'correction_surplus_bep',
        'correction_surplus_autre',
        'forfaitaire_pdo_vpdo',
        'forfaitaire_cdc_cdca'
    ];

    protected $casts = [
        'annee' => 'integer',
        'secretariat' => 'decimal:2',
        'surveillance_securite' => 'decimal:2',
        'correction_max_copies' => 'decimal:2',
        'correction_surplus_bep' => 'decimal:2',
        'correction_surplus_autre' => 'decimal:2',
        'forfaitaire_pdo_vpdo' => 'decimal:2',
        'forfaitaire_cdc_cdca' => 'decimal:2'
    ];
} 