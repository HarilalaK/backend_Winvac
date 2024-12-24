<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prix',
        'requiert_jours_travaille',
        'requiert_jours_surveillance',
        'requiert_copies',
        'requiert_matiere'
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'requiert_jours_travaille' => 'boolean',
        'requiert_jours_surveillance' => 'boolean',
        'requiert_copies' => 'boolean',
        'requiert_matiere' => 'boolean'
    ];

    public function agents()
    {
        return $this->hasMany(Agent::class);
    }
} 