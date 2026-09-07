<?php

namespace Database\Seeders;

use App\Models\WilayahSungai;
use Illuminate\Database\Seeder;

/**
 * Hanya ada satu wilayah sungai yang menjadi wewenang Provinsi Kalimantan
 * Selatan, yaitu WS Cengal-Batulicin. Nama dan kodenya mengikuti Peta Wilayah
 * Sungai Cengal-Batulicin (Lampiran V.87, Kode WS 04.10.B).
 *
 * Meski sekarang hanya satu, wilayah sungai tetap disimpan sebagai data biasa
 * supaya wilayah sungai baru cukup ditambahkan lewat menu, tanpa ubah kode.
 */
class WilayahSungaiSeeder extends Seeder
{
    public function run(): void
    {
        WilayahSungai::updateOrCreate(
            ['kode_sungai' => '04.10.B'],
            [
                'nama_sungai' => 'Wilayah Sungai Cengal-Batulicin',
                'deskripsi' => 'Satu-satunya wilayah sungai yang menjadi wewenang Provinsi '
                    . 'Kalimantan Selatan. Membentang di Kabupaten Kotabaru, Tanah Bumbu, '
                    . 'dan Tanah Laut.',
                'aktif' => true,
            ]
        );
    }
}
