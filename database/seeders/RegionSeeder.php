<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            // Antananarivo (ID: 1)
            [
                'nom' => 'Analamanga',
                'province_id' => 1
            ],
            [
                'nom' => 'Bongolava',
                'province_id' => 1
            ],
            [
                'nom' => 'Itasy',
                'province_id' => 1
            ],
            [
                'nom' => 'Vakinankaratra',
                'province_id' => 1
            ],

            // Antsiranana (ID: 2)
            [
                'nom' => 'Diana',
                'province_id' => 2
            ],
            [
                'nom' => 'Sava',
                'province_id' => 2
            ],

            // Fianarantsoa (ID: 3)
            [
                'nom' => 'Amoron\'i Mania',
                'province_id' => 3
            ],
            [
                'nom' => 'Atsimo-Atsinanana',
                'province_id' => 3
            ],
            [
                'nom' => 'Haute Matsiatra',
                'province_id' => 3
            ],
            [
                'nom' => 'Ihorombe',
                'province_id' => 3
            ],
            [
                'nom' => 'Vatovavy',
                'province_id' => 3
            ],
            [
                'nom' => 'Fitovinany',
                'province_id' => 3
            ],

            // Mahajanga (ID: 4)
            [
                'nom' => 'Betsiboka',
                'province_id' => 4
            ],
            [
                'nom' => 'Boeny',
                'province_id' => 4
            ],
            [
                'nom' => 'Melaky',
                'province_id' => 4
            ],
            [
                'nom' => 'Sofia',
                'province_id' => 4
            ],

            // Toamasina (ID: 5)
            [
                'nom' => 'Alaotra-Mangoro',
                'province_id' => 5
            ],
            [
                'nom' => 'Analanjirofo',
                'province_id' => 5
            ],
            [
                'nom' => 'Atsinanana',
                'province_id' => 5
            ],

            // Toliara (ID: 6)
            [
                'nom' => 'Androy',
                'province_id' => 6
            ],
            [
                'nom' => 'Anosy',
                'province_id' => 6
            ],
            [
                'nom' => 'Atsimo-Andrefana',
                'province_id' => 6
            ],
            [
                'nom' => 'Menabe',
                'province_id' => 6
            ]
        ];

        foreach ($regions as $region) {
            Region::create($region);
        }
    }
} 