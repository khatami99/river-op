<?php

namespace Database\Seeders;

use App\Models\Das;
use App\Models\JuruSungai;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Juru sungai contoh, ditempatkan pada DAS yang sudah punya titik perkiraan
 * supaya laporan mereka bisa terlihat menyebar di peta. Dicari lewat kode DAS,
 * bukan id, agar tidak bergantung pada urutan penyisipan.
 */
class JuruSungaiSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = [
            ['DAS-003', 'Ahmad Junaidi',    '081234567001', 'ahmad_juru'],
            ['DAS-015', 'Bambang Setiawan', '081234567002', 'bambang_juru'],
            ['DAS-024', 'Candra Wijaya',    '081234567003', 'candra_juru'],
            ['DAS-026', 'Dedi Rahmatullah', '081234567004', 'dedi_juru'],
            ['DAS-042', 'Eko Saputra',      '081234567005', 'eko_juru'],
            ['DAS-046', 'Fajar Nugroho',    '081234567006', 'fajar_juru'],
        ];

        foreach ($daftar as [$kodeDas, $nama, $telepon, $username]) {
            $das = Das::where('kode_das', $kodeDas)->first();

            if (! $das) {
                continue;
            }

            JuruSungai::updateOrCreate(
                ['username' => $username],
                [
                    'das_id' => $das->id,
                    'nama' => $nama,
                    'no_telepon' => $telepon,
                    'nomor_sk' => '800/SK-JURU/PUPR/2026',
                    'password' => Hash::make('password'),
                    'aktif' => true,
                ]
            );
        }
    }
}
