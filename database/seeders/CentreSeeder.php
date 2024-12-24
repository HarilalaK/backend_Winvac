<?php

namespace Database\Seeders;

use App\Models\Centre;
use Illuminate\Database\Seeder;

class CentreSeeder extends Seeder
{
    public function run(): void
    {
        $centres = [
            // Centres dans Analamanga (ID: 1)
            [
                'nom' => 'LTP Alarobia',
                'type' => 'examen',
                'region_id' => 1,
                'nombre_salles' => 10,
                'nombre_candidats' => 200,
                'numero_centre' => 'BEP001',
                'type_examen' => 'BEP',
                'session' => 2024
            ],
            [
                'nom' => 'LTP Ampefiloha',
                'type' => 'examen',
                'region_id' => 1,
                'nombre_salles' => 8,
                'nombre_candidats' => 160,
                'numero_centre' => 'CAP001',
                'type_examen' => 'CAP',
                'session' => 2024
            ],

            // Centres dans Vakinankaratra (ID: 4)
            [
                'nom' => 'CFP Antsirabe',
                'type' => 'examen',
                'region_id' => 4,
                'nombre_salles' => 12,
                'nombre_candidats' => 240,
                'numero_centre' => 'CFA001',
                'type_examen' => 'CFA',
                'session' => 2024
            ],

            // Centres dans Diana (ID: 5)
            [
                'nom' => 'LTP Antsiranana',
                'type' => 'examen',
                'region_id' => 5,
                'nombre_salles' => 15,
                'nombre_candidats' => 300,
                'numero_centre' => 'LTP001',
                'type_examen' => 'ConcoursLTP',
                'session' => 2024
            ],

            // Centres dans Haute Matsiatra (ID: 9)
            [
                'nom' => 'CFP Fianarantsoa',
                'type' => 'examen',
                'region_id' => 9,
                'nombre_salles' => 6,
                'nombre_candidats' => 120,
                'numero_centre' => 'CFP001',
                'type_examen' => 'ConcoursCFP',
                'session' => 2024
            ],

            // Centres dans Boeny (ID: 14)
            [
                'nom' => 'LTP Mahajanga',
                'type' => 'examen',
                'region_id' => 14,
                'nombre_salles' => 8,
                'nombre_candidats' => 160,
                'numero_centre' => 'BEP002',
                'type_examen' => 'BEP',
                'session' => 2024
            ],

            // Centres dans Atsinanana (ID: 19)
            [
                'nom' => 'CFP Toamasina',
                'type' => 'examen',
                'region_id' => 19,
                'nombre_salles' => 10,
                'nombre_candidats' => 200,
                'numero_centre' => 'CAP002',
                'type_examen' => 'CAP',
                'session' => 2024
            ],

            // Centres dans Atsimo-Andrefana (ID: 22)
            [
                'nom' => 'LTP Toliara',
                'type' => 'examen',
                'region_id' => 22,
                'nombre_salles' => 7,
                'nombre_candidats' => 140,
                'numero_centre' => 'CFA002',
                'type_examen' => 'CFA',
                'session' => 2024
            ]
        ];

        foreach ($centres as $centre) {
            Centre::create($centre);
        }
    }
} 