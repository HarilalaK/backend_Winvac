<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TauxRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Taux forfaitaires
        DB::table('taux_roles')->insert([
            [
                'role' => 'PDO',
                'taux_forfaitaire' => 150000,
                'created_at' => now()
            ],
            [
                'role' => 'VPDO',
                'taux_forfaitaire' => 150000,
                'created_at' => now()
            ],
            [
                'role' => 'CDC',
                'taux_forfaitaire' => 130000,
                'created_at' => now()
            ],
            [
                'role' => 'CDCA',
                'taux_forfaitaire' => 130000,
                'created_at' => now()
            ],
        ]);

        // Taux journaliers
        DB::table('taux_roles')->insert([
            [
                'role' => 'Secretaire',
                'taux_journalier' => 8000,
                'created_at' => now()
            ],
            [
                'role' => 'SecOrg',
                'taux_journalier' => 8000,
                'created_at' => now()
            ],
            [
                'role' => 'Surveillance',
                'taux_journalier' => 9000,
                'created_at' => now()
            ],
            [
                'role' => 'Securite',
                'taux_journalier' => 9000,
                'created_at' => now()
            ],
        ]);

        // Taux correcteur (avec ses spécificités)
        // Taux base correcteur : 30000
        DB::table('taux_roles')->insert([
            [
                'role' => 'Correcteur',
                'taux_base_correcteur' => 30000,
                'taux_surplus_bep' => 300,
                'taux_surplus_autres' => 200,
                'created_at' => now()
            ],
        ]);
    }
} 