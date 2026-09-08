<?php

namespace Tests\Feature;

use App\Models\Das;
use App\Models\Kabupaten;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Pengisian kabupaten untuk seluruh DAS sekaligus.
 *
 * Kabupaten ikut tercetak pada blangko OP yang diserahkan ke dinas, jadi yang
 * diuji bukan sekadar halamannya terbuka melainkan datanya benar-benar
 * mendarat — termasuk satu DAS yang membentang di dua kabupaten.
 */
class KabupatenDasTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    public function test_kabupaten_tersimpan_untuk_banyak_das_sekaligus(): void
    {
        $satu = $this->buatDas('DAS Satu');
        $dua = $this->buatDas('DAS Dua');
        [$kabA, $kabB] = Kabupaten::orderBy('nama')->take(2)->get()->all();

        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->post('/das/kabupaten', ['kabupaten' => [
                $satu->id => [$kabA->id],
                $dua->id => [$kabB->id],
            ]])
            ->assertRedirect(route('das.kabupaten'));

        $this->assertSame([$kabA->id], $satu->fresh()->kabupaten->pluck('id')->all());
        $this->assertSame([$kabB->id], $dua->fresh()->kabupaten->pluck('id')->all());
    }

    public function test_satu_das_boleh_membentang_di_dua_kabupaten(): void
    {
        $das = $this->buatDas('DAS Lintas');
        $kab = Kabupaten::orderBy('nama')->take(2)->pluck('id')->all();

        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->post('/das/kabupaten', ['kabupaten' => [$das->id => $kab]]);

        // Bukan pilihan tunggal: batas DAS tidak berhenti di batas kabupaten.
        $this->assertEqualsCanonicalizing($kab, $das->fresh()->kabupaten->pluck('id')->all());
    }

    public function test_kabupaten_dapat_dikosongkan_kembali(): void
    {
        $das = $this->buatDas();
        $das->kabupaten()->sync(Kabupaten::pluck('id')->take(1)->all());

        // DAS yang tidak dikirim sama sekali oleh formulir berarti seluruh
        // centangnya dilepas — itulah cara formulir centang mengabarkan "kosong".
        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->post('/das/kabupaten', ['kabupaten' => []]);

        $this->assertCount(0, $das->fresh()->kabupaten);
    }

    public function test_id_kabupaten_karangan_diabaikan(): void
    {
        $das = $this->buatDas();

        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->post('/das/kabupaten', ['kabupaten' => [$das->id => [99999, 'bukan angka']]]);

        $this->assertCount(0, $das->fresh()->kabupaten, 'Isian formulir tidak boleh dipercaya begitu saja.');
    }

    public function test_viewer_tidak_boleh_mengubah_kabupaten(): void
    {
        $das = $this->buatDas();
        $kab = Kabupaten::first();

        $this->actingAs($this->buatStaf('viewer'), 'staf')
            ->get('/das/kabupaten')
            ->assertForbidden();

        $this->actingAs($this->buatStaf('viewer'), 'staf')
            ->post('/das/kabupaten', ['kabupaten' => [$das->id => [$kab->id]]])
            ->assertForbidden();

        $this->assertCount(0, $das->fresh()->kabupaten);
    }
}
