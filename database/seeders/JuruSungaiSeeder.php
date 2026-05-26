<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JuruSungai;
use Illuminate\Support\Facades\Hash;

class JuruSungaiSeeder extends Seeder
{
    public function run(): void
    {
        JuruSungai::create([
            'wilayah_sungai_id' => 1,
            'nama' => 'Ahmad Junaidi',
            'nip' => '198501012010121001',
            'username' => 'ahmad_juru',
            'password' => Hash::make('password'),
            'aktif' => true,
        ]);

        JuruSungai::create([
            'wilayah_sungai_id' => 2,
            'nama' => 'Bambang Setiawan',
            'nip' => '198805122013011002',
            'username' => 'bambang_juru',
            'password' => Hash::make('password'),
            'aktif' => true,
        ]);

        JuruSungai::create([
            'wilayah_sungai_id' => 3,
            'nama' => 'Candra Wijaya',
            'nip' => '199109202015031003',
            'username' => 'candra_juru',
            'password' => Hash::make('password'),
            'aktif' => true,
        ]);
    }
}
