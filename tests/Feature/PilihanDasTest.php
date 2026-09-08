<?php

namespace Tests\Feature;

use App\Models\Das;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Tulisan pada daftar pilihan DAS.
 *
 * Peramban punya ketik-cepat bawaan pada elemen select: menekan huruf
 * melompat ke pilihan yang diawali huruf itu. Kemampuan itu mati bila semua
 * pilihan diawali kata yang sama — dan seluruh nama DAS memang diawali "DAS".
 *
 * Yang diuji di sini bukan cara menulisnya, melainkan akibatnya: huruf
 * pertama pilihan harus beragam. Kalau suatu hari awalannya kembali, tes ini
 * gagal sebelum ada yang capek menggulung enam puluh dua baris.
 */
class PilihanDasTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    public function test_awalan_das_dilepas_dari_tulisan_pilihan(): void
    {
        $das = Das::make(['nama_das' => 'DAS Kintap']);

        $this->assertSame('Kintap', $das->namaRingkas());
    }

    public function test_nama_tanpa_awalan_dibiarkan_apa_adanya(): void
    {
        $this->assertSame('Sungai Tabanio', Das::make(['nama_das' => 'Sungai Tabanio'])->namaRingkas());
    }

    public function test_huruf_pertama_pilihan_beragam_di_formulir_juru(): void
    {
        foreach (['DAS Kintap', 'DAS Angsana', 'DAS Batulicin'] as $nama) {
            $this->buatDas($nama);
        }

        $html = $this->actingAs($this->buatStaf('admin'), 'staf')
            ->get('/juru/create')
            ->assertOk()
            ->getContent();

        preg_match('~<select[^>]*name="das_id".*?</select>~s', $html, $blok);
        preg_match_all('~<option[^>]*value="\d+"[^>]*>\s*([^<(]+)~', $blok[0] ?? '', $pilihan);

        $huruf = collect($pilihan[1] ?? [])->map(fn ($t) => mb_strtoupper(mb_substr(trim($t), 0, 1)))->unique();

        $this->assertGreaterThan(1, $huruf->count(),
            'Semua pilihan DAS diawali huruf yang sama, sehingga ketik-cepat peramban tidak berguna.');
    }
}
