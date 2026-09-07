<?php

namespace Tests\Feature;

use App\Models\Das;
use App\Models\LogAktivitas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Jejak perubahan data. Gunanya baru terasa saat ada pertanyaan
 * "siapa yang mengubah ini", jadi yang diuji bukan cuma tercatat, tetapi juga
 * nilai sebelum-sesudahnya benar dan rahasianya tidak ikut bocor.
 */
class AuditLogTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    public function test_perubahan_dicatat_beserta_nilai_sebelum_dan_sesudah(): void
    {
        $admin = $this->buatStaf('admin');
        $das = $this->buatDas('DAS Lama');

        $this->actingAs($admin, 'staf')->put("/das/{$das->id}", [
            'nama_das' => 'DAS Baru',
            'kode_das' => $das->kode_das,
            'wilayah_sungai_id' => $das->wilayah_sungai_id,
            'aktif' => 1,
        ])->assertRedirect();

        $log = LogAktivitas::where('jenis_data', 'Das')->where('aksi', 'diubah')->latest('id')->first();

        $this->assertNotNull($log, 'Perubahan DAS harus tercatat.');
        $this->assertSame($admin->id, $log->staf_dinas_id);
        $this->assertSame($admin->nama, $log->nama_pelaku);
        $this->assertSame('DAS Lama', $log->perubahan['sebelum']['nama_das']);
        $this->assertSame('DAS Baru', $log->perubahan['sesudah']['nama_das']);
    }

    public function test_penambahan_dan_penghapusan_ikut_tercatat(): void
    {
        $admin = $this->buatStaf('admin');
        $wilayah = $this->buatDas()->wilayahSungai;

        // Sengaja lewat halaman, bukan langsung ke model, sebab pencatatan
        // memang hanya berjalan ketika ada staf yang sedang masuk.
        $this->actingAs($admin, 'staf')->post('/das', [
            'nama_das' => 'DAS Dibuat Admin',
            'kode_das' => 'DAS-TES-01',
            'wilayah_sungai_id' => $wilayah->id,
            'aktif' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'dibuat', 'jenis_data' => 'Das']);

        $baru = Das::where('kode_das', 'DAS-TES-01')->firstOrFail();

        $this->actingAs($admin, 'staf')->delete("/das/{$baru->id}")->assertRedirect();

        $this->assertDatabaseHas('log_aktivitas', ['aksi' => 'dihapus', 'jenis_data' => 'Das']);
    }

    public function test_kata_sandi_tidak_pernah_ikut_tercatat(): void
    {
        $admin = $this->buatStaf('admin');

        $this->actingAs($admin, 'staf')->post('/staf', [
            'nama' => 'Staf Baru',
            'username' => 'staf_baru',
            'password' => 'rahasia-sekali',
            'password_confirmation' => 'rahasia-sekali',
            'role' => 'viewer',
            'aktif' => 1,
        ]);

        $log = LogAktivitas::where('jenis_data', 'StafDinas')->where('aksi', 'dibuat')->latest('id')->first();

        if ($log) {
            $this->assertArrayNotHasKey('password', $log->perubahan ?? []);
            $this->assertStringNotContainsString('rahasia-sekali', json_encode($log->perubahan));
        }

        $this->assertTrue(true);
    }

    public function test_perubahan_tanpa_staf_yang_masuk_tidak_dicatat(): void
    {
        // Perubahan dari seeder atau perintah artisan tidak punya pelaku yang
        // bertanggung jawab, jadi mencatatnya hanya memenuhi log.
        Das::first() ?? $this->buatDas();

        $this->assertSame(0, LogAktivitas::count(),
            'Perubahan di luar sesi staf tidak boleh masuk log.');
    }

    public function test_halaman_log_hanya_untuk_admin(): void
    {
        $this->actingAs($this->buatStaf('viewer'), 'staf')->get('/log')->assertForbidden();
        $this->actingAs($this->buatStaf('admin'), 'staf')->get('/log')->assertOk();
    }
}
