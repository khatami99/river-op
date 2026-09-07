<?php

namespace Tests\Feature;

use App\Models\ItemInspeksi;
use App\Models\JenisBangunan;
use App\Models\LaporanOp;
use App\Models\StatusSinkronisasi;
use App\Models\TemuanKondisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Jalur masuk dari aplikasi juru. Ini pintu yang paling rawan: aplikasi
 * offline-first mengirim ulang laporan yang sama ketika jaringan tersendat,
 * dan penilaian bermasalah dihitung di sini sekali untuk selamanya karena
 * rekapitulasi mengandalkannya.
 */
class SinkronisasiLaporanTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    private function payload(string $uuid, string $itemUuid, array $kondisi): array
    {
        $jenis = JenisBangunan::with('bagianBangunan')->orderBy('urutan')->first();

        return [
            'laporan' => [[
                'uuid' => $uuid,
                'tanggal_inspeksi' => '2026-08-11',
                'bulan' => 8,
                'tahun' => 2026,
                'cuaca' => 'cerah',
                'debit_air' => 'normal',
                'dibuat_di_hp' => '2026-08-11 08:00:00',
                'item_inspeksi' => [[
                    'uuid' => $itemUuid,
                    'jenis_bangunan_id' => $jenis->id,
                    'nama_bangunan' => 'Tanggul Uji',
                    'nama_sungai' => 'Asam-asam',
                    'lokasi_km_dari' => 3.2,
                    'lokasi_km_sampai' => 3.8,
                    'koordinat_lat' => -3.9012,
                    'koordinat_lng' => 115.0456,
                    'temuan_kondisi' => [[
                        'bagian_bangunan_id' => $jenis->bagianBangunan->first()->id,
                        'kondisi_ditemukan' => $kondisi,
                        'tindakan_diperlukan' => 'Perbaikan ringan',
                    ]],
                ]],
            ]],
        ];
    }

    public function test_juru_dapat_masuk_dan_mengirim_laporan(): void
    {
        $juru = $this->buatJuru();

        $masuk = $this->postJson('/api/auth/login', [
            'username' => $juru->username,
            'password' => 'rahasia',
        ])->assertOk();

        $token = $masuk->json('token');
        $this->assertNotEmpty($token);

        $uuid = (string) Str::uuid();
        $itemUuid = (string) Str::uuid();

        $this->withToken($token)
            ->postJson('/api/laporan/sync', $this->payload($uuid, $itemUuid, ['Lereng Longsor']))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('laporan_op', 1);

        $item = ItemInspeksi::where('uuid', $itemUuid)->first();
        $this->assertSame('Asam-asam', $item->nama_sungai);
        $this->assertEquals(3.2, (float) $item->lokasi_km_dari);
        $this->assertEquals(-3.9012, (float) $item->koordinat_lat);
    }

    public function test_laporan_yang_sama_tidak_masuk_dua_kali(): void
    {
        $juru = $this->buatJuru();
        $token = $this->postJson('/api/auth/login', [
            'username' => $juru->username, 'password' => 'rahasia',
        ])->json('token');

        $uuid = (string) Str::uuid();
        $itemUuid = (string) Str::uuid();
        $payload = $this->payload($uuid, $itemUuid, ['Lereng Longsor']);

        // Aplikasi offline-first mengirim ulang ketika jaringan tersendat,
        // jadi pengiriman kedua harus diterima tanpa menggandakan data.
        $this->withToken($token)->postJson('/api/laporan/sync', $payload)->assertOk();
        $this->withToken($token)->postJson('/api/laporan/sync', $payload)->assertOk();

        $this->assertDatabaseCount('laporan_op', 1);
        $this->assertDatabaseCount('item_inspeksi', 1);
        $this->assertDatabaseCount('temuan_kondisi', 1);
    }

    public function test_penanda_bermasalah_dihitung_saat_sinkronisasi(): void
    {
        $juru = $this->buatJuru();
        $token = $this->postJson('/api/auth/login', [
            'username' => $juru->username, 'password' => 'rahasia',
        ])->json('token');

        $this->withToken($token)->postJson('/api/laporan/sync',
            $this->payload((string) Str::uuid(), (string) Str::uuid(), ['Normal']))->assertOk();
        $this->withToken($token)->postJson('/api/laporan/sync',
            $this->payload((string) Str::uuid(), (string) Str::uuid(), ['Lereng Longsor']))->assertOk();

        $this->assertSame(1, TemuanKondisi::where('ada_masalah', false)->count());
        $this->assertSame(1, TemuanKondisi::where('ada_masalah', true)->count());
    }

    public function test_sinkronisasi_ditolak_tanpa_token(): void
    {
        $this->postJson('/api/laporan/sync',
            $this->payload((string) Str::uuid(), (string) Str::uuid(), ['Normal']))
            ->assertUnauthorized();

        $this->assertDatabaseCount('laporan_op', 0);
    }

    public function test_staf_dinas_tidak_dapat_memakai_jalur_juru(): void
    {
        // Token juru dan sesi staf sengaja dipisah. Staf tidak boleh punya
        // token API sama sekali, apalagi mengirim laporan atas nama juru.
        $staf = $this->buatStaf('admin');

        $this->postJson('/api/auth/login', [
            'username' => $staf->username,
            'password' => 'rahasia',
        ])->assertStatus(401);
    }

    public function test_juru_dapat_mengabarkan_keadaan_antreannya(): void
    {
        $juru = $this->buatJuru();
        $token = $this->postJson('/api/auth/login', [
            'username' => $juru->username, 'password' => 'rahasia',
        ])->json('token');

        $this->withToken($token)->postJson('/api/sinkronisasi/status', [
            'jumlah_tertunda' => 2,
            'jumlah_gagal' => 1,
            'jumlah_terkirim' => 9,
            'versi_aplikasi' => '1.0',
            'pesan_terakhir' => 'Timeout ke server',
        ])->assertOk();

        $status = StatusSinkronisasi::where('juru_sungai_id', $juru->id)->first();
        $this->assertSame(1, $status->jumlah_gagal);
        $this->assertTrue($status->bermasalah());

        // Melapor lagi menimpa, bukan menumpuk baris baru.
        $this->withToken($token)->postJson('/api/sinkronisasi/status', [
            'jumlah_tertunda' => 0, 'jumlah_gagal' => 0, 'jumlah_terkirim' => 12,
        ])->assertOk();

        $this->assertDatabaseCount('status_sinkronisasi', 1);
        $this->assertSame(0, $status->fresh()->jumlah_gagal);
    }
}
