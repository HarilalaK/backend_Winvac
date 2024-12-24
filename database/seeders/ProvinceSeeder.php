<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            ['nom' => 'Antananarivo'],
            ['nom' => 'Antsiranana'],
            ['nom' => 'Fianarantsoa'],
            ['nom' => 'Mahajanga'],
            ['nom' => 'Toamasina'],
            ['nom' => 'Toliara']
        ];

        foreach ($provinces as $province) {
            Province::create($province);
        }
    }
} 