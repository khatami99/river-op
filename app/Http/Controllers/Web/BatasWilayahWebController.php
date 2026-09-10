<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Das;
use App\Models\WilayahSungai;
use App\Services\PembacaBatasWilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Pemasukan batas wilayah dari berkas BWS.
 *
 * Layar ini dibuat untuk dipakai tanpa kehadiran pembuatnya. Batas DAS dan
 * wilayah sungai belum ada saat aplikasi ini diserahkan, dan mungkin baru
 * turun dari BWS bertahun-tahun kemudian — jadi jalannya harus bisa
 * ditempuh sendiri oleh siapa pun yang saat itu memegang akun admin.
 *
 * Dua langkah, bukan satu. Berkas dari instansi tidak pernah memakai nama
 * kolom yang sama, dan menebaknya diam-diam berarti batas bisa terpasang
 * pada DAS yang salah tanpa ada yang tahu. Karena itu isinya ditampilkan
 * lebih dulu, lengkap dengan pasangan yang diusulkan, dan baru tersimpan
 * sesudah dibenarkan orang.
 */
class BatasWilayahWebController extends Controller
{
    /** Berkas telaah yang lebih tua dari ini dianggap ditinggalkan. */
    private const UMUR_TELAAH_JAM = 6;

    public function index()
    {
        return view('batas.index', $this->ringkasan());
    }

    public function telaah(Request $request, PembacaBatasWilayah $pembaca)
    {
        $request->validate([
            'jenis' => ['required', 'in:das,wilayah'],
            'berkas' => ['required', 'file', 'max:51200', 'mimes:zip,json,geojson,txt'],
        ], [], ['berkas' => 'berkas batas wilayah']);

        $unggahan = $request->file('berkas');

        try {
            $fitur = $pembaca->baca($unggahan->getRealPath(), $unggahan->getClientOriginalName());
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['berkas' => $e->getMessage()]);
        }

        $this->bersihkanTelaahLama();

        $tanda = (string) Str::uuid();
        $this->tulisTelaah($tanda, [
            'jenis' => $request->input('jenis'),
            'nama_berkas' => $unggahan->getClientOriginalName(),
            'fitur' => $fitur,
        ]);

