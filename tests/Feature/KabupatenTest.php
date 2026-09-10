<?php

namespace Tests\Feature;

use App\Models\Das;
use App\Models\Kabupaten;
use App\Models\WilayahSungai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Kabupaten bertingkat: wilayah sungai menentukan, DAS memilih.
 *
 * Sebuah DAS mustahil berada di kabupaten yang tidak disinggung wilayah
 * sungainya, dan aturan itulah yang dijaga berkas ini — bukan sekadar bahwa
 * datanya tersimpan. Dua sisi diuji terpisah: apa yang DITAWARKAN formulir,
 * dan apa yang DITERIMA server, sebab yang pertama cuma berlaku bagi orang
 * yang memakai layar.
 */
class KabupatenTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    private Kabupaten $tanahLaut;
    private Kabupaten $tanahBumbu;
    private Kabupaten $tabalong;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();

        $this->tanahLaut = Kabupaten::where('kode', 'TALA')->firstOrFail();
        $this->tanahBumbu = Kabupaten::where('kode', 'TANBU')->firstOrFail();
        $this->tabalong = Kabupaten::where('kode', 'TABALONG')->firstOrFail();
    }

    /**
     * Wilayah sungai yang menaungi DAS bikinan DataUji.
     *
     * Dibuat dengan firstOrCreate persis seperti di sana, sebab kabupatennya
     * sering perlu diatur SEBELUM DAS pertama ada.
     */
    private function wilayah(): WilayahSungai
    {
        return WilayahSungai::firstOrCreate(
            ['kode_sungai' => '04.10.B'],
            ['nama_sungai' => 'Wilayah Sungai Cengal-Batulicin', 'aktif' => true]
        );
    }

    private function simpanDas(Das $das, array $kabupatenIds)
    {
        return $this->actingAs($this->buatStaf('admin'), 'staf')
            ->put("/das/{$das->id}", [
                'nama_das' => $das->nama_das,
                'kode_das' => $das->kode_das,
                'wilayah_sungai_id' => $das->wilayah_sungai_id,
                'kabupaten_ids' => $kabupatenIds,
                'aktif' => 1,
            ]);
    }

    private function simpanWilayah(WilayahSungai $wilayah, array $kabupatenIds, string $peran = 'admin')
    {
        return $this->actingAs($this->buatStaf($peran), 'staf')
            ->put("/wilayah/{$wilayah->id}", [
                'nama_sungai' => $wilayah->nama_sungai,
                'kode_sungai' => $wilayah->kode_sungai,
                'kabupaten_ids' => $kabupatenIds,
                'aktif' => 1,
            ]);
    }

    /**
     * Inilah jalan buntu yang membuat tingkatan ini dibuat: dulu tabel
     * kabupaten cuma berisi tiga baris yang kebetulan disinggung WS
     * Cengal-Batulicin, sedangkan layar untuk menambah kabupaten tidak ada
     * dan memang tidak akan dibuat. Wilayah sungai baru di Tabalong berarti
     * mentok tanpa jalan keluar.
     */
    public function test_seluruh_kabupaten_kalsel_tersedia(): void
    {
        $this->assertSame(13, Kabupaten::count());

        foreach (['Tabalong', 'Balangan', 'Hulu Sungai Utara', 'Kota Banjarmasin'] as $nama) {
            $this->assertTrue(
                Kabupaten::where('nama', $nama)->exists(),
                "{$nama} harus bisa dipilih tanpa perlu menyunting basis data."
            );
        }
    }

    public function test_formulir_das_hanya_menawarkan_kabupaten_wilayah_sungainya(): void
    {
        $this->wilayah()->kabupaten()->sync([$this->tanahLaut->id, $this->tanahBumbu->id]);
        $das = $this->buatDas();

        $halaman = $this->actingAs($this->buatStaf('admin'), 'staf')
            ->get("/das/{$das->id}/edit")
            ->assertOk();

        $halaman->assertSee('Tanah Laut');
        $halaman->assertSee('Tanah Bumbu');

        // Tabalong tidak menempel pada wilayah sungai mana pun, jadi ia tidak
        // digambar sama sekali — bukan digambar lalu disembunyikan.
        $halaman->assertDontSee('Tabalong');
    }

    public function test_kabupaten_di_luar_wilayah_sungai_ditolak(): void
    {
        $this->wilayah()->kabupaten()->sync([$this->tanahLaut->id]);
        $das = $this->buatDas();

        // Menyembunyikan pilihan di layar hanya menjaga orang yang memakai
        // layar; pengiriman yang dirakit sendiri harus ditahan di server.
        $this->simpanDas($das, [$this->tabalong->id])
            ->assertSessionHasErrors('kabupaten_ids.0');

        $this->assertCount(0, $das->fresh()->kabupaten);
    }

    public function test_das_boleh_membentang_di_dua_kabupaten_wilayah_sungainya(): void
    {
        $this->wilayah()->kabupaten()->sync([$this->tanahLaut->id, $this->tanahBumbu->id]);
        $das = $this->buatDas();

        $this->simpanDas($das, [$this->tanahLaut->id, $this->tanahBumbu->id])
            ->assertSessionHasNoErrors();

        // Batas DAS mengikuti punggung bukit, batas kabupaten digambar
        // manusia — keduanya memang tidak berhenti di tempat yang sama.
        $this->assertEqualsCanonicalizing(
            [$this->tanahLaut->id, $this->tanahBumbu->id],
            $das->fresh()->kabupaten->pluck('id')->all()
        );
    }

    public function test_kabupaten_yang_masih_dipakai_das_tidak_bisa_dicabut_dari_wilayah_sungai(): void
    {
        $wilayah = $this->wilayah();
        $wilayah->kabupaten()->sync([$this->tanahLaut->id, $this->tanahBumbu->id]);

        $das = $this->buatDas('DAS Angsana');
        $das->kabupaten()->sync([$this->tanahLaut->id]);

        $jawaban = $this->simpanWilayah($wilayah, [$this->tanahBumbu->id])
            ->assertSessionHasErrors('kabupaten_ids');

        // Pesannya harus menyebut apa yang menghalangi, bukan sekadar
        // menolak: admin perlu tahu DAS mana yang harus dibereskan dulu.
        $pesan = $jawaban->getSession()->get('errors')->first('kabupaten_ids');
        $this->assertStringContainsString('Tanah Laut', $pesan);
        $this->assertStringContainsString('DAS Angsana', $pesan);

        $this->assertEqualsCanonicalizing(
            [$this->tanahLaut->id, $this->tanahBumbu->id],
            $wilayah->fresh()->kabupaten->pluck('id')->all(),
            'Penolakan tidak boleh menyisakan perubahan separuh jalan.'
        );
    }

    public function test_kabupaten_yang_tidak_dipakai_boleh_dicabut(): void
    {
        $wilayah = $this->wilayah();
        $wilayah->kabupaten()->sync([
            $this->tanahLaut->id, $this->tanahBumbu->id, $this->tabalong->id,
        ]);

        $das = $this->buatDas();
        $das->kabupaten()->sync([$this->tanahLaut->id]);

        $this->simpanWilayah($wilayah, [$this->tanahLaut->id, $this->tanahBumbu->id])
            ->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing(
            [$this->tanahLaut->id, $this->tanahBumbu->id],
            $wilayah->fresh()->kabupaten->pluck('id')->all()
        );
    }

    public function test_viewer_tidak_boleh_mengubah_kabupaten_wilayah_sungai(): void
    {
        $wilayah = $this->wilayah();
        $wilayah->kabupaten()->sync([$this->tanahLaut->id]);

        $this->simpanWilayah($wilayah, [$this->tanahBumbu->id], 'viewer')
            ->assertForbidden();

        $this->assertSame(
            [$this->tanahLaut->id],
            $wilayah->fresh()->kabupaten->pluck('id')->all()
        );
    }
}
