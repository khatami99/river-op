<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Menonaktifkan juru harus benar-benar memutus aksesnya, bukan sekadar
 * menutup pintu masuk yang sudah terlanjur dilewatinya.
 *
 * Sebelum ada middleware `juru.aktif`, kolom `aktif` hanya diperiksa pada
 * saat login. Juru yang dinonaktifkan hari ini tetap dapat mengirim laporan
 * sampai tiga puluh hari ke depan memakai token yang sudah ada di HP-nya —
 * dan laporan itu masuk ke sistem sebagai laporan resmi tanpa penanda apa pun.
 */
class JuruNonaktifTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    /**
     * Melupakan pengguna yang sudah terlanjur dipecahkan guard.
     *
     * Di dalam satu tes, seluruh permintaan berbagi satu instans aplikasi,
     * sehingga `auth:sanctum` menyimpan objek juru dari permintaan pertama
     * dan memakainya lagi pada permintaan berikutnya — lengkap dengan nilai
     * `aktif` yang sudah basi. Di server sungguhan hal ini tidak terjadi:
     * tiap permintaan HTTP memecahkan penggunanya sendiri dari basis data.
     * Tanpa pemanggilan ini, tesnya menguji ingatan guard, bukan middleware.
     */
    private function permintaanBaru(): void
    {
        Auth::forgetGuards();
    }

    private function tokenUntuk($juru): string
    {
        $masuk = $this->postJson('/api/auth/login', [
            'username' => $juru->username,
            'password' => 'rahasia',
        ]);

        $masuk->assertOk();

        return 'Bearer ' . $masuk->json('token');
    }

    public function test_token_lama_ditolak_sesudah_juru_dinonaktifkan(): void
    {
        $juru = $this->buatJuru();
        $token = $this->tokenUntuk($juru);

        // Selagi masih aktif, token itu sah.
        $this->getJson('/api/master/profil', ['Authorization' => $token])->assertOk();

        $juru->update(['aktif' => false]);
        $this->permintaanBaru();

        $this->getJson('/api/master/profil', ['Authorization' => $token])
            ->assertStatus(401);
    }

    public function test_juru_nonaktif_tidak_dapat_mengirim_laporan(): void
    {
        $juru = $this->buatJuru();
        $token = $this->tokenUntuk($juru);

        $juru->update(['aktif' => false]);

        $this->postJson('/api/laporan/sync', [
            'laporan' => [[
                'uuid' => (string) Str::uuid(),
                'tanggal_inspeksi' => '2026-09-10',
                'bulan' => 9,
                'tahun' => 2026,
                'item_inspeksi' => [],
            ]],
        ], ['Authorization' => $token])->assertStatus(401);

        $this->assertSame(0, \App\Models\LaporanOp::count());
    }

    public function test_juru_nonaktif_tidak_dapat_mengabarkan_status_sinkronisasi(): void
    {
        $juru = $this->buatJuru();
        $token = $this->tokenUntuk($juru);

        $juru->update(['aktif' => false]);

        $this->postJson('/api/sinkronisasi/status', [
            'jumlah_tertunda' => 3,
            'jumlah_gagal' => 0,
            'jumlah_terkirim' => 0,
            'versi_aplikasi' => '1.0',
        ], ['Authorization' => $token])->assertStatus(401);
    }

    /**
     * Penonaktifan sering bersifat sementara — cuti panjang, atau salah pencet.
     * Karena tokennya sengaja tidak dimusnahkan, mengaktifkan kembali akun itu
     * cukup untuk memulihkan keadaan tanpa menghubungi juru dan memintanya
     * masuk ulang.
     */
    public function test_akses_pulih_sendiri_begitu_juru_diaktifkan_lagi(): void
    {
        $juru = $this->buatJuru();
        $token = $this->tokenUntuk($juru);

        $juru->update(['aktif' => false]);
        $this->permintaanBaru();
        $this->getJson('/api/master/profil', ['Authorization' => $token])->assertStatus(401);

        $juru->update(['aktif' => true]);
        $this->permintaanBaru();
        $this->getJson('/api/master/profil', ['Authorization' => $token])->assertOk();
    }

    public function test_juru_aktif_tidak_terganggu(): void
    {
        $juru = $this->buatJuru();
        $token = $this->tokenUntuk($juru);

        $this->getJson('/api/master/profil', ['Authorization' => $token])->assertOk();
        $this->getJson('/api/master/sync', ['Authorization' => $token])->assertOk();
    }
}