        return redirect()->route('batas.pratinjau', $tanda);
    }

    public function pratinjau(Request $request, string $tanda)
    {
        $telaah = $this->bacaTelaah($tanda);

        if (! $telaah) {
            return redirect()->route('batas.index')
                ->withErrors(['berkas' => 'Telaah berkas sudah kedaluwarsa. Unggah ulang berkasnya.']);
        }

        $kolom = $this->daftarKolom($telaah['fitur']);
        $kolomNama = $request->input('kolom_nama');

        if (! in_array($kolomNama, $kolom, true)) {
            $kolomNama = $this->tebakKolomNama($telaah['fitur'], $kolom, $telaah['jenis']);
        }

        $tujuan = $this->daftarTujuan($telaah['jenis']);
        $terpakai = [];

        $baris = collect($telaah['fitur'])->map(function ($f, $i) use ($kolomNama, $tujuan, &$terpakai) {
            $nama = $kolomNama ? ($f['atribut'][$kolomNama] ?? '') : '';
            $cocok = $this->cariPasangan($nama, $tujuan, $terpakai);

            if ($cocok) {
                $terpakai[] = $cocok->id;
            }

            return [
                'indeks' => $i,
                'nama' => $nama !== '' ? $nama : '(kolom kosong)',
                'titik' => $f['jumlah_titik'],
                'titik_asli' => $f['titik_asli'] ?? $f['jumlah_titik'],
                'cocok' => $cocok?->id,
            ];
        });

        return view('batas.pratinjau', [
            'tanda' => $tanda,
            'telaah' => $telaah,
            'kolom' => $kolom,
            'kolomNama' => $kolomNama,
            'baris' => $baris,
            'tujuan' => $tujuan,
        ]);
    }

    public function simpan(Request $request, string $tanda)
    {
        $telaah = $this->bacaTelaah($tanda);

        if (! $telaah) {
            return redirect()->route('batas.index')
                ->withErrors(['berkas' => 'Telaah berkas sudah kedaluwarsa. Unggah ulang berkasnya.']);
        }

        $pasangan = (array) $request->input('cocok', []);
        $tujuan = $this->daftarTujuan($telaah['jenis'])->keyBy('id');
        $tersimpan = 0;

        DB::transaction(function () use ($pasangan, $telaah, $tujuan, &$tersimpan) {
            foreach ($pasangan as $indeks => $idTujuan) {
                $idTujuan = (int) $idTujuan;
                $fitur = $telaah['fitur'][(int) $indeks] ?? null;

                if ($idTujuan === 0 || ! $fitur || ! $tujuan->has($idTujuan)) {
                    continue;
                }

                $baris = $tujuan->get($idTujuan);
                $baris->poligon = json_encode($fitur['geometri']);
                $baris->poligon_diperbarui_at = now();
                $baris->save();

                $tersimpan++;
            }
        });

        @unlink($this->jalurTelaah($tanda));

        if ($tersimpan === 0) {
            return redirect()->route('batas.index')
                ->withErrors(['berkas' => 'Tidak ada satu pun batas yang dipasangkan, jadi tidak ada yang disimpan.']);
        }

        $label = $telaah['jenis'] === 'das' ? 'DAS' : 'wilayah sungai';

        return redirect()->route('batas.index')
            ->with('sukses', "Batas wilayah tersimpan pada {$tersimpan} {$label}.");
    }

    public function hapus(string $jenis, int $id)
    {
        abort_unless(in_array($jenis, ['das', 'wilayah'], true), 404);

        $baris = $this->daftarTujuan($jenis)->firstWhere('id', $id);
        abort_unless($baris !== null, 404);

        $baris->poligon = null;
        $baris->poligon_diperbarui_at = null;
        $baris->save();

        return back()->with('sukses', 'Batas wilayah dihapus.');
    }

    // ---------------------------------------------------------------

    /**
     * Kolom poligon sengaja TIDAK ikut diambil.
     *
     * Isinya bisa ratusan kilobita per baris, dan halaman ini cuma perlu tahu
     * ada atau tidak. Memuat enam puluh dua di antaranya hanya untuk
     * menggambar tanda centang akan menghabiskan memori tanpa guna.
     */
    private function ringkasan(): array
    {
        return [
            'das' => Das::select('id', 'nama_das', 'kode_das', 'poligon_diperbarui_at')
                ->selectRaw('poligon IS NOT NULL as punya')
                ->orderBy('nama_das')->get(),
            'wilayah' => WilayahSungai::select('id', 'nama_sungai', 'kode_sungai', 'poligon_diperbarui_at')
                ->selectRaw('poligon IS NOT NULL as punya')
                ->orderBy('nama_sungai')->get(),
        ];
    }

    private function daftarTujuan(string $jenis)
    {
        return $jenis === 'das'
            ? Das::orderBy('nama_das')->get()
            : WilayahSungai::orderBy('nama_sungai')->get();
    }

    /** @return array<int, string> */
    private function daftarKolom(array $fitur): array
    {
        $kolom = [];

        foreach ($fitur as $f) {
            foreach (array_keys($f['atribut']) as $k) {
                $kolom[$k] = true;
            }
        }

        return array_keys($kolom);
    }

    /**
     * Menebak kolom mana yang berisi nama wilayah.
     *
     * Bukan dengan menghafal nama kolom yang lazim — NAMOBJ, NAMA_DAS, WADMKK,
     * dan entah apa lagi — melainkan dengan mencoba tiap kolom dan memilih
     * yang paling banyak cocok dengan data yang sudah ada di sini. Cara ini
     * tetap bekerja pada berkas yang penamaannya belum pernah kita lihat.
     */
    private function tebakKolomNama(array $fitur, array $kolom, string $jenis): ?string
    {
        $tujuan = $this->daftarTujuan($jenis);
        $terbaik = null;
        $skorTerbaik = 0;

        foreach ($kolom as $k) {
            $skor = 0;
            $terpakai = [];

            foreach ($fitur as $f) {
                $cocok = $this->cariPasangan($f['atribut'][$k] ?? '', $tujuan, $terpakai);
                if ($cocok) {
                    $terpakai[] = $cocok->id;
                    $skor++;
                }
            }

            if ($skor > $skorTerbaik) {
                $skorTerbaik = $skor;
                $terbaik = $k;
            }
        }

        return $terbaik ?? ($kolom[0] ?? null);
    }

    /** @param array<int, int> $terpakai */
    private function cariPasangan(string $nama, $tujuan, array $terpakai)
    {
        $kunci = $this->normalkan($nama);

        if ($kunci === '') {
            return null;
        }

        return $tujuan->first(function ($baris) use ($kunci, $terpakai) {
            return ! in_array($baris->id, $terpakai, true)
                && $this->normalkan($baris->label) === $kunci;
        });
    }

    /**
     * "DAS Kusan", "das kusan", dan "KUSAN" adalah wilayah yang sama.
     *
     * Awalan jenis dibuang karena berkas instansi kadang menyertakannya dan
     * kadang tidak, dan itu bukan perbedaan yang berarti apa pun.
     */
    private function normalkan(?string $teks): string
    {
        $teks = mb_strtolower(trim((string) $teks));
        $teks = preg_replace('/^(das|ws|wilayah sungai|sungai)\s+/u', '', $teks);

        return preg_replace('/[^a-z0-9]/u', '', $teks) ?? '';
    }

    // ---------------------------------------------------------------

    private function jalurTelaah(string $tanda): string
    {
        // Nama berkas dibatasi ke bentuk uuid supaya tanda dari luar tidak bisa
        // menunjuk ke berkas lain di server.
        $bersih = preg_replace('/[^a-f0-9\-]/i', '', $tanda);

        return storage_path('app/batas-sementara/' . $bersih . '.json');
    }

    private function tulisTelaah(string $tanda, array $isi): void
    {
        $direktori = storage_path('app/batas-sementara');

        if (! is_dir($direktori)) {
            mkdir($direktori, 0755, true);
        }

        file_put_contents($this->jalurTelaah($tanda), json_encode($isi));
    }

    private function bacaTelaah(string $tanda): ?array
    {
        $jalur = $this->jalurTelaah($tanda);

        if (! is_file($jalur)) {
            return null;
        }

        $isi = json_decode(file_get_contents($jalur), true);

        return is_array($isi) ? $isi : null;
    }

    /**
     * Membuang telaah yang ditinggalkan.
     *
     * Berkas ini bisa berukuran puluhan megabita, dan orang kerap mengunggah
     * lalu menutup jendelanya tanpa menyimpan. Tanpa pembersihan, tumpukannya
     * memenuhi penyimpanan server bertahun-tahun kemudian tanpa ada yang tahu
     * asalnya.
     */
    private function bersihkanTelaahLama(): void
    {
        $batas = time() - self::UMUR_TELAAH_JAM * 3600;

        foreach (glob(storage_path('app/batas-sementara/*.json')) ?: [] as $berkas) {
            if (filemtime($berkas) < $batas) {
                @unlink($berkas);
            }
        }
    }
}
