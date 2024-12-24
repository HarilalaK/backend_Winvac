<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProvinceSeeder::class,
            RegionSeeder::class,
            CentreSeeder::class,
            MatiereSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
        ]);
    }
}
