<?php

namespace Tests\Feature;

use App\Models\ItemInspeksi;
use App\Services\BlangkoOpExporter;
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
    /** Baris kepala kolom pada blangko dinas. */
    private const BARIS_KEPALA = 10;

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

    /**
     * Kepala kolom pada lembar PDF, diambil dari markup yang benar-benar
     * dirender — bukan dari daftar yang ditulis ulang di berkas tes ini.
     */
    private function kepalaKolomPdf(\App\Models\LaporanOp $laporan): array
    {
        $html = view('laporan.blangko-pdf', [
            'laporan' => $laporan,
            'lembar' => app(BlangkoOpExporter::class)->lembarUntukPdf($laporan),
        ])->render();

        preg_match('~<thead>(.*?)</thead>~s', $html, $kepala);
        preg_match_all('~<th[^>]*>(.*?)</th>~s', $kepala[1] ?? '', $kolom);

        return array_map(fn ($t) => trim(html_entity_decode(strip_tags($t))), $kolom[1] ?? []);
    }

    public function test_pdf_memberi_satu_lembar_untuk_tiap_bangunan(): void
    {
        $juru = $this->buatJuru();
        $laporan = $this->buatLaporan($juru);

        $jenis = JenisBangunan::orderBy('urutan')->first();

        // Bangunan kedua dengan jenis yang sama: di Excel ia mendapat lembar
        // sendiri, dan PDF harus memperlakukannya sama.
        ItemInspeksi::create([
            'uuid' => (string) Str::uuid(),
            'laporan_op_id' => $laporan->id,
            'jenis_bangunan_id' => $jenis->id,
            'nama_bangunan' => 'Bangunan Kedua',
            'nama_sungai' => 'Uji',
            'koordinat_lat' => -3.44,
            'koordinat_lng' => 115.97,
        ]);

        $lembar = app(BlangkoOpExporter::class)->lembarUntukPdf($laporan->fresh());

        $this->assertCount(2, $lembar, 'Tiap bangunan yang diperiksa berhak atas lembarnya sendiri.');
    }

    public function test_temuan_pada_pdf_mendarat_di_bagian_bangunan_yang_benar(): void
    {
        $juru = $this->buatJuru();
        $laporan = $this->buatLaporan($juru);

        $lembar = app(BlangkoOpExporter::class)->lembarUntukPdf($laporan);
        $baris = $lembar[0]['baris'];

        // Temuan dibuat pada bagian bangunan urutan pertama. Sisi Excel sudah
        // menjaga ini; sisi PDF sebelumnya tidak diperiksa sama sekali,
        // sehingga temuan yang mendarat di bagian yang keliru akan tetap
        // menghasilkan berkas yang lolos.
        $this->assertStringContainsString('Lereng Longsor', $baris[0]['pengamatan']);
        $this->assertNotSame('', $baris[0]['koordinat'], 'Baris yang ada temuannya harus membawa koordinat.');

        // Bagian yang tidak diperiksa tetap muncul sebagai baris kosong,
        // persis seperti blangko kertas — bukan dihilangkan.
        $this->assertGreaterThan(1, $baris->count());
        $this->assertSame('', $baris[1]['pengamatan']);
        $this->assertSame('', $baris[1]['koordinat']);
    }

    public function test_kepala_kolom_pdf_sama_dengan_template_excel(): void
    {
        $juru = $this->buatJuru();
        $laporan = $this->buatLaporan($juru);
        $staf = $this->buatStaf('viewer');

        $isi = $this->actingAs($staf, 'staf')->get("/laporan/{$laporan->id}/blangko")->getContent();
        $lembar = $this->muat($isi)->getActiveSheet();

        $dariTemplate = [];
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $kolom) {
            $dariTemplate[] = trim((string) $lembar->getCell($kolom . self::BARIS_KEPALA)->getValue());
        }

        // Excel mengisi berkas blangko asli milik dinas, sedangkan PDF meniru
        // tata letaknya lewat markup sendiri — dua sumber untuk satu bentuk.
        // Bila suatu hari dinas merevisi blangkonya, berkas templatenya cukup
        // diganti dan Excel ikut berubah sendiri; PDF tidak, dan tidak ada
        // yang memberi tahu. Tes ini yang memberi tahu.
        $this->assertSame(
            $dariTemplate,
            $this->kepalaKolomPdf($laporan),
            'Kepala kolom PDF tidak lagi sama dengan blangko dinas. '
            . 'Kalau template Excel-nya baru saja diganti, sesuaikan juga blangko-pdf.blade.php.'
        );
    }
}
