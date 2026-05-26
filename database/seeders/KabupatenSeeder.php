<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KabupatenSeeder extends Seeder
{
    public function run(): void
    {
        $kabupaten = [
            ['nama' => 'Tanah Laut',  'kode' => 'TALA'],
            ['nama' => 'Tanah Bumbu', 'kode' => 'TANBU'],
            ['nama' => 'Kotabaru',    'kode' => 'KOTABARU'],
        ];

        foreach ($kabupaten as $item) {
            DB::table('kabupaten')->insert([
                'nama'       => $item['nama'],
                'kode'       => $item['kode'],
                'aktif'      => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
