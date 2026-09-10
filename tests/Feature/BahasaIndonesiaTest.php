<?php

namespace Tests\Feature;

use App\Models\Das;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DataUji;
use Tests\TestCase;

/**
 * Tidak boleh ada kalimat berbahasa Inggris yang sampai ke layar pengguna.
 *
 * Sistem ini dipakai pegawai Dinas PUPR, dan tidak seorang pun di antara
 * mereka wajib memahami bahasa Inggris untuk mengisi sebuah formulir. Sebelum
 * ada berkas terjemahan, kesalahan pengisian muncul sebagai kalimat separuh
 * Indonesia separuh Inggris — "The kode DAS has already been taken." — dan
 * justru bagian yang menjelaskan sebabnya yang berbahasa asing.
 *
 * Kunci terjemahan yang tertinggal tidak menimbulkan galat apa pun: Laravel
 * diam-diam memakai bahasa cadangan. Karena itu keadaan ini harus dijaga tes,
 * bukan diandalkan pada ingatan orang.
 */
class BahasaIndonesiaTest extends TestCase
{
    use RefreshDatabase;
    use DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanMasterData();
    }

    public function test_bahasa_bawaan_aplikasi_indonesia(): void
    {
        $this->assertSame('id', config('app.locale'));
        $this->assertSame('id', config('app.fallback_locale'));
    }

    /** Persis keadaan yang dilaporkan: kode DAS yang sudah dipakai. */
    public function test_kode_das_kembar_ditolak_dalam_bahasa_indonesia(): void
    {
        $das = $this->buatDas();

        $jawab = $this->actingAs($this->buatStaf('admin'), 'staf')
            ->from(route('das.create'))
            ->post(route('das.store'), [
                'nama_das' => 'DAS Uji Kembar',
                'kode_das' => $das->kode_das,
                'wilayah_sungai_id' => $das->wilayah_sungai_id,
                'aktif' => 1,
            ]);

        $pesan = session('errors')->first('kode_das');

        $this->assertSame('Kode DAS sudah digunakan.', $pesan);
        $this->assertStringNotContainsStringIgnoringCase('has already been taken', $pesan);
    }

    public function test_kolom_wajib_kosong_ditolak_dalam_bahasa_indonesia(): void
    {
        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->from(route('das.create'))
            ->post(route('das.store'), []);

        $galat = session('errors');

        $this->assertSame('Kolom Nama DAS wajib diisi.', $galat->first('nama_das'));
        $this->assertSame('Kolom Kode DAS wajib diisi.', $galat->first('kode_das'));
        $this->assertSame('Kolom Wilayah sungai wajib diisi.', $galat->first('wilayah_sungai_id'));
    }

    /**
     * Nama kolom basis data tidak boleh bocor ke layar. Pegawai yang membaca
     * "wilayah_sungai_id" tidak punya cara menghubungkannya dengan kotak isian
     * mana pun yang tampak di hadapannya.
     */
    public function test_nama_kolom_basis_data_tidak_muncul_di_pesan(): void
    {
        $this->actingAs($this->buatStaf('admin'), 'staf')
            ->from(route('das.create'))
            ->post(route('das.store'), []);

        foreach (session('errors')->all() as $pesan) {
            $this->assertStringNotContainsString('_id', $pesan);
            $this->assertStringNotContainsString('_das', $pesan);
        }
    }

    /**
     * Seluruh kunci terjemahan harus ada dalam bahasa Indonesia. Kunci yang
     * tertinggal tidak pernah mengeluh — ia hanya menampilkan bahasa Inggris.
     */
    public function test_tidak_ada_kunci_terjemahan_yang_tertinggal(): void
    {
        $rata = function (array $a, string $awalan = '') use (&$rata): array {
            $hasil = [];
            foreach ($a as $kunci => $nilai) {
                $hasil = is_array($nilai)
                    ? array_merge($hasil, $rata($nilai, "{$awalan}{$kunci}."))
                    : array_merge($hasil, ["{$awalan}{$kunci}"]);
            }

            return $hasil;
        };

        foreach (glob(base_path('lang/en/*.php')) as $berkasInggris) {
            $nama = basename($berkasInggris);
            $berkasIndonesia = base_path('lang/id/' . $nama);

            $this->assertFileExists($berkasIndonesia, "Terjemahan lang/id/{$nama} belum ada.");

            $tertinggal = array_filter(
                array_diff($rata(require $berkasInggris), $rata(require $berkasIndonesia)),
                fn ($k) => ! str_starts_with($k, 'custom.') && ! str_starts_with($k, 'attributes.')
            );

            $this->assertSame(
                [],
                array_values($tertinggal),
                "Kunci berikut belum diterjemahkan pada {$nama}."
            );
        }
    }

    /**
     * Pesan bawaan Laravel yang paling sering terlihat, diperiksa langsung dari
     * penerjemahnya supaya kekeliruan ketik pada berkas terjemahan ketahuan di
     * sini, bukan di hadapan pegawai dinas.
     */
    public function test_pesan_bawaan_laravel_sudah_berbahasa_indonesia(): void
    {
        $this->assertSame(
            'Kolom Nama wajib diisi.',
            trans('validation.required', ['attribute' => 'Nama'])
        );

        $this->assertSame(
            'Nama pengguna sudah digunakan.',
            trans('validation.unique', ['attribute' => 'Nama pengguna'])
        );

        $this->assertSame('&laquo; Sebelumnya', trans('pagination.previous'));
        $this->assertSame('Berikutnya &raquo;', trans('pagination.next'));
    }
}
