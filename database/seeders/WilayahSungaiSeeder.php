<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WilayahSungai;

class WilayahSungaiSeeder extends Seeder
{
    public function run(): void
    {
        WilayahSungai::create([
            'kabupaten_id' => 1,
            'nama_sungai' => 'Sungai Tabanio',
            'kode_sungai' => 'WS-TABA-01',
            'deskripsi' => 'Aliran sungai utama di Kabupaten Tanah Laut',
            'aktif' => true,
        ]);

        WilayahSungai::create([
            'kabupaten_id' => 2,
            'nama_sungai' => 'Sungai Batulicin',
            'kode_sungai' => 'WS-BLIN-02',
            'deskripsi' => 'Aliran sungai utama di Kabupaten Tanah Bumbu',
            'aktif' => true,
        ]);

        WilayahSungai::create([
            'kabupaten_id' => 3,
            'nama_sungai' => 'Sungai Cantung',
            'kode_sungai' => 'WS-CANT-03',
            'deskripsi' => 'Aliran sungai utama di Kabupaten Kotabaru',
            'aktif' => true,
        ]);
    }
}
