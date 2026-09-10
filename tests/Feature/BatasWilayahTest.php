<?php

namespace Tests\Feature;

use App\Models\Das;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Shapefile\Geometry\Linestring;
use Shapefile\Geometry\Point;
use Shapefile\Geometry\Polygon;
use Shapefile\Shapefile;
use Shapefile\ShapefileWriter;
use Tests\DataUji;
use Tests\TestCase;
use ZipArchive;

/**
 * Memasukkan batas wilayah dari berkas BWS.
 *
 * Berkas batasnya belum ada saat aplikasi ini diserahkan dan mungkin baru
 * turun bertahun-tahun kemudian. Yang dijaga berkas ini bukan sekadar bahwa
 * alurnya jalan, melainkan bahwa alurnya masih bisa ditempuh sendiri oleh
 * orang yang tidak punya siapa pun untuk ditanyai — terutama bahwa berkas
 * yang keliru DITOLAK dengan sebab yang jelas, bukan diterima diam-diam lalu
 * menggambar peta yang salah tanpa ada yang menyadarinya.
 */
class BatasWilayahTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    private function kotak(float $lng, float $lat, float $ukuran = 0.2): array
    {
        return [[
            [$lng, $lat],
            [$lng + $ukuran, $lat],
            [$lng + $ukuran, $lat - $ukuran],
            [$lng, $lat - $ukuran],
            [$lng, $lat],
        ]];
    }

    private function geojson(array $fitur): UploadedFile
    {
        $jalur = tempnam(sys_get_temp_dir(), 'uji') . '.geojson';
        file_put_contents($jalur, json_encode(['type' => 'FeatureCollection', 'features' => $fitur]));

        return new UploadedFile($jalur, 'batas.geojson', 'application/json', null, true);
    }

    private function fitur(array $atribut, array $koordinat): array
    {
        return [
            'type' => 'Feature',
            'properties' => $atribut,
            'geometry' => ['type' => 'Polygon', 'coordinates' => $koordinat],
        ];
    }

    private function unggah(UploadedFile $berkas, string $jenis = 'das')
    {
        return $this->actingAs($this->buatStaf('admin'), 'staf')
            ->post('/batas-wilayah', ['jenis' => $jenis, 'berkas' => $berkas]);
    }

    // ---------------------------------------------------------------

    public function test_geojson_dipasangkan_dan_tersimpan(): void
    {
        $satu = $this->buatDas('DAS Kusan');
        $dua = $this->buatDas('DAS Satui');

        // Tanpa awalan "DAS", persis seperti berkas instansi pada umumnya.
        $jawab = $this->unggah($this->geojson([
            $this->fitur(['NAMOBJ' => 'Kusan'], $this->kotak(115.6, -3.4)),
            $this->fitur(['NAMOBJ' => 'Satui'], $this->kotak(115.4, -3.8)),
        ]));

        $jawab->assertRedirect();
        $tanda = basename($jawab->headers->get('Location'));

        $admin = $this->buatStaf('admin');
        $this->actingAs($admin, 'staf')->get("/batas-wilayah/{$tanda}")
            ->assertOk()
            ->assertSee('Kusan')
            ->assertSee('Satui');

        $this->actingAs($admin, 'staf')
            ->post("/batas-wilayah/{$tanda}", ['cocok' => [0 => $satu->id, 1 => $dua->id]])
            ->assertRedirect(route('batas.index'));

        $this->assertNotNull($satu->fresh()->poligon);
        $this->assertNotNull($dua->fresh()->poligon);
        $this->assertNotNull($satu->fresh()->poligon_diperbarui_at);

        $tersimpan = json_decode($satu->fresh()->poligon, true);
        $this->assertSame('Polygon', $tersimpan['type']);
    }

    public function test_shapefile_zip_terbaca(): void
    {
        $das = $this->buatDas('DAS Batulicin');

        $jawab = $this->unggah($this->buatZipShapefile([['Batulicin', 115.9, -3.5]]));
        $tanda = basename($jawab->headers->get('Location'));

        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->post("/batas-wilayah/{$tanda}", ['cocok' => [0 => $das->id]])
            ->assertRedirect(route('batas.index'));

        $this->assertNotNull($das->fresh()->poligon);
    }

    /**
     * Inilah penolakan yang paling penting. Berkas instansi kerap memakai
     * koordinat UTM, dan menerimanya berarti seluruh batas tergambar di tempat
     * yang salah tanpa satu pun tanda bahwa ada yang keliru.
     */
    public function test_koordinat_utm_ditolak_dengan_petunjuk_qgis(): void
    {
        $this->unggah($this->geojson([
            $this->fitur(['NAMOBJ' => 'Kusan'], $this->kotak(345000, 9600000, 1000)),
        ]))->assertSessionHasErrors('berkas');

        $pesan = session('errors')->first('berkas');
        $this->assertStringContainsString('EPSG:4326', $pesan);
        $this->assertStringContainsString('QGIS', $pesan);
    }

    public function test_berkas_provinsi_lain_ditolak(): void
    {
        // Sekitar Jakarta.
        $this->unggah($this->geojson([
            $this->fitur(['NAMOBJ' => 'Ciliwung'], $this->kotak(106.8, -6.2)),
        ]))->assertSessionHasErrors('berkas');

        $this->assertStringContainsString(
            'luar Kalimantan Selatan',
            session('errors')->first('berkas')
        );
    }

    public function test_berkas_berisi_titik_ditolak(): void
    {
        $jalur = tempnam(sys_get_temp_dir(), 'uji') . '.geojson';
        file_put_contents($jalur, json_encode([
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'properties' => [],
                'geometry' => ['type' => 'Point', 'coordinates' => [115.5, -3.4]],
            ]],
        ]));

        $this->unggah(new UploadedFile($jalur, 'titik.geojson', 'application/json', null, true))
            ->assertSessionHasErrors('berkas');
    }

    /**
     * Batas DAS dari BWS bisa berisi puluhan ribu titik. Enam puluh dua di
     * antaranya akan membuat halaman DAS memuat berpuluh megabita sebelum apa
     * pun tergambar.
     */
    public function test_poligon_terlalu_rapat_disederhanakan(): void
    {
        $das = $this->buatDas('DAS Kusan');

        $titik = [];
        for ($i = 0; $i < 6000; $i++) {
            $sudut = 2 * M_PI * $i / 6000;
            $titik[] = [115.5 + 0.3 * cos($sudut), -3.4 + 0.3 * sin($sudut)];
        }
        $titik[] = $titik[0];

        $jawab = $this->unggah($this->geojson([$this->fitur(['NAMOBJ' => 'Kusan'], [$titik])]));
        $tanda = basename($jawab->headers->get('Location'));

        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->post("/batas-wilayah/{$tanda}", ['cocok' => [0 => $das->id]]);

        $tersimpan = json_decode($das->fresh()->poligon, true);
        $jumlah = count($tersimpan['coordinates'][0]);

        $this->assertLessThan(6001, $jumlah, 'Poligon rapat harus dipangkas.');
        $this->assertGreaterThan(50, $jumlah, 'Tetapi jangan sampai bentuknya hilang.');
    }

    public function test_baris_tanpa_pasangan_dilewati(): void
    {
        $das = $this->buatDas('DAS Kusan');

        $jawab = $this->unggah($this->geojson([
            $this->fitur(['NAMOBJ' => 'Kusan'], $this->kotak(115.6, -3.4)),
            $this->fitur(['NAMOBJ' => 'Entah Apa'], $this->kotak(115.2, -3.9)),
        ]));
        $tanda = basename($jawab->headers->get('Location'));

        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->post("/batas-wilayah/{$tanda}", ['cocok' => [0 => $das->id, 1 => '']])
            ->assertSessionHas('sukses', fn ($p) => str_contains($p, '1 DAS'));

        $this->assertSame(1, Das::whereNotNull('poligon')->count());
    }

    public function test_batas_dapat_dihapus_kembali(): void
    {
        $das = $this->buatDas('DAS Kusan');
        $das->poligon = json_encode(['type' => 'Polygon', 'coordinates' => $this->kotak(115.6, -3.4)]);
        $das->poligon_diperbarui_at = now();
        $das->save();

        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->delete("/batas-wilayah/das/{$das->id}")
            ->assertRedirect();

        $this->assertNull($das->fresh()->poligon);
        $this->assertNull($das->fresh()->poligon_diperbarui_at);
    }

    public function test_viewer_tidak_boleh_memasukkan_batas(): void
    {
        $this->actingAs($this->buatStaf('viewer'), 'staf')
            ->get('/batas-wilayah')
            ->assertForbidden();

        $this->actingAs($this->buatStaf('viewer'), 'staf')
            ->post('/batas-wilayah', [
                'jenis' => 'das',
                'berkas' => $this->geojson([$this->fitur(['NAMOBJ' => 'Kusan'], $this->kotak(115.6, -3.4))]),
            ])
            ->assertForbidden();
    }

    /**
     * Peta harus menggambar batasnya walau titik perkiraannya belum pernah
     * diisi — batas itulah wujud sesungguhnya, dan titiknya cuma penambal
     * selama batasnya belum ada.
     */
    public function test_das_berbatas_tanpa_titik_tetap_masuk_data_peta(): void
    {
        $das = $this->buatDas('DAS Kusan');
        $das->koordinat_lat = null;
        $das->koordinat_lng = null;
        $das->poligon = json_encode(['type' => 'Polygon', 'coordinates' => $this->kotak(115.6, -3.4)]);
        $das->save();

        $html = $this->actingAs($this->buatStaf('admin'), 'staf')
            ->get('/das')->assertOk()->getContent();

        $this->assertStringContainsString('poligon', $html);
        $this->assertStringNotContainsString(
            'DAS belum punya titik dan tidak muncul di peta',
            $html,
            'DAS berbatas tidak boleh lagi dihitung sebagai tidak muncul di peta.'
        );
    }

    // ---------------------------------------------------------------

    /** @param array<int, array{0: string, 1: float, 2: float}> $wilayah */
    private function buatZipShapefile(array $wilayah): UploadedFile
    {
        $dir = sys_get_temp_dir() . '/shp_uji_' . uniqid();
        mkdir($dir, 0755, true);

        $penulis = new ShapefileWriter("{$dir}/batas.shp", [
            Shapefile::OPTION_EXISTING_FILES_MODE => Shapefile::MODE_OVERWRITE,
        ]);
        $penulis->setShapeType(Shapefile::SHAPE_TYPE_POLYGON);
        $penulis->addCharField('NAMOBJ', 60);

        foreach ($wilayah as [$nama, $lng, $lat]) {
            $cincin = new Linestring();
            foreach ([[0, 0], [0.2, 0], [0.2, -0.2], [0, -0.2], [0, 0]] as [$dx, $dy]) {
                $cincin->addPoint(new Point($lng + $dx, $lat + $dy));
            }

            $poligon = new Polygon([$cincin]);
            $poligon->setDataArray(['NAMOBJ' => $nama]);
            $penulis->writeRecord($poligon);
        }

        unset($penulis);

        $jalurZip = "{$dir}/batas.zip";
        $zip = new ZipArchive();
        $zip->open($jalurZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach (glob("{$dir}/batas.*") as $berkas) {
            if (! str_ends_with($berkas, '.zip')) {
                $zip->addFile($berkas, basename($berkas));
            }
        }
        $zip->close();

        return new UploadedFile($jalurZip, 'batas.zip', 'application/zip', null, true);
    }
}
