<?php

namespace Database\Seeders;

use App\Models\Das;
use Illuminate\Database\Seeder;

/**
 * DAS adalah satuan wilayah kerja yang dipegang seorang juru sungai.
 * Seluruhnya berada di bawah Wilayah Sungai Cengal.
 */
class DasSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = [
            ['nama_das' => 'Sungai Tabanio',   'kode_das' => 'DAS-TABA-01', 'deskripsi' => 'Aliran sungai utama di Kabupaten Tanah Laut'],
            ['nama_das' => 'Sungai Batulicin', 'kode_das' => 'DAS-BLIN-02', 'deskripsi' => 'Aliran sungai utama di Kabupaten Tanah Bumbu'],
            ['nama_das' => 'Sungai Cantung',   'kode_das' => 'DAS-CANT-03', 'deskripsi' => 'Aliran sungai utama di Kabupaten Kotabaru'],
        ];

        foreach ($daftar as $item) {
            Das::create($item + [
                'wilayah_sungai_id' => 1,
                'aktif' => true,
            ]);
        }
    }
}
