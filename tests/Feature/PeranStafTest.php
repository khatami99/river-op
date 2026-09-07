<?php

namespace Tests\Feature;

use App\Models\Das;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Pembatasan peran. Viewer boleh melihat apa pun, tetapi tidak boleh mengubah
 * apa pun — termasuk bila permintaannya ditembak langsung tanpa lewat tombol,
 * sebab tombol yang disembunyikan bukan pengaman.
 */
class PeranStafTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/laporan')->assertRedirect('/login');
    }

    public function test_viewer_boleh_melihat(): void
    {
        $viewer = $this->buatStaf('viewer');

        foreach (['/dashboard', '/laporan', '/rekap', '/das', '/juru', '/wilayah', '/peta', '/sebaran'] as $halaman) {
            $this->actingAs($viewer, 'staf')->get($halaman)->assertOk();
        }
    }

    public function test_viewer_ditolak_saat_mencoba_mengubah_data(): void
    {
        $viewer = $this->buatStaf('viewer');
        $das = $this->buatDas();

        $this->actingAs($viewer, 'staf')->get('/das/create')->assertForbidden();
        $this->actingAs($viewer, 'staf')->post('/das', ['nama_das' => 'Sisipan'])->assertForbidden();
        $this->actingAs($viewer, 'staf')->put("/das/{$das->id}", ['nama_das' => 'Diubah'])->assertForbidden();
        $this->actingAs($viewer, 'staf')->delete("/das/{$das->id}")->assertForbidden();

        $this->assertSame(1, Das::count(), 'Data tidak boleh berubah oleh viewer.');
    }

    public function test_viewer_tidak_boleh_membuka_halaman_khusus_admin(): void
    {
        $viewer = $this->buatStaf('viewer');

        $this->actingAs($viewer, 'staf')->get('/staf')->assertForbidden();
        $this->actingAs($viewer, 'staf')->get('/log')->assertForbidden();
    }

    public function test_admin_boleh_mengubah_data(): void
    {
        $admin = $this->buatStaf('admin');
        $das = $this->buatDas();

        $this->actingAs($admin, 'staf')
            ->put("/das/{$das->id}", [
                'nama_das' => 'DAS Sudah Diubah',
                'kode_das' => $das->kode_das,
                'wilayah_sungai_id' => $das->wilayah_sungai_id,
                'aktif' => 1,
            ])
            ->assertRedirect();

        $this->assertSame('DAS Sudah Diubah', $das->fresh()->nama_das);
    }

    public function test_das_yang_masih_dipakai_tidak_dapat_dihapus(): void
    {
        $admin = $this->buatStaf('admin');
        $das = $this->buatDas();
        $this->buatJuru($das);

        $this->actingAs($admin, 'staf')->delete("/das/{$das->id}");

        $this->assertNotNull($das->fresh(), 'DAS yang masih dipegang juru harus tetap ada.');
    }
}
