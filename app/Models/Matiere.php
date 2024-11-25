<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    use HasFactory;

    // Indique les colonnes qui peuvent être assignées en masse
    protected $fillable = [
        'Nmat',
        'code',
        'designation',
        'BEP',
        'CFA',
        'CAP',
        'ConcoursLTP',
        'ConcoursCFP',
        'Observations',
    ];
}
