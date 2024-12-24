<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nom' => 'Président',
                'prix' => 150000.00,
                'requiert_jours_travaille' => false,
                'requiert_jours_surveillance' => false,
                'requiert_copies' => false,
                'requiert_matiere' => false
            ],
            [
                'nom' => 'Président Adjoint',
                'prix' => 120000.00,
                'requiert_jours_travaille' => false,
                'requiert_jours_surveillance' => false,
                'requiert_copies' => false,
                'requiert_matiere' => false
            ],
            [
                'nom' => 'CDC',
                'prix' => 100000.00,
                'requiert_jours_travaille' => false,
                'requiert_jours_surveillance' => false,
                'requiert_copies' => false,
                'requiert_matiere' => false
            ],
            [
                'nom' => 'Secrétaire',
                'prix' => 5000.00,
                'requiert_jours_travaille' => true,
                'requiert_jours_surveillance' => true,
                'requiert_copies' => false,
                'requiert_matiere' => false
            ],
            [
                'nom' => 'Surveillant',
                'prix' => 4000.00,
                'requiert_jours_travaille' => false,
                'requiert_jours_surveillance' => true,
                'requiert_copies' => false,
                'requiert_matiere' => false
            ],
            [
                'nom' => 'Correcteur',
                'prix' => 50000.00,
                'requiert_jours_travaille' => false,
                'requiert_jours_surveillance' => false,
                'requiert_copies' => true,
                'requiert_matiere' => true
            ]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
} 