<?php

namespace Database\Seeders;

use App\Models\Das;
use App\Models\WilayahSungai;
use Illuminate\Database\Seeder;

/**
 * Ke-62 DAS di bawah WS Cengal-Batulicin, sesuai daftar "Nama DAS" pada Peta
 * Wilayah Sungai Cengal-Batulicin (Lampiran V.87, Kode WS 04.10.B). Nomor
 * 001-062 mengikuti penomoran resmi di peta tersebut.
 *
 * Koordinat hanya diisi untuk DAS yang letaknya dapat ditentukan dengan cukup
 * yakin, dan sifatnya PERKIRAAN dengan ketelitian sekitar sepuluh kilometer.
 * DAS adalah wilayah, bukan titik: titik ini semata-mata pegangan agar DAS
 * dapat ditandai di peta. Sisanya sengaja dibiarkan kosong daripada ditebak,
 * dan dapat diisi admin dengan mengklik peta pada formulir DAS.
 *
 * Kabupaten tidak diisi di sini. Batas DAS mengikuti punggung bukit sehingga
 * belum tentu berhenti di batas kabupaten, dan menebaknya dari peta skala
 * 1:800.000 lebih berisiko daripada membiarkannya kosong untuk diisi orang
 * yang memang tahu.
 */
class DasSeeder extends Seeder
{
    public function run(): void
    {
        $wilayah = WilayahSungai::where('kode_sungai', '04.10.B')->firstOrFail();

        // [nomor, nama, lat, lng] — lat/lng null berarti belum ditentukan.
        $daftar = [
            [1, 'Cengal', -2.32, 116.02],      [2, 'Manunggal', null, null],
            [3, 'Sampanahan', -2.62, 116.10],  [4, 'Hanyar', null, null],
            [5, 'Sebuli', null, null],         [6, 'Senakin', -2.86, 116.18],
            [7, 'Tanjung Sela', null, null],   [8, 'Penggawa', null, null],
            [9, 'Tamiang', -2.98, 116.28],     [10, 'Sangsang', null, null],
            [11, 'Bungkukan', null, null],     [12, 'Selatu', null, null],
            [13, 'Bengkalaan', null, null],    [14, 'Bandilan', null, null],
            [15, 'Cantung', -2.95, 115.88],    [16, 'Bendo', null, null],
            [17, 'Serongga', -3.16, 116.03],   [18, 'Terusan', null, null],
            [19, 'Setangga', null, null],      [20, 'Buluh', null, null],
            [21, 'Dua', null, null],           [22, 'Samariti', null, null],
            [23, 'Hanau', null, null],         [24, 'Batulicin', -3.44, 115.97],
            [25, 'Segumbang', null, null],     [26, 'Kusan', -3.58, 115.75],
            [27, 'Lembu', null, null],         [28, 'Bantai', null, null],
            [29, 'Betung', null, null],        [30, 'Lawangan', null, null],
            [31, 'Lokan', null, null],         [32, 'Bakau', null, null],
            [33, 'Dua Laut', null, null],      [34, 'Dua Pumpung', null, null],
            [35, 'Panyulingan', null, null],   [36, 'Kandang Haur', null, null],
            [37, 'Sebamban', -3.66, 115.62],   [38, 'Bunati', -3.70, 115.58],
            [39, 'Angsana', -3.74, 115.50],    [40, 'Setarap', null, null],
            [41, 'Cuka', null, null],          [42, 'Satui', -3.77, 115.40],
            [43, 'Duri', null, null],          [44, 'Tengah', null, null],
            [45, 'Bekarangan', null, null],    [46, 'Kintap', -3.85, 115.23],
            [47, 'Pandansari', null, null],    [48, 'Rakin', null, null],
            [49, 'Pampang', null, null],       [50, 'Asam-Asam', -3.92, 115.05],
            [51, 'Pabaungan', null, null],     [52, 'Danau', null, null],
            [53, 'Swarangan', -3.97, 114.86],  [54, 'Pandan', null, null],
            [55, 'Sebuhur', null, null],       [56, 'Seponggar', null, null],
            [57, 'Senipah', null, null],       [58, 'Cabe', null, null],
            [59, 'Batakan', null, null],       [60, 'Tambangan', null, null],
            [61, 'Anyar', null, null],         [62, 'Talok', -4.05, 114.72],
        ];

        foreach ($daftar as [$nomor, $nama, $lat, $lng]) {
            Das::updateOrCreate(
                ['kode_das' => sprintf('DAS-%03d', $nomor)],
                [
                    'wilayah_sungai_id' => $wilayah->id,
                    'nama_das' => 'DAS ' . $nama,
                    'deskripsi' => sprintf(
                        'DAS nomor %03d pada Peta Wilayah Sungai Cengal-Batulicin.',
                        $nomor
                    ),
                    'koordinat_lat' => $lat,
                    'koordinat_lng' => $lng,
                    'aktif' => true,
                ]
            );
        }
    }
}
