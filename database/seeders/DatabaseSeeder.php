<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            KabupatenSeeder::class,
            JenisBangunanSeeder::class,
            WilayahSungaiSeeder::class,
            JuruSungaiSeeder::class,
            StafDinasSeeder::class,
        ]);
    }
}
