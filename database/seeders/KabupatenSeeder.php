<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seluruh kabupaten dan kota Kalimantan Selatan, bukan hanya yang kebetulan
 * disinggung wilayah sungai yang sudah terdaftar.
 *
 * Alasannya: tidak ada layar untuk menambah kabupaten, dan memang tidak perlu
 * ada — pembagian wilayah ini ditetapkan undang-undang dan berubah sekali
 * dalam satu dekade. Yang tidak boleh terjadi adalah admin membuka formulir
 * wilayah sungai baru di Tabalong lalu menemukan Tabalong tidak ada di sana
 * tanpa jalan apa pun untuk mengadakannya.
 */
class KabupatenSeeder extends Seeder
{
    public function run(): void
    {
        $kabupaten = [
            ['nama' => 'Tanah Laut',          'kode' => 'TALA'],
            ['nama' => 'Kotabaru',            'kode' => 'KOTABARU'],
            ['nama' => 'Banjar',              'kode' => 'BANJAR'],
            ['nama' => 'Barito Kuala',        'kode' => 'BATOLA'],
            ['nama' => 'Tapin',               'kode' => 'TAPIN'],
            ['nama' => 'Hulu Sungai Selatan', 'kode' => 'HSS'],
            ['nama' => 'Hulu Sungai Tengah',  'kode' => 'HST'],
            ['nama' => 'Hulu Sungai Utara',   'kode' => 'HSU'],
            ['nama' => 'Tabalong',            'kode' => 'TABALONG'],
            ['nama' => 'Tanah Bumbu',         'kode' => 'TANBU'],
            ['nama' => 'Balangan',            'kode' => 'BALANGAN'],

            // Kolom kode selebar sepuluh huruf, jadi dua kota memakai
            // singkatan yang memang sudah lazim dipakai orang di sini.
            ['nama' => 'Kota Banjarmasin',    'kode' => 'BJM'],
            ['nama' => 'Kota Banjarbaru',     'kode' => 'BJB'],
        ];

        // Dicocokkan lewat kode, bukan disisipkan begitu saja. Migrasi yang
        // menambahkan sepuluh kabupaten menyusul juga mengisi tabel ini,
        // sehingga pemasangan yang bermigrasi lalu menyemai akan menabrak
        // kunci unik — dan seeder daftar rujukan memang seharusnya boleh
        // dijalankan berulang tanpa merusak apa pun.
        foreach ($kabupaten as $item) {
            DB::table('kabupaten')->updateOrInsert(
                ['kode' => $item['kode']],
                [
                    'nama'       => $item['nama'],
                    'aktif'      => true,
                    'updated_at' => now(),
                ]
            );
        }
    }
}
