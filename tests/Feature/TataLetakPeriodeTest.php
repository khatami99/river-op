<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DataUji;
use Tests\TestCase;

class TataLetakPeriodeTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    private function halaman(string $alamat): string
    {
        return $this->actingAs($this->buatStaf('admin'), 'staf')
            ->get($alamat)->assertOk()->getContent();
    }

    private function potong(string $html, string $awal, string $akhir): string
    {
        $a = strpos($html, $awal);
        $b = strpos($html, $akhir, $a);

        return substr($html, $a, $b - $a);
    }

    public function test_pemilih_periode_berada_di_badan_bukan_di_kop(): void
    {
        foreach (['/dashboard', '/rekap'] as $alamat) {
            $html = $this->halaman($alamat);

            $kop = $this->potong($html, '<header', '<main');
            $badan = substr($html, strpos($html, '<main'));

            $this->assertStringNotContainsString('name="bulan"', $kop, "Periode masih di kop {$alamat}");
            $this->assertStringContainsString('name="bulan"', $badan, "Periode hilang dari badan {$alamat}");
            $this->assertStringContainsString('name="tahun"', $badan);
        }
    }

    public function test_export_rekap_tetap_di_kop_dan_terpisah_dari_periode(): void
    {
        $html = $this->halaman('/rekap');
        $kop = $this->potong($html, '<header', '<main');

        $this->assertStringContainsString('Export Rekap', $kop);
        $this->assertStringNotContainsString('Export Rekap', substr($html, strpos($html, '<main')));
    }

    public function test_periode_masih_mengubah_isi_halaman(): void
    {
        $juru = $this->buatJuru();
        $this->buatLaporan($juru, ['tanggal_inspeksi' => '2026-08-05', 'bulan' => 8, 'tahun' => 2026]);

        $this->assertStringContainsString('Agustus 2026', $this->halaman('/dashboard?bulan=8&tahun=2026'));
        $this->assertStringContainsString('Maret 2026', $this->halaman('/rekap?bulan=3&tahun=2026'));
    }
}
