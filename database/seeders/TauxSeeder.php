<?php

namespace Database\Seeders;

use App\Models\Taux;
use Illuminate\Database\Seeder;

class TauxSeeder extends Seeder
{
    public function run(): void
    {
        Taux::create([
            'annee' => 2024,
            'secretariat' => 8000,
            'surveillance_securite' => 9000,
            'correction_max_copies' => 30000,
            'correction_surplus_bep' => 300,
            'correction_surplus_autre' => 200,
            'forfaitaire_pdo_vpdo' => 150000,
            'forfaitaire_cdc_cdca' => 130000
        ]);
    }
} 