<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Permintaan API tanpa sesi yang sah harus dijawab 401, bukan dialihkan.
 *
 * Bawaan Laravel mengalihkan permintaan tak terautentikasi ke halaman login web
 * bila permintaannya tidak meminta JSON — dan aplikasi juru memang tidak
 * memintanya. Akibatnya POST /api/laporan/sync dengan token kedaluwarsa
 * berakhir di HTML berkode 200 sesudah pengalihan diikuti: dari sisi aplikasi
 * terbaca sebagai berhasil, padahal laporannya tidak pernah sampai ke dinas.
 */
class ApiTidakDialihkanTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    /**
     * Tanpa header Accept sama sekali — persis seperti yang dikirim Retrofit
     * sebelum header itu ditambahkan.
     */
    public function test_api_tanpa_token_dijawab_401_bukan_dialihkan(): void
    {
        $jawab = $this->get('/api/master/sync');

        $jawab->assertStatus(401);
        $this->assertFalse($jawab->isRedirect(), 'API tidak boleh dialihkan ke halaman login web.');
    }

    public function test_api_dengan_token_ngawur_dijawab_401(): void
    {
        $jawab = $this->post(
            '/api/laporan/sync',
            ['laporan' => []],
            ['Authorization' => 'Bearer token-yang-tidak-pernah-ada']
        );

        $jawab->assertStatus(401);
        $this->assertFalse($jawab->isRedirect());
    }

    public function test_pengiriman_laporan_tanpa_sesi_tidak_menyimpan_apa_pun(): void
    {
        $this->post(
            '/api/laporan/sync',
            ['laporan' => [['uuid' => (string) \Illuminate\Support\Str::uuid()]]],
            ['Authorization' => 'Bearer token-yang-tidak-pernah-ada']
        )->assertStatus(401);

        $this->assertSame(0, \App\Models\LaporanOp::count());
    }

    /** Halaman web biasa harus tetap dialihkan seperti sedia kala. */
    public function test_halaman_web_tetap_dialihkan_ke_login(): void
    {
        $this->get('/das')->assertRedirect(route('login'));
    }

    public function test_juru_dengan_token_sah_tetap_dilayani(): void
    {
        $juru = $this->buatJuru();

        $token = $this->postJson('/api/auth/login', [
            'username' => $juru->username,
            'password' => 'rahasia',
        ])->json('token');

        $this->get('/api/master/sync', ['Authorization' => "Bearer {$token}"])
            ->assertOk();
    }
}
