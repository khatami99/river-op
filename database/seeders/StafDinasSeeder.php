<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StafDinasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('staf_dinas')->insert([
            'nama'       => 'Administrator',
            'username'   => 'admin',
            'password'   => Hash::make('admin123'),
            'role'       => 'admin',
            'aktif'      => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
