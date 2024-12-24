<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'im' => 1001,
                'cin' => '101321456789',
                'nom_prenom' => 'Admin Principal',
                'date_cin' => '2020-01-01',
                'lieu_cin' => 'Antananarivo',
                'attribution' => 'Administration',
                'sexe' => 'M',
                'date_entree' => '2020-01-01',
                'statut' => 'Admin',
                'ref_st' => 1,
                'contact' => '0340000001',
                'password' => Hash::make('admin123'),
                'photo' => null
            ],
            [
                'im' => 2001,
                'cin' => '101321456790',
                'nom_prenom' => 'Directeur Regional',
                'date_cin' => '2020-01-02',
                'lieu_cin' => 'Antananarivo',
                'attribution' => 'Direction Régionale',
                'sexe' => 'M',
                'date_entree' => '2020-01-02',
                'statut' => 'DR',
                'ref_st' => 2,
                'contact' => '0340000002',
                'password' => Hash::make('dr123'),
                'photo' => null
            ],
            [
                'im' => 3001,
                'cin' => '101321456791',
                'nom_prenom' => 'Operateur Principal',
                'date_cin' => '2020-01-03',
                'lieu_cin' => 'Antananarivo',
                'attribution' => 'Opération',
                'sexe' => 'F',
                'date_entree' => '2020-01-03',
                'statut' => 'Operateur',
                'ref_st' => 3,
                'contact' => '0340000003',
                'password' => Hash::make('op123'),
                'photo' => null
            ]
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
} 