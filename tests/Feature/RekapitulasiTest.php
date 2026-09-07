<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Angka rekapitulasi. Ini yang dibaca kepala dinas dan dipakai menagih juru,
 * jadi salah hitung di sini berakibat nyata pada orang.
 *
 * Sengaja menguji angkanya, bukan sekadar halamannya menjawab 200.
 */
class RekapitulasiTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    /** Dua DAS: satu jurunya melapor, satu lagi tidak. */
    private function siapkanKeadaan(): array
    {
        $dasA = $this->buatDas('DAS Sudah');
        $dasB = $this->buatDas('DAS Belum');

        $juruA = $this->buatJuru($dasA, 'Juru Rajin');
        $juruB = $this->buatJuru($dasB, 'Juru Absen');

        $this->buatLaporan($juruA, bermasalah: true);
        $this->buatLaporan($juruA, ['tanggal_inspeksi' => '2026-08-20'], bermasalah: false);

        return [$dasA, $dasB, $juruA, $juruB];
    }

    public function test_halaman_rekap_menampilkan_yang_sudah_dan_belum_melapor(): void
    {
        [, , , $juruB] = $this->siapkanKeadaan();
        $staf = $this->buatStaf('viewer');

        $halaman = $this->actingAs($staf, 'staf')->get('/rekap?bulan=8&tahun=2026')->assertOk();

        $halaman->assertSee('DAS Sudah');
        $halaman->assertSee('DAS Belum');
        $halaman->assertSee($juruB->nama);
    }

    public function test_angka_export_rekap_sesuai_datanya(): void
    {
        $this->siapkanKeadaan();
        $staf = $this->buatStaf('admin');

        $isi = $this->actingAs($staf, 'staf')
            ->get('/rekap/export?bulan=8&tahun=2026')
            ->assertOk()
            ->getContent();

        $berkas = tempnam(sys_get_temp_dir(), 'rekap') . '.xlsx';
        file_put_contents($berkas, $isi);

        $lembar = IOFactory::load($berkas)->getActiveSheet();

        $total = null;
        $adaDaftarBelum = false;

        for ($r = 1; $r <= $lembar->getHighestRow(); $r++) {
            $nilai = $lembar->getCell('A' . $r)->getValue();

            if ($nilai === 'TOTAL') {
                $total = [
                    'juru' => (int) $lembar->getCell('B' . $r)->getValue(),
                    'sudah' => (int) $lembar->getCell('C' . $r)->getValue(),
                    'belum' => (int) $lembar->getCell('D' . $r)->getValue(),
                    'laporan' => (int) $lembar->getCell('E' . $r)->getValue(),
                    'bangunan' => (int) $lembar->getCell('F' . $r)->getValue(),
                    'bermasalah' => (int) $lembar->getCell('G' . $r)->getValue(),
                ];
            }

            if ($nilai === 'DAFTAR JURU BELUM MELAPOR') {
                $adaDaftarBelum = true;
            }
        }

        unlink($berkas);

        $this->assertNotNull($total, 'Baris TOTAL harus ada pada berkas rekap.');
        $this->assertSame(2, $total['juru']);
        $this->assertSame(1, $total['sudah']);
        $this->assertSame(1, $total['belum']);
        $this->assertSame(2, $total['laporan']);
        $this->assertSame(2, $total['bangunan']);

        // Dua laporan, satu bermasalah dan satu normal.
        $this->assertSame(1, $total['bermasalah']);

        $this->assertTrue($adaDaftarBelum, 'Nama juru yang belum melapor harus ikut ter-export.');
    }

    public function test_periode_lain_tidak_ikut_terhitung(): void
    {
        [, , $juruA] = $this->siapkanKeadaan();
        $this->buatLaporan($juruA, [
            'tanggal_inspeksi' => '2026-07-05', 'bulan' => 7,
        ]);

        $staf = $this->buatStaf('admin');

        $isi = $this->actingAs($staf, 'staf')
            ->get('/rekap/export?bulan=8&tahun=2026')
            ->getContent();

        $berkas = tempnam(sys_get_temp_dir(), 'rekap') . '.xlsx';
        file_put_contents($berkas, $isi);
        $lembar = IOFactory::load($berkas)->getActiveSheet();

        for ($r = 1; $r <= $lembar->getHighestRow(); $r++) {
            if ($lembar->getCell('A' . $r)->getValue() === 'TOTAL') {
                $this->assertSame(2, (int) $lembar->getCell('E' . $r)->getValue(),
                    'Laporan bulan Juli tidak boleh ikut terhitung pada rekap Agustus.');
                break;
            }
        }

        unlink($berkas);
    }
}
