<?php

namespace Tests\Feature;

use App\Models\JuruSungai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Memindahkan juru yang sudah ada ke DAS lain lewat halaman detail DAS.
 *
 * Satu DAS boleh dipegang banyak juru, tetapi satu juru hanya punya satu DAS.
 * Karena itu menugaskan juru ke DAS baru berarti MENCABUTNYA dari DAS lama —
 * dan itulah yang diuji di sini, bukan sekadar bahwa nilainya berubah.
 */
class PindahJuruTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    public function test_juru_pindah_dan_das_lamanya_kehilangan_dia(): void
    {
        $lama = $this->buatDas('DAS Lama');
        $baru = $this->buatDas('DAS Baru');
        $juru = $this->buatJuru($lama, 'Ahmad');

        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->post("/das/{$baru->id}/pindah-juru", ['juru_sungai_id' => $juru->id])
            ->assertRedirect(route('das.show', $baru));

        $this->assertSame($baru->id, $juru->fresh()->das_id);
        $this->assertCount(0, $lama->fresh()->juruSungai, 'DAS lama harus benar-benar ditinggalkan.');
        $this->assertCount(1, $baru->fresh()->juruSungai);
    }

    public function test_satu_das_boleh_menampung_lebih_dari_satu_juru(): void
    {
        $das = $this->buatDas('DAS Luas');
        $lain = $this->buatDas('DAS Lain');
        $this->buatJuru($das, 'Juru Pertama');
        $kedua = $this->buatJuru($lain, 'Juru Kedua');

        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->post("/das/{$das->id}/pindah-juru", ['juru_sungai_id' => $kedua->id]);

        // Inilah yang menjawab keluhan "DAS itu luas, tidak cukup satu orang".
        $this->assertCount(2, $das->fresh()->juruSungai);
    }

    public function test_memindahkan_ke_das_yang_sama_tidak_merusak_apa_pun(): void
    {
        $das = $this->buatDas();
        $juru = $this->buatJuru($das);

        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->post("/das/{$das->id}/pindah-juru", ['juru_sungai_id' => $juru->id]);

        $this->assertSame($das->id, $juru->fresh()->das_id);
        $this->assertCount(1, $das->fresh()->juruSungai);
    }

    public function test_juru_karangan_ditolak(): void
    {
        $das = $this->buatDas();

        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->post("/das/{$das->id}/pindah-juru", ['juru_sungai_id' => 99999])
            ->assertSessionHasErrors('juru_sungai_id');
    }

    public function test_viewer_tidak_boleh_memindahkan_juru(): void
    {
        $lama = $this->buatDas('DAS Lama');
        $baru = $this->buatDas('DAS Baru');
        $juru = $this->buatJuru($lama);

        $this->actingAs($this->buatStaf('viewer'), 'staf')
            ->post("/das/{$baru->id}/pindah-juru", ['juru_sungai_id' => $juru->id])
            ->assertForbidden();

        $this->assertSame($lama->id, $juru->fresh()->das_id);
    }
}
