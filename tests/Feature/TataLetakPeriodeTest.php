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

    /**
     * Uji ini dulu berbunyi terbalik: tombol unduh dijaga TETAP di kop. Alasannya
     * waktu itu ia tindakan, bukan kendali, jadi tidak ikut pindah bersama
     * pemilih periode.
     *
     * Alasan itu runtuh sesudah akun dan sakelar tema menempati pojok kanan
     * atas. Kop sekarang menjawab satu hal saja — sedang di halaman apa dan
     * masuk sebagai siapa — dan tombol unduh di sebelah nama pengguna
     * mencampur identitas dengan tindakan. Tempatnya ikut turun ke badan
     * halaman.
     */
    public function test_tombol_tindakan_berada_di_badan_bukan_di_kop(): void
    {
        $tindakan = [
            '/rekap' => 'Unduh Rekap',
            '/das' => 'Tambah DAS',
            '/juru' => 'Tambah Juru',
            '/wilayah' => 'Tambah Wilayah',
        ];

        foreach ($tindakan as $alamat => $tombol) {
            $html = $this->halaman($alamat);
            $kop = $this->potong($html, '<header', '<main');
            $badan = substr($html, strpos($html, '<main'));

            $this->assertStringNotContainsString($tombol, $kop, "{$tombol} masih di kop {$alamat}");
            $this->assertStringContainsString($tombol, $badan, "{$tombol} hilang dari badan {$alamat}");
        }
    }

    /**
     * Baris tindakan tidak boleh tergambar kosong. Tombol Tambah dibungkus
     * penjagaan peran, jadi bagi peninjau slotnya cuma berisi spasi — dan
     * wadah kosong tetap menyisakan jarak bawahnya sebagai celah hampa.
     */
    public function test_baris_tindakan_hilang_sama_sekali_bagi_peninjau(): void
    {
        $html = $this->actingAs($this->buatStaf('viewer'), 'staf')
            ->get('/juru')->assertOk()->getContent();

        $badan = substr($html, strpos($html, '<main'));

        $this->assertStringNotContainsString('Tambah Juru', $badan);
        $this->assertStringNotContainsString('mb-4 flex flex-wrap items-center gap-2.5', $badan);
    }

    /** Kop tetap memegang identitas: itulah yang tersisa sesudah tindakan pindah. */
    public function test_kop_masih_memegang_akun_dan_tema(): void
    {
        $kop = $this->potong($this->halaman('/rekap'), '<header', '<main');

        $this->assertStringContainsString('data-sakelar-tema', $kop);
        $this->assertStringContainsString('data-panel-pilihan', $kop);
    }

    public function test_periode_masih_mengubah_isi_halaman(): void
    {
        $juru = $this->buatJuru();
        $this->buatLaporan($juru, ['tanggal_inspeksi' => '2026-08-05', 'bulan' => 8, 'tahun' => 2026]);

        $this->assertStringContainsString('Agustus 2026', $this->halaman('/dashboard?bulan=8&tahun=2026'));
        $this->assertStringContainsString('Maret 2026', $this->halaman('/rekap?bulan=3&tahun=2026'));
    }
}
