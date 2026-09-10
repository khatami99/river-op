<?php

namespace Tests\Feature;

use App\Models\JuruSungai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Menetapkan siapa saja yang bertugas di sebuah DAS, dari formulir DAS.
 *
 * juru_sungai.das_id berarti satu juru punya tepat satu DAS, jadi ini bukan
 * tabel penghubung yang tinggal disinkronkan: menugaskan juru ke sebuah DAS
 * otomatis mencabutnya dari DAS sebelumnya, dan mencopotnya meninggalkannya
 * tanpa DAS sama sekali. Kedua akibat itulah yang dijaga berkas ini —
 * termasuk bahwa keduanya dikabarkan, bukan terjadi diam-diam.
 */
class PenugasanJuruTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    private function simpanDas($das, array $juruIds)
    {
        return $this->actingAs($this->buatStaf('admin'), 'staf')
            ->put("/das/{$das->id}", [
                'nama_das' => $das->nama_das,
                'kode_das' => $das->kode_das,
                'wilayah_sungai_id' => $das->wilayah_sungai_id,
                'juru_ids' => $juruIds,
                'aktif' => 1,
            ]);
    }

    public function test_beberapa_juru_bisa_bertugas_di_satu_das(): void
    {
        $das = $this->buatDas('DAS Kusan');
        $satu = $this->buatJuru(null, 'Ahmad Fauzi');
        $dua = $this->buatJuru(null, 'Budi Santoso');

        $this->simpanDas($das, [$satu->id, $dua->id])
            ->assertSessionHasNoErrors();

        // DAS itu wilayah yang luas; membatasinya pada satu juru bukan aturan
        // dinas melainkan keterbatasan yang kebetulan ada di layar.
        $this->assertEqualsCanonicalizing(
            [$satu->id, $dua->id],
            $das->fresh()->juruSungai->pluck('id')->all()
        );
    }

    public function test_menugaskan_juru_dari_das_lain_memindahkannya(): void
    {
        $asal = $this->buatDas('DAS Asal');
        $tujuan = $this->buatDas('DAS Tujuan');
        $juru = $this->buatJuru($asal, 'Ahmad Fauzi');

        $this->simpanDas($tujuan, [$juru->id]);

        // Bukan menyalin: satu juru cuma punya satu das_id, jadi DAS asal
        // benar-benar kehilangan orangnya.
        $this->assertSame($tujuan->id, $juru->fresh()->das_id);
        $this->assertCount(0, $asal->fresh()->juruSungai);
    }

    public function test_perpindahan_dikabarkan_pada_pesan_berhasil(): void
    {
        $asal = $this->buatDas('DAS Asal');
        $tujuan = $this->buatDas('DAS Tujuan');
        $juru = $this->buatJuru($asal, 'Ahmad Fauzi');

        // Tindakan di satu layar yang berakibat pada layar lain tidak boleh
        // berlalu tanpa disebut; admin harus tahu DAS Asal barusan kosong.
        $this->simpanDas($tujuan, [$juru->id])
            ->assertSessionHas('sukses', fn ($pesan) => str_contains($pesan, '1 juru dipindahkan dari DAS lain'));
    }

    public function test_juru_yang_dicopot_menjadi_tanpa_das(): void
    {
        $das = $this->buatDas('DAS Kusan');
        $juru = $this->buatJuru($das, 'Ahmad Fauzi');

        // Keadaan sah, bukan kerusakan: juru boleh didata lebih dulu selagi
        // wilayah tugasnya belum diputuskan.
        $this->simpanDas($das, [])
            ->assertSessionHas('sukses', fn ($pesan) => str_contains($pesan, '1 juru kini tanpa DAS'));

        $this->assertNull($juru->fresh()->das_id);
    }

    public function test_juru_karangan_ditolak_dan_tidak_mengubah_apa_pun(): void
    {
        $das = $this->buatDas('DAS Kusan');
        $juru = $this->buatJuru($das, 'Ahmad Fauzi');

        $this->simpanDas($das, [$juru->id, 999999])
            ->assertSessionHasErrors('juru_ids.1');

        // Penolakan tidak boleh menyisakan perubahan separuh jalan.
        $this->assertSame($das->id, $juru->fresh()->das_id);
    }

    public function test_juru_das_lain_tidak_ikut_tercopot(): void
    {
        $das = $this->buatDas('DAS Kusan');
        $tetangga = $this->buatDas('DAS Tetangga');
        $juru = $this->buatJuru($das, 'Ahmad Fauzi');
        $jiran = $this->buatJuru($tetangga, 'Budi Santoso');

        // Menyimpan DAS Kusan dengan daftar kosong hanya boleh menyentuh
        // orang-orangnya sendiri.
        $this->simpanDas($das, []);

        $this->assertNull($juru->fresh()->das_id);
        $this->assertSame($tetangga->id, $jiran->fresh()->das_id);
    }

    public function test_juru_boleh_disimpan_tanpa_das(): void
    {
        // Tanpa ini alasan dibolehkannya juru menggantung jadi omong kosong:
        // satu-satunya cara mendata juru yang wilayahnya belum diputuskan
        // adalah memberinya DAS asal-asalan lalu mencopotnya dari layar DAS.
        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->post('/juru', [
                'nama' => 'Ahmad Fauzi',
                'no_telepon' => '081234567890',
                'username' => 'ahmad_uji',
                'password' => 'rahasia123',
                'das_id' => '',
                'aktif' => 1,
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull(JuruSungai::where('username', 'ahmad_uji')->firstOrFail()->das_id);
    }
}
