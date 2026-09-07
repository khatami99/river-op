<?php

namespace Tests\Feature;

use App\Models\ItemInspeksi;
use App\Models\JenisBangunan;
use App\Models\TemuanKondisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Export blangko OP. Inilah berkas yang benar-benar diarsipkan dinas, jadi
 * yang diuji isinya — bukan sekadar berkasnya terunduh.
 */
class BlangkoOpTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    private function muat(string $isi)
    {
        $berkas = tempnam(sys_get_temp_dir(), 'blangko') . '.xlsx';
        file_put_contents($berkas, $isi);
        $wb = IOFactory::load($berkas);
        unlink($berkas);

        return $wb;
    }

    public function test_hanya_jenis_bangunan_yang_diperiksa_yang_ikut_tercetak(): void
    {
        $juru = $this->buatJuru();
        $laporan = $this->buatLaporan($juru);
        $staf = $this->buatStaf('viewer');

        $isi = $this->actingAs($staf, 'staf')
            ->get("/laporan/{$laporan->id}/blangko")
            ->assertOk()
            ->getContent();

        $wb = $this->muat($isi);

        // Laporan ini hanya memuat satu bangunan, jadi tidak boleh ada
        // belasan lembar kosong ikut tercetak.
        $this->assertCount(1, $wb->getSheetNames());
        $this->assertSame('OP - 01.a', $wb->getSheetNames()[0]);
    }

    public function test_baris_lokasi_memuat_nama_sungai_dan_patok_kilometer(): void
    {
        $juru = $this->buatJuru();
        $laporan = $this->buatLaporan($juru);
        $staf = $this->buatStaf('viewer');

        $isi = $this->actingAs($staf, 'staf')->get("/laporan/{$laporan->id}/blangko")->getContent();
        $lembar = $this->muat($isi)->getActiveSheet();

        $lokasi = (string) $lembar->getCell('C5')->getValue();

        $this->assertStringContainsString('Sungai Uji', $lokasi);
        $this->assertStringContainsString('Km 1,5', $lokasi);
        $this->assertStringContainsString('Km 2', $lokasi);

        $this->assertStringContainsString($juru->nama, (string) $lembar->getCell('C6')->getValue());
    }

    public function test_bangunan_berjenis_sama_mendapat_lembarnya_sendiri(): void
    {
        $juru = $this->buatJuru();
        $laporan = $this->buatLaporan($juru);

        // Blangko hanya menyediakan satu lembar per jenis, sehingga bangunan
        // kedua yang berjenis sama harus digandakan lembarnya.
        $jenis = JenisBangunan::with('bagianBangunan')->orderBy('urutan')->first();

        $item = ItemInspeksi::create([
            'uuid' => (string) Str::uuid(),
            'laporan_op_id' => $laporan->id,
            'jenis_bangunan_id' => $jenis->id,
            'nama_bangunan' => 'Bangunan Kedua',
            'koordinat_lat' => -3.4,
            'koordinat_lng' => 115.9,
        ]);

        TemuanKondisi::create([
            'item_inspeksi_id' => $item->id,
            'bagian_bangunan_id' => $jenis->bagianBangunan->first()->id,
            'kondisi_ditemukan' => ['Normal'],
            'ada_masalah' => false,
        ]);

        $staf = $this->buatStaf('viewer');
        $isi = $this->actingAs($staf, 'staf')->get("/laporan/{$laporan->id}/blangko")->getContent();
        $wb = $this->muat($isi);

        $this->assertCount(2, $wb->getSheetNames());
        $this->assertContains('OP - 01.a (2)', $wb->getSheetNames());
        $this->assertStringContainsString(
            'Bangunan Kedua',
            (string) $wb->getSheetByName('OP - 01.a (2)')->getCell('C5')->getValue()
        );
    }

    public function test_temuan_masuk_ke_baris_bagian_bangunan_yang_benar(): void
    {
        $juru = $this->buatJuru();
        $laporan = $this->buatLaporan($juru);
        $staf = $this->buatStaf('viewer');

        $isi = $this->actingAs($staf, 'staf')->get("/laporan/{$laporan->id}/blangko")->getContent();
        $lembar = $this->muat($isi)->getActiveSheet();

        // Temuan dibuat pada bagian bangunan urutan pertama, yang di blangko
        // menempati baris 11. Pencocokannya lewat urutan, bukan nama.
        $this->assertStringContainsString('Lereng Longsor', (string) $lembar->getCell('C11')->getValue());
        $this->assertStringContainsString('S', (string) $lembar->getCell('E11')->getValue());
    }

    public function test_blangko_pdf_dapat_diunduh(): void
    {
        $juru = $this->buatJuru();
        $laporan = $this->buatLaporan($juru);
        $staf = $this->buatStaf('viewer');

        $balasan = $this->actingAs($staf, 'staf')
            ->get("/laporan/{$laporan->id}/blangko-pdf")
            ->assertOk();

        $this->assertSame('application/pdf', $balasan->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $balasan->getContent());
    }
}
