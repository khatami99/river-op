<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use App\Models\WilayahSungai;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WilayahSungaiWebController extends Controller
{
    public function index(Request $request)
    {
        $wilayah = WilayahSungai::withCount(['das', 'juruSungai', 'laporanOp'])
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = $request->string('cari')->toString();
                $q->where(fn ($sub) => $sub->where('nama_sungai', 'like', "%{$cari}%")
                    ->orWhere('kode_sungai', 'like', "%{$cari}%"));
            })
            ->orderBy('nama_sungai')
            ->paginate(10)
            ->withQueryString();

        // Wilayah sungai tidak punya koordinat sendiri — ia wilayah, bukan
        // titik. Penandanya diletakkan di rata-rata titik DAS yang bernaung di
        // bawahnya: bukan angka karangan, melainkan turunan dari data yang ada,
        // dan ikut bergeser sendiri setiap titik DAS ditambah atau dikoreksi.
        $wilayah->loadMissing(['das' => fn ($q) => $q
            ->whereNotNull('koordinat_lat')
            ->whereNotNull('koordinat_lng')]);

        $dataPeta = $wilayah
            ->filter(fn ($w) => $w->das->isNotEmpty())
            ->values()
            ->map(fn ($w) => [
                'nama' => $w->nama_sungai,
                'kode' => $w->kode_sungai,
                'lat' => round($w->das->avg('koordinat_lat'), 7),
                'lng' => round($w->das->avg('koordinat_lng'), 7),
                'das' => $w->das_count,
                'das_bertitik' => $w->das->count(),
                'juru' => $w->juru_sungai_count,
                'laporan' => $w->laporan_op_count,
                'aktif' => (bool) $w->aktif,
                'url' => route('wilayah.show', $w),
                'url_das' => route('das.index', ['cari' => '']),
            ]);

        $data = [
            'dataPeta' => $dataPeta,
            'wilayah' => $wilayah,
            'filter' => ['cari' => $request->input('cari')],
        ];

        // Permintaan potongan datang dari penyaring yang berubah di layar.
        // Hanya area hasilnya yang dikirim ulang, sehingga kursor pengguna
        // tidak lepas dari kotak pencarian dan halaman tidak berkedip.
        if ($request->boolean('fragmen')) {
            return response()->json([
                'hasil' => view('wilayah._hasil', $data)->render(),
                'jumlah' => $data['wilayah']->total() . ' wilayah sungai terdaftar',
            ]);
        }

        return view('wilayah.index', $data);
    }

    public function show(WilayahSungai $wilayah)
    {
        $wilayah->load(['das' => fn ($q) => $q
            ->with(['juruSungai' => fn ($j) => $j->where('aktif', true)->orderBy('nama')])
            ->withCount(['juruSungai', 'laporanOp'])
            ->orderBy('nama_das')]);
        $wilayah->loadCount(['das', 'juruSungai', 'laporanOp']);

        // Peta di sini menjawab pertanyaan yang khas halaman ini: DAS mana saja
        // yang bernaung di bawah wilayah sungai ini, dan letaknya di mana.
        //
        // Dua kumpulan yang berbeda dengan sengaja: peta memerlukan seluruh
        // DAS yang bertitik, sedangkan daftar di bawahnya cukup sepuluh per
        // halaman seperti daftar lain di aplikasi ini. Memaksa keduanya
        // memakai satu kumpulan berarti memilih antara peta yang tidak utuh
        // atau daftar sepanjang 62 baris.
        $daftarDas = $wilayah->das()
            ->withCount(['juruSungai', 'laporanOp'])
            ->orderBy('nama_das')
            ->paginate(10)
            ->withQueryString();

        $dataPeta = $wilayah->das
            ->filter(fn ($d) => $d->punyaKoordinat())
            ->values()
            ->map(fn ($d) => [
                'id' => $d->id,
                'nama' => $d->nama_das,
                'kode' => $d->kode_das,
                'wilayah' => $wilayah->nama_sungai,
                'lat' => (float) $d->koordinat_lat,
                'lng' => (float) $d->koordinat_lng,
                'juru' => $d->juru_sungai_count,
                'laporan' => $d->laporan_op_count,
                'bermasalah' => 0,
                'url' => route('das.show', $d),
                'daftar_juru' => $d->juruSungai->map(fn ($j) => [
                    'nama' => $j->nama,
                    'telepon' => $j->no_telepon ?: '-',
                    'inisial' => mb_strtoupper(mb_substr($j->nama, 0, 1)),
                    'url' => route('juru.show', $j),
                ])->values(),
            ]);

        return view('wilayah.show', [
            'wilayah' => $wilayah,
            'daftarDas' => $daftarDas,
            'dataPeta' => $dataPeta,
            'belumBerkoordinat' => $wilayah->das->filter(fn ($d) => ! $d->punyaKoordinat())->count(),
        ]);
    }

    public function create()
    {
        return view('wilayah.form', [
            'wilayah' => new WilayahSungai(['aktif' => true]),
            'daftarKabupaten' => Kabupaten::where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['aktif'] = $request->boolean('aktif');

        $wilayah = WilayahSungai::create(Arr::except($data, 'kabupaten_nama'));
        $wilayah->kabupaten()->sync($this->idKabupaten($request->input('kabupaten_nama')));

        return redirect()->route('wilayah.index')->with('sukses', 'Wilayah sungai berhasil ditambahkan.');
    }

    public function edit(WilayahSungai $wilayah)
    {
        $wilayah->load('kabupaten');

        return view('wilayah.form', [
            'wilayah' => $wilayah,
            'daftarKabupaten' => Kabupaten::where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, WilayahSungai $wilayah)
    {
        $data = $this->validasi($request, $wilayah);
        $data['aktif'] = $request->boolean('aktif');

        $wilayah->load('kabupaten');
        $nama = $this->uraikanNama($request->input('kabupaten_nama'));

        // Pencocokan dulu, pembuatan belakangan. Bila simpanan ini nanti
        // ditolak, tidak boleh ada kabupaten baru yang terlanjur lahir dan
        // menggantung tanpa pemilik.
        $sudahAda = $this->cocokkanNama($nama);

        if ($keberatan = $this->kabupatenMasihDipakai($wilayah, $sudahAda)) {
            return back()->withInput()->withErrors(['kabupaten_nama' => $keberatan]);
        }

        $wilayah->update(Arr::except($data, 'kabupaten_nama'));
        $wilayah->kabupaten()->sync($this->idKabupaten($request->input('kabupaten_nama')));

        return redirect()->route('wilayah.index')->with('sukses', 'Data wilayah sungai diperbarui.');
    }

    /**
     * Isian "Kotabaru, Tanah Bumbu, Tanah Laut" menjadi daftar nama.
     *
     * Nama kosong dibuang dan nama kembar disatukan tanpa memedulikan huruf
     * besar-kecil, sebab mengetik "Kotabaru, kotabaru" jelas bukan maksud
     * menyebut dua kabupaten.
     */
    private function uraikanNama(?string $isian)
    {
        return collect(explode(',', (string) $isian))
            ->map(fn ($n) => Kabupaten::rapikanNama($n))
            ->filter()
            ->unique(fn ($n) => mb_strtolower($n))
            ->values();
    }

    /** Id kabupaten yang namanya sudah dikenal — tanpa membuat yang baru. */
    private function cocokkanNama($nama): array
    {
        $dikenal = Kabupaten::withTrashed()->get()->keyBy(fn ($k) => mb_strtolower($k->nama));

        return $nama
            ->map(fn ($n) => $dikenal->get(mb_strtolower($n))?->id)
            ->filter()
            ->values()
            ->all();
    }

    /** Seperti cocokkanNama, tetapi nama yang belum dikenal dibuatkan. */
    private function idKabupaten(?string $isian): array
    {
        return DB::transaction(fn () => $this->uraikanNama($isian)
            ->map(fn ($n) => Kabupaten::dariNama($n)?->id)
            ->filter()
            ->values()
            ->all());
    }

    /**
     * Menahan pencabutan kabupaten yang masih dipegang DAS di bawahnya.
     *
     * Pilihan sikapnya ada tiga: menolak, ikut mencabut dari DAS-nya, atau
     * membiarkan data saling bertentangan. Yang dipakai adalah menolak, sebab
     * kabupaten pada DAS dikumpulkan sedikit demi sedikit oleh orang yang tahu
     * lapangan, dan satu klik keliru di layar wilayah sungai tidak boleh
     * menghapusnya diam-diam. DAS yang menghalangi disebut namanya supaya
     * admin tahu persis apa yang harus dibereskan lebih dulu.
     */
    private function kabupatenMasihDipakai(WilayahSungai $wilayah, array $pilihan): ?string
    {
        $dicabut = array_diff($wilayah->kabupaten->pluck('id')->all(), $pilihan);

        if ($dicabut === []) {
            return null;
        }

        $bentrok = DB::table('das_kabupaten')
            ->join('das', 'das_kabupaten.das_id', '=', 'das.id')
            ->join('kabupaten', 'das_kabupaten.kabupaten_id', '=', 'kabupaten.id')
            ->where('das.wilayah_sungai_id', $wilayah->id)
            ->whereIn('das_kabupaten.kabupaten_id', $dicabut)
            ->whereNull('das.deleted_at')
            ->orderBy('das.nama_das')
            ->get(['kabupaten.nama as kabupaten', 'das.nama_das as das'])
            ->groupBy('kabupaten');

        if ($bentrok->isEmpty()) {
            return null;
        }

        $kalimat = $bentrok->map(function ($baris, $kabupaten) {
            $nama = $baris->pluck('das');
            $sisa = $nama->count() - 2;

            return $kabupaten . ' masih dipakai ' . $nama->take(2)->join(', ')
                . ($sisa > 0 ? " dan {$sisa} DAS lain" : '');
        })->join('; ');

        return $kalimat . '. Ubah kabupaten pada DAS itu lebih dulu.';
    }

    public function destroy(WilayahSungai $wilayah)
    {
        if ($wilayah->das()->exists()) {
            return back()->withErrors([
                'hapus' => 'Wilayah sungai ini masih memiliki DAS sehingga tidak dapat dihapus. Hapus atau pindahkan DAS-nya lebih dulu.',
            ]);
        }

        $wilayah->delete();

        return redirect()->route('wilayah.index')->with('sukses', 'Wilayah sungai dihapus.');
    }

    private function validasi(Request $request, ?WilayahSungai $wilayah = null): array
    {
        return $request->validate([
            'nama_sungai' => ['required', 'string', 'max:150'],
            'kode_sungai' => [
                'required', 'string', 'max:30',
                Rule::unique('wilayah_sungai', 'kode_sungai')->ignore($wilayah?->id)->whereNull('deleted_at'),
            ],
            'deskripsi' => ['nullable', 'string'],
            'kabupaten_nama' => ['nullable', 'string', 'max:500'],
        ], [], [
            'nama_sungai' => 'nama wilayah sungai',
            'kode_sungai' => 'kode',
            'kabupaten_nama' => 'kabupaten',
        ]);
    }
}
