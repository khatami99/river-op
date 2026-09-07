<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Penyaring pada halaman Laporan OP.
 *
 * Periode punya empat kemungkinan isian karena pengguna bebas mengisi sebagian
 * saja, dan keempatnya gampang rusak diam-diam: pernah terjadi "Agustus" tanpa
 * batas akhir justru berarti "Agustus dan seterusnya".
 */
class PenyaringLaporanTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    private function jumlah(array $parameter): int
    {
        $jawaban = $this->actingAs($this->buatStaf('viewer'), 'staf')
            ->getJson('/laporan?' . http_build_query($parameter + ['fragmen' => 1]))
            ->assertOk();

        preg_match('/^(\d+)/', $jawaban->json('jumlah'), $cocok);

        return (int) ($cocok[1] ?? -1);
    }

    /** Tiga laporan pada bulan berbeda, dua juru berbeda. */
    private function siapkanKeadaan(): array
    {
        $juruA = $this->buatJuru(null, 'Juru A');
        $juruB = $this->buatJuru(null, 'Juru B');

        $this->buatLaporan($juruA, ['tanggal_inspeksi' => '2026-07-05', 'bulan' => 7]);
        $this->buatLaporan($juruA, ['tanggal_inspeksi' => '2026-08-05', 'bulan' => 8]);
        $this->buatLaporan($juruB, ['tanggal_inspeksi' => '2026-09-05', 'bulan' => 9]);

        return [$juruA, $juruB];
    }

    public function test_satu_bulan_berarti_bulan_itu_saja(): void
    {
        $this->siapkanKeadaan();

        // Pernah salah: tanpa batas akhir, "Agustus" berarti Agustus dan
        // seterusnya, sehingga September ikut terhitung.
        $this->assertSame(1, $this->jumlah(['bulan' => 8, 'tahun' => 2026]));
    }

    public function test_rentang_periode_mencakup_ujung_ujungnya(): void
    {
        $this->siapkanKeadaan();

        $this->assertSame(3, $this->jumlah(['bulan' => 7, 'tahun' => 2026, 'bulan_sampai' => 9]));
        $this->assertSame(2, $this->jumlah(['bulan' => 8, 'tahun' => 2026, 'bulan_sampai' => 9]));
    }

    public function test_rentang_boleh_melewati_pergantian_tahun(): void
    {
        $juru = $this->buatJuru();
        $this->buatLaporan($juru, ['tanggal_inspeksi' => '2026-11-05', 'bulan' => 11, 'tahun' => 2026]);
        $this->buatLaporan($juru, ['tanggal_inspeksi' => '2027-01-05', 'bulan' => 1, 'tahun' => 2027]);
        $this->buatLaporan($juru, ['tanggal_inspeksi' => '2027-06-05', 'bulan' => 6, 'tahun' => 2027]);

        // Bulan 11 lebih besar daripada bulan 1, jadi perbandingan per bulan
        // saja akan menghasilkan nol. Periodenya dibandingkan sebagai 202611
        // sampai 202702.
        $this->assertSame(2, $this->jumlah([
            'bulan' => 11, 'tahun' => 2026,
            'bulan_sampai' => 2, 'tahun_sampai' => 2027,
        ]));
    }

    public function test_tahun_saja_berarti_sepanjang_tahun(): void
    {
        $this->siapkanKeadaan();

        $this->assertSame(3, $this->jumlah(['tahun' => 2026]));
    }

    public function test_bulan_saja_berlaku_pada_tahun_mana_pun(): void
    {
        $juru = $this->buatJuru();
        $this->buatLaporan($juru, ['tanggal_inspeksi' => '2026-08-05', 'bulan' => 8, 'tahun' => 2026]);
        $this->buatLaporan($juru, ['tanggal_inspeksi' => '2027-08-05', 'bulan' => 8, 'tahun' => 2027]);

        $this->assertSame(2, $this->jumlah(['bulan' => 8]));
    }

    public function test_juru_dapat_dipilih_lebih_dari_satu(): void
    {
        [$juruA, $juruB] = $this->siapkanKeadaan();

        $this->assertSame(2, $this->jumlah(['juru_sungai_id' => [$juruA->id]]));
        $this->assertSame(3, $this->jumlah(['juru_sungai_id' => [$juruA->id, $juruB->id]]));
    }

    public function test_export_mengikuti_penyaring_yang_sama(): void
    {
        $this->siapkanKeadaan();
        $staf = $this->buatStaf('admin');

        // Export membaca penyaring lewat fungsi yang sama dengan tabelnya,
        // sehingga apa yang terlihat di layar itulah yang terunduh.
        $isi = $this->actingAs($staf, 'staf')
            ->get('/laporan/export?bulan=8&tahun=2026')
            ->assertOk()
            ->getContent();

        $berkas = tempnam(sys_get_temp_dir(), 'ekspor') . '.xlsx';
        file_put_contents($berkas, $isi);

        $lembar = \PhpOffice\PhpSpreadsheet\IOFactory::load($berkas)->getActiveSheet();
        unlink($berkas);

        $baris = 0;
        for ($r = 6; $r <= $lembar->getHighestRow(); $r++) {
            if ($lembar->getCell('A' . $r)->getValue()) {
                $baris++;
            }
        }

        $this->assertSame(1, $baris, 'Export harus memuat hanya laporan Agustus.');
    }

    public function test_pencarian_bebas_menjangkau_seluruh_isi_laporan(): void
    {
        $juru = $this->buatJuru(null, 'Ahmad Junaidi');

        $this->buatLaporan($juru, [
            'tanggal_inspeksi' => '2026-09-05', 'bulan' => 9, 'tahun' => 2026,
            'cuaca' => 'hujan', 'debit_air' => 'tinggi',
        ]);
        $this->buatLaporan($juru, [
            'tanggal_inspeksi' => '2026-08-05', 'bulan' => 8, 'tahun' => 2026,
            'cuaca' => 'cerah', 'debit_air' => 'normal',
        ]);

        $this->assertSame(2, $this->jumlah(['cari' => 'Ahmad']), 'nama juru');
        $this->assertSame(1, $this->jumlah(['cari' => 'hujan']), 'cuaca');
        $this->assertSame(1, $this->jumlah(['cari' => 'tinggi']), 'debit air');
        $this->assertSame(1, $this->jumlah(['cari' => 'September']), 'nama bulan');
        $this->assertSame(2, $this->jumlah(['cari' => '2026']), 'tahun');
        $this->assertSame(0, $this->jumlah(['cari' => 'tidakada']), 'tanpa hasil');
    }

    public function test_ketikan_pendek_tidak_menyeret_seluruh_bulan(): void
    {
        // Debit sengaja bukan "normal", sebab kata itu memuat "ma" dan akan
        // ikut cocok lewat jalur teks biasa — bukan lewat nama bulan.
        $juru = $this->buatJuru(null, 'Budi');
        $this->buatLaporan($juru, ['tanggal_inspeksi' => '2026-03-05', 'bulan' => 3, 'debit_air' => 'tinggi']);
        $this->buatLaporan($juru, ['tanggal_inspeksi' => '2026-05-05', 'bulan' => 5, 'debit_air' => 'tinggi']);

        // "ma" adalah awalan Maret dan Mei. Tanpa batas tiga huruf, mengetik dua
        // huruf saat mencari nama orang akan menyeret dua bulan penuh.
        $this->assertSame(0, $this->jumlah(['cari' => 'ma']));
        $this->assertSame(1, $this->jumlah(['cari' => 'mar']));
    }

    public function test_angka_dicocokkan_ke_jumlah_bangunan_atau_tahun(): void
    {
        $juru = $this->buatJuru();
        $this->buatLaporan($juru, ['tahun' => 2026]);

        // Satu laporan berisi satu bangunan. Tahun selalu empat angka,
        // sedangkan jumlah bangunan tidak pernah sebanyak itu, sehingga
        // keduanya tidak mungkin saling tertukar.
        $this->assertSame(1, $this->jumlah(['cari' => '1']));
        $this->assertSame(0, $this->jumlah(['cari' => '9']));
        $this->assertSame(1, $this->jumlah(['cari' => '2026']));
    }
}
