<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WilayahSungai;

/**
 * Hanya ada satu wilayah sungai yang menjadi wewenang Provinsi Kalimantan
 * Selatan, yaitu Wilayah Sungai Cengal. Pembagian tugas juru sungai dilakukan
 * per DAS di bawahnya, lihat DasSeeder.
 */
class WilayahSungaiSeeder extends Seeder
{
    public function run(): void
    {
        WilayahSungai::create([
            'kabupaten_id' => 1,
            'nama_sungai' => 'Wilayah Sungai Cengal',
            'kode_sungai' => 'WS-CENGAL',
            'deskripsi' => 'Satu-satunya wilayah sungai yang menjadi wewenang Provinsi Kalimantan Selatan.',
            'aktif' => true,
        ]);
    }
}
