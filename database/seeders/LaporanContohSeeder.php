<?php

namespace Database\Seeders;

use App\Models\ItemInspeksi;
use App\Models\JuruSungai;
use App\Models\LaporanOp;
use App\Models\TemuanKondisi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Laporan CONTOH untuk menguji dashboard, rekapitulasi, dan peta sebaran.
 *
 * Bukan data sungguhan. Titiknya disebar di sekitar titik perkiraan DAS
 * masing-masing juru, jadi letaknya masuk akal di peta tetapi tidak mewakili
 * bangunan yang benar-benar ada di lapangan.
 *
 * Seeder ini MENGHAPUS seluruh isi laporan lebih dulu, karena tujuannya
 * menyiapkan data uji yang dapat diulang. Jangan dijalankan bila sudah ada
 * laporan sungguhan dari juru.
 */
class LaporanContohSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->warn('Dilewati: seeder contoh tidak dijalankan di produksi.');

            return;
        }

        DB::table('temuan_kondisi')->delete();
        DB::table('item_inspeksi')->delete();
        DB::table('laporan_op')->delete();

        // Hanya jenis bangunan yang bagiannya sudah terdaftar yang bisa dipakai,
        // sebab temuan kondisi selalu menempel pada bagian bangunan.
        $jenis = DB::table('jenis_bangunan')
            ->whereIn('id', DB::table('bagian_bangunan')->distinct()->pluck('jenis_bangunan_id'))
            ->where('aktif', 1)
            ->get();

        if ($jenis->isEmpty()) {
            $this->command->warn('Dilewati: belum ada jenis bangunan yang punya bagian bangunan.');

            return;
        }

        $bagianPerJenis = DB::table('bagian_bangunan')->where('aktif', 1)->get()->groupBy('jenis_bangunan_id');
        $kondisiPerBagian = DB::table('pilihan_kondisi')->where('aktif', 1)->get()->groupBy('bagian_bangunan_id');

        $juruSungai = JuruSungai::with('das')->where('aktif', true)->get()
            ->filter(fn ($j) => $j->das?->punyaKoordinat());

        if ($juruSungai->isEmpty()) {
            $this->command->warn('Dilewati: belum ada juru pada DAS yang punya titik.');

            return;
        }

        // Diacak dengan benih tetap supaya hasilnya sama tiap kali dijalankan.
        mt_srand(20260907);

        $cuaca = ['cerah', 'berawan', 'hujan'];
        $debit = ['rendah', 'normal', 'tinggi', 'banjir'];
        $penanda = ['Hulu', 'Muara', 'Jembatan', 'Kampung', 'Bendung', 'Simpang'];
        $jumlah = ['laporan' => 0, 'bangunan' => 0, 'bermasalah' => 0];

        foreach ($juruSungai as $juru) {
            $das = $juru->das;

            foreach ([6, 7, 8, 9] as $bulan) {
                // Sebagian juru sengaja tidak melapor pada bulan tertentu, agar
                // daftar "juru belum melapor" pada rekapitulasi ada isinya.
                if (mt_rand(1, 10) === 1) {
                    continue;
                }

                $hari = mt_rand(3, 26);
                $tanggal = sprintf('2026-%02d-%02d', $bulan, $hari);

                $laporan = LaporanOp::create([
                    'uuid' => (string) Str::uuid(),
                    'juru_sungai_id' => $juru->id,
                    'das_id' => $das->id,
                    'tanggal_inspeksi' => $tanggal,
                    'bulan' => $bulan,
                    'tahun' => 2026,
                    'cuaca' => $cuaca[array_rand($cuaca)],
                    'debit_air' => $debit[array_rand($debit)],
                    'catatan_umum' => 'Data contoh untuk pengujian sistem.',
                    'kendala' => mt_rand(1, 4) === 1 ? 'Akses jalan menuju lokasi becek.' : null,
                    'sync_status' => 'synced',
                    'dibuat_di_hp' => $tanggal . ' 08:' . sprintf('%02d', mt_rand(0, 59)) . ':00',
                    'synced_at' => $tanggal . ' 16:' . sprintf('%02d', mt_rand(0, 59)) . ':00',
                ]);

                $jumlah['laporan']++;

                foreach (range(1, mt_rand(2, 4)) as $urutan) {
                    $jenisTerpilih = $jenis->random();
                    $bagian = $bagianPerJenis[$jenisTerpilih->id] ?? collect();

                    // Titik disebar sekitar 5 km di sekeliling titik DAS.
                    $km = mt_rand(0, 24) + mt_rand(0, 9) / 10;

                    $item = ItemInspeksi::create([
                        'uuid' => (string) Str::uuid(),
                        'laporan_op_id' => $laporan->id,
                        'jenis_bangunan_id' => $jenisTerpilih->id,
                        'nama_bangunan' => $jenisTerpilih->nama . ' ' . $penanda[array_rand($penanda)]
                            . ' ' . Str::after($das->nama_das, 'DAS '),
                        'lokasi_km_dari' => $km,
                        'lokasi_km_sampai' => $km + mt_rand(1, 8) / 10,
                        'koordinat_lat' => round($das->koordinat_lat + mt_rand(-450, 450) / 10000, 7),
                        'koordinat_lng' => round($das->koordinat_lng + mt_rand(-450, 450) / 10000, 7),
                        'tipe_konstruksi' => $jenisTerpilih->tipe_konstruksi,
                        'catatan' => null,
                    ]);

                    $jumlah['bangunan']++;

                    foreach ($bagian as $b) {
                        $pilihan = $kondisiPerBagian[$b->id] ?? collect();

                        // Sekitar sepertiga bagian dibuat bermasalah, sisanya normal,
                        // supaya warna penanda dan angka rekapitulasi ada variasinya.
                        $bermasalah = $pilihan->isNotEmpty() && mt_rand(1, 3) === 1;

                        $kondisi = $bermasalah
                            ? [$pilihan->random()->kondisi]
                            : ['Normal'];

                        TemuanKondisi::create([
                            'item_inspeksi_id' => $item->id,
                            'bagian_bangunan_id' => $b->id,
                            'kondisi_ditemukan' => $kondisi,
                            'ada_masalah' => TemuanKondisi::nilaiAdaMasalah($kondisi),
                            'tindakan_diperlukan' => $bermasalah ? 'Perlu perbaikan ringan.' : null,
                            'keterangan' => null,
                        ]);

                        if ($bermasalah) {
                            $jumlah['bermasalah']++;
                        }
                    }
                }
            }
        }

        $this->command->info(sprintf(
            'Laporan contoh: %d laporan, %d bangunan, %d temuan bermasalah.',
            $jumlah['laporan'],
            $jumlah['bangunan'],
            $jumlah['bermasalah']
        ));
    }
}
