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
        'jours_ensalles'
    ];

    protected $casts = [
        'date_cin' => 'date',
        'annee' => 'integer',
    ];

    public function centre()
    {
        return $this->belongsTo(Centre::class);
    }
}
