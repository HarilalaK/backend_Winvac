<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

     protected $fillable = [
        'date_op',
        'operateur',
        'operations',
        'cin',
        'nom_prenom',
        'autres',
    ];
}
