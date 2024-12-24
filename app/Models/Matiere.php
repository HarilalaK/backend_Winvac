<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matiere extends Model
{
    protected $fillable = [
        'num_matiere',
        'code',
        'designation',
        'BEP',
        'CAP',
        'CFA',
        'ConcoursLTP',
        'ConcoursCFP'
    ];

    protected $casts = [
        'BEP' => 'boolean',
        'CAP' => 'boolean',
        'CFA' => 'boolean',
        'ConcoursLTP' => 'boolean',
        'ConcoursCFP' => 'boolean'
    ];
}
