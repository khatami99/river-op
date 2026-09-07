<?php

namespace Tests\Feature;

use App\Models\ItemInspeksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Peta sebaran laporan. Titiknya dikirim ke halaman sebagai JSON, jadi yang
 * diuji jumlah titik yang lolos penyaring — bukan tampilan petanya.
 */
class PetaSebaranTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    private function jumlahTitik(string $isi): int
    {
        preg_match('/data-titik="([^"]*)"/', $isi, $cocok);

        if (! isset($cocok[1])) {
            return 0;
        }

        return count(json_decode(html_entity_decode($cocok[1], ENT_QUOTES), true) ?? []);
    }

    public function test_hanya_bangunan_berkoordinat_yang_muncul(): void
    {
        $juru = $this->buatJuru();
        $laporan = $this->buatLaporan($juru);

        // Bangunan dari sebelum GPS ada tidak punya titik dan tidak akan
        // pernah punya, jadi tidak boleh ikut digambar.
        ItemInspeksi::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'laporan_op_id' => $laporan->id,
            'jenis_bangunan_id' => 1,
            'nama_bangunan' => 'Tanpa Titik',
            'koordinat_lat' => null,
            'koordinat_lng' => null,
        ]);

        $isi = $this->actingAs($this->buatStaf('viewer'), 'staf')
            ->get('/sebaran')->assertOk()->getContent();

        $this->assertSame(1, $this->jumlahTitik($isi));
        $this->assertStringContainsString('1 bangunan tidak muncul di peta', $isi);
    }

    public function test_penyaring_hanya_bermasalah_menyisakan_yang_bermasalah(): void
    {
        $juru = $this->buatJuru();
        $this->buatLaporan($juru, bermasalah: true);
        $this->buatLaporan($juru, ['tanggal_inspeksi' => '2026-08-20'], bermasalah: false);

        $staf = $this->buatStaf('viewer');

        $semua = $this->actingAs($staf, 'staf')->get('/sebaran')->getContent();
        $this->assertSame(2, $this->jumlahTitik($semua));

        $disaring = $this->actingAs($staf, 'staf')->get('/sebaran?bermasalah=1')->getContent();
        $this->assertSame(1, $this->jumlahTitik($disaring));
    }

    public function test_penyaring_das_memisahkan_wilayah(): void
    {
        $dasA = $this->buatDas('DAS A');
        $dasB = $this->buatDas('DAS B');

        $this->buatLaporan($this->buatJuru($dasA));
        $this->buatLaporan($this->buatJuru($dasB));

        $staf = $this->buatStaf('viewer');

        $this->assertSame(2, $this->jumlahTitik(
            $this->actingAs($staf, 'staf')->get('/sebaran')->getContent()
        ));

        $this->assertSame(1, $this->jumlahTitik(
            $this->actingAs($staf, 'staf')->get('/sebaran?das=' . $dasA->id)->getContent()
        ));
    }

    public function test_penyaring_periode_bekerja(): void
    {
        $juru = $this->buatJuru();
        $this->buatLaporan($juru);
        $this->buatLaporan($juru, ['tanggal_inspeksi' => '2026-07-05', 'bulan' => 7]);

        $staf = $this->buatStaf('viewer');

        $this->assertSame(1, $this->jumlahTitik(
            $this->actingAs($staf, 'staf')->get('/sebaran?bulan=7')->getContent()
        ));
    }
}
