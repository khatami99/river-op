<?php

namespace Tests;

use App\Models\Das;
use App\Models\ItemInspeksi;
use App\Models\JuruSungai;
use App\Models\LaporanOp;
use App\Models\StafDinas;
use App\Models\TemuanKondisi;
use App\Models\WilayahSungai;
use Database\Seeders\JenisBangunanSeeder;
use Database\Seeders\KabupatenSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Penyiapan data untuk pengujian.
 *
 * Master data jenis bangunan diambil dari seeder yang sungguhan, bukan dibuat
 * ulang di sini, supaya tes ikut menjaga agar seeder itu tetap sejalan dengan
 * blangko OP resmi.
 */
trait DataUji
{
    protected function siapkanMasterData(): void
    {
        $this->seed(KabupatenSeeder::class);
        $this->seed(JenisBangunanSeeder::class);
    }

    protected function buatDas(string $nama = 'DAS Uji'): Das
    {
        $wilayah = WilayahSungai::firstOrCreate(
            ['kode_sungai' => '04.10.B'],
            ['nama_sungai' => 'Wilayah Sungai Cengal-Batulicin', 'aktif' => true]
        );

        return Das::create([
            'wilayah_sungai_id' => $wilayah->id,
            'nama_das' => $nama,
            'kode_das' => 'DAS-' . Str::upper(Str::random(6)),
            'koordinat_lat' => -3.44,
            'koordinat_lng' => 115.97,
            'aktif' => true,
        ]);
    }

    protected function buatJuru(?Das $das = null, string $nama = 'Juru Uji'): JuruSungai
    {
        return JuruSungai::create([
            'das_id' => ($das ?? $this->buatDas())->id,
            'nama' => $nama,
            'nip' => (string) random_int(10 ** 17, 10 ** 18 - 1),
            'username' => 'juru_' . Str::lower(Str::random(6)),
            'password' => Hash::make('rahasia'),
            'aktif' => true,
        ]);
    }

    protected function buatStaf(string $role = 'admin'): StafDinas
    {
        return StafDinas::create([
            'nama' => Str::title($role) . ' Uji',
            'username' => $role . '_' . Str::lower(Str::random(6)),
            'password' => Hash::make('rahasia'),
            'role' => $role,
            'aktif' => true,
        ]);
    }

    /**
     * Satu laporan berisi satu bangunan dengan satu temuan. Dipakai tes yang
     * butuh data ada, tanpa peduli isinya.
     */
    protected function buatLaporan(JuruSungai $juru, array $atribut = [], bool $bermasalah = true): LaporanOp
    {
        $laporan = LaporanOp::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'juru_sungai_id' => $juru->id,
            'das_id' => $juru->das_id,
            'tanggal_inspeksi' => '2026-08-10',
            'bulan' => 8,
            'tahun' => 2026,
            'cuaca' => 'cerah',
            'debit_air' => 'normal',
            'sync_status' => 'synced',
            'synced_at' => now(),
        ], $atribut));

        $jenis = \App\Models\JenisBangunan::with('bagianBangunan')->orderBy('urutan')->first();

        $item = ItemInspeksi::create([
            'uuid' => (string) Str::uuid(),
            'laporan_op_id' => $laporan->id,
            'jenis_bangunan_id' => $jenis->id,
            'nama_bangunan' => 'Bangunan Uji',
            'nama_sungai' => 'Uji',
            'lokasi_km_dari' => 1.5,
            'lokasi_km_sampai' => 2,
            'koordinat_lat' => -3.44,
            'koordinat_lng' => 115.97,
        ]);

        $kondisi = $bermasalah ? ['Lereng Longsor'] : ['Normal'];

        TemuanKondisi::create([
            'item_inspeksi_id' => $item->id,
            'bagian_bangunan_id' => $jenis->bagianBangunan->first()->id,
            'kondisi_ditemukan' => $kondisi,
            'ada_masalah' => TemuanKondisi::nilaiAdaMasalah($kondisi),
        ]);

        return $laporan;
    }
}
