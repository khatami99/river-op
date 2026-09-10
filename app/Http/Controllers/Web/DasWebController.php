<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Das;
use App\Models\JuruSungai;
use App\Models\WilayahSungai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class DasWebController extends Controller
{
    public function index(Request $request)
    {
        $das = Das::with('wilayahSungai')
            ->withCount(['juruSungai', 'laporanOp'])
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = $request->string('cari')->toString();
                $q->where(fn ($sub) => $sub->where('nama_das', 'like', "%{$cari}%")
                    ->orWhere('kode_das', 'like', "%{$cari}%"));
            })
            ->orderBy('nama_das')
            ->paginate(10)
            ->withQueryString();

        // Peta DAS dulu berdiri sebagai halaman sendiri. Padahal peta itu cara
        // melihat DAS, bukan hal lain: menaruhnya di atas daftar membuat satu
        // halaman menjawab "DAS mana saja yang ada" sekaligus "letaknya di mana".
        $semuaDas = Das::with(['wilayahSungai', 'juruSungai' => fn ($q) => $q->where('aktif', true)->orderBy('nama')])
            ->withCount([
                'juruSungai as jumlah_juru' => fn ($q) => $q->where('aktif', true),
                'laporanOp as jumlah_laporan',
            ])
            ->addSelect(['temuan_bermasalah' => DB::table('temuan_kondisi')
                ->selectRaw('COUNT(*)')
                ->join('item_inspeksi', 'item_inspeksi.id', '=', 'temuan_kondisi.item_inspeksi_id')
                ->join('laporan_op', 'laporan_op.id', '=', 'item_inspeksi.laporan_op_id')
                ->where('temuan_kondisi.ada_masalah', true)
                ->whereNull('laporan_op.deleted_at')
                ->whereColumn('laporan_op.das_id', 'das.id'),
            ])
            ->orderBy('nama_das')
            ->get();

        $bolehTambahJuru = auth('staf')->user()?->isAdmin();

        $data = [
            'das' => $das,
            'filter' => ['cari' => $request->input('cari')],
            'dataPeta' => $semuaDas->filter(fn ($d) => $d->punyaKoordinat())->values()->map(fn ($d) => [
                'id' => $d->id,
                'nama' => $d->nama_das,
                'kode' => $d->kode_das,
                'wilayah' => $d->wilayahSungai?->nama_sungai ?? '-',
                'lat' => (float) $d->koordinat_lat,
                'lng' => (float) $d->koordinat_lng,
                'juru' => $d->jumlah_juru,
                'laporan' => $d->jumlah_laporan,
                'bermasalah' => (int) $d->temuan_bermasalah,
                'url' => route('das.show', $d),
                'url_tambah_juru' => $bolehTambahJuru ? route('juru.create', ['das_id' => $d->id]) : null,
                'daftar_juru' => $d->juruSungai->map(fn ($j) => [
                    'nama' => $j->nama,
                    'telepon' => $j->no_telepon ?: '-',
                    'inisial' => mb_strtoupper(mb_substr($j->nama, 0, 1)),
                    'url' => route('juru.show', $j),
                ])->values(),
            ]),
            'belumBerkoordinat' => $semuaDas->filter(fn ($d) => ! $d->punyaKoordinat())->values(),
        ];

        // Permintaan potongan datang dari penyaring yang berubah di layar.
        // Hanya area hasilnya yang dikirim ulang, sehingga kursor pengguna
        // tidak lepas dari kotak pencarian dan halaman tidak berkedip.
        if ($request->boolean('fragmen')) {
            return response()->json([
                'hasil' => view('das._hasil', $data)->render(),
                'jumlah' => $data['das']->total() . ' daerah aliran sungai terdaftar',
            ]);
        }

        return view('das.index', $data);
    }

    public function show(Das $das)
    {
        $das->load(['wilayahSungai', 'kabupaten', 'juruSungai']);
        $das->loadCount('laporanOp');

        $laporan = $das->laporanOp()
            ->with('juruSungai')
            ->withCount('itemInspeksi')
            ->orderByDesc('tanggal_inspeksi')
            ->paginate(10);

        return view('das.show', [
            'das' => $das,
            'laporan' => $laporan,
        ]);
    }

    public function create()
    {
        return view('das.form', [
            'das' => new Das(['aktif' => true]),
            'daftarWilayah' => $this->wilayahBesertaKabupaten(),
            'daftarJuru' => $this->daftarJuru(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['aktif'] = $request->boolean('aktif');

        $das = Das::create(Arr::except($data, ['kabupaten_ids', 'juru_ids']));
        $das->kabupaten()->sync($data['kabupaten_ids'] ?? []);
        $kabar = $this->tugaskanJuru($das, $data['juru_ids'] ?? []);

        return redirect()->route('das.index')
            ->with('sukses', trim('DAS berhasil ditambahkan. ' . $kabar));
    }

    public function edit(Das $das)
    {
        $das->load(['kabupaten', 'juruSungai' => fn ($q) => $q->orderBy('nama')]);

        return view('das.form', [
            'das' => $das,
            'daftarWilayah' => $this->wilayahBesertaKabupaten(),
            'daftarJuru' => $this->daftarJuru(),
        ]);
    }

    public function update(Request $request, Das $das)
    {
        $data = $this->validasi($request, $das);
        $data['aktif'] = $request->boolean('aktif');

        $das->update(Arr::except($data, ['kabupaten_ids', 'juru_ids']));
        $das->kabupaten()->sync($data['kabupaten_ids'] ?? []);
        $kabar = $this->tugaskanJuru($das, $data['juru_ids'] ?? []);

        return redirect()->route('das.index')
            ->with('sukses', trim('Data DAS diperbarui. ' . $kabar));
    }

    public function destroy(Das $das)
    {
        if ($das->juruSungai()->exists() || $das->laporanOp()->exists()) {
            return back()->withErrors([
                'hapus' => 'DAS ini masih dipakai juru sungai atau laporan sehingga tidak dapat dihapus. Nonaktifkan saja.',
            ]);
        }

        $das->delete();

        return redirect()->route('das.index')->with('sukses', 'DAS dihapus.');
    }

    /**
     * Wilayah sungai beserta kabupaten yang disinggungnya.
     *
     * Kabupatennya ikut karena formulir DAS menggambar satu blok kotak
     * centang untuk tiap wilayah sungai sekaligus, lalu membuka yang sesuai
     * pilihan. Tanpa dimuat di sini, penggambaran itu akan menembak basis
     * data sekali untuk setiap wilayah sungai.
     */
    private function wilayahBesertaKabupaten()
    {
        return WilayahSungai::with('kabupaten')
            ->where('aktif', true)
            ->orderBy('nama_sungai')
            ->get();
    }

    /**
     * Semua juru, bukan hanya yang aktif.
     *
     * Juru nonaktif pun tetap tercatat memegang DAS, dan kalau ia tidak ikut
     * di daftar ini chipnya akan tampil sebagai angka id tanpa nama —
     * kelihatan seperti data rusak padahal cuma tidak dikenali.
     */
    private function daftarJuru()
    {
        return JuruSungai::with('das')->orderBy('nama')->get();
    }

    /**
     * Menetapkan siapa saja yang bertugas di DAS ini.
     *
     * juru_sungai.das_id berarti satu juru punya tepat satu DAS, jadi ini
     * bukan tabel penghubung yang bisa disinkronkan: menugaskan juru ke sini
     * otomatis mencabutnya dari DAS sebelumnya, dan mencopotnya dari sini
     * meninggalkannya tanpa DAS sama sekali.
     *
     * Keduanya dihitung lebih dulu lalu dikabarkan pada pesan berhasil.
     * Perpindahan yang diminta lewat satu layar tetapi berakibat pada layar
     * lain tidak boleh berlalu tanpa disebut — admin harus tahu bahwa DAS
     * Kusan barusan kehilangan jurunya.
     */
    private function tugaskanJuru(Das $das, array $diminta): string
    {
        $sah = JuruSungai::whereIn('id', collect($diminta)->map('intval')->unique())
            ->pluck('id')
            ->all();
        $tanpaSah = $sah === [] ? [0] : $sah;

        $dipindah = JuruSungai::whereIn('id', $sah)
            ->whereNotNull('das_id')
            ->where('das_id', '!=', $das->id)
            ->count();

        $dicopot = JuruSungai::where('das_id', $das->id)
            ->whereNotIn('id', $tanpaSah)
            ->count();

        DB::transaction(function () use ($das, $sah, $tanpaSah) {
            JuruSungai::where('das_id', $das->id)
                ->whereNotIn('id', $tanpaSah)
                ->update(['das_id' => null]);

            if ($sah !== []) {
                JuruSungai::whereIn('id', $sah)->update(['das_id' => $das->id]);
            }
        });

        return collect([
            $dipindah > 0 ? "{$dipindah} juru dipindahkan dari DAS lain." : null,
            $dicopot > 0 ? "{$dicopot} juru kini tanpa DAS." : null,
        ])->filter()->join(' ');
    }

    private function validasi(Request $request, ?Das $das = null): array
    {
        return $request->validate([
            'nama_das' => ['required', 'string', 'max:150'],
            'kode_das' => [
                'required', 'string', 'max:30',
                Rule::unique('das', 'kode_das')->ignore($das?->id)->whereNull('deleted_at'),
            ],
            'wilayah_sungai_id' => ['required', 'exists:wilayah_sungai,id'],
            'deskripsi' => ['nullable', 'string'],
            'koordinat_lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:koordinat_lng'],
            'koordinat_lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:koordinat_lat'],
            // Kotak centang sungguhan, jadi datang sebagai larik. Sebelumnya
            // isian ini berbentuk chip dan mengirim untai id dipisah koma;
            // panel centang tidak memerlukan perantara itu dan sekaligus tetap
            // bekerja tanpa JavaScript.
            'juru_ids' => ['nullable', 'array'],
            'juru_ids.*' => [
                'integer',
                Rule::exists('juru_sungai', 'id')->whereNull('deleted_at'),
            ],

            'kabupaten_ids' => ['nullable', 'array'],

            // Bukan sekadar "kabupaten ini ada", melainkan "kabupaten ini
            // disinggung wilayah sungai yang dipilih". Di layar aturan itu
            // sudah dijaga dengan menyembunyikan pilihan yang tidak berlaku,
            // tetapi penjagaan di layar hanya berlaku bagi orang yang memakai
            // layar — pengiriman yang dirakit sendiri melewatinya begitu saja.
            'kabupaten_ids.*' => [
                'integer',
                Rule::exists('wilayah_sungai_kabupaten', 'kabupaten_id')
                    ->where('wilayah_sungai_id', $request->input('wilayah_sungai_id')),
            ],
        ], [
            'kabupaten_ids.*.exists' => 'Kabupaten yang dipilih tidak disinggung wilayah sungai ini. Tambahkan lebih dulu pada data wilayah sungainya.',
            'juru_ids.*.exists' => 'Ada juru yang sudah tidak terdaftar lagi. Muat ulang halaman ini lalu pilih kembali.',
        ], [
            'nama_das' => 'nama DAS',
            'kode_das' => 'kode DAS',
            'wilayah_sungai_id' => 'wilayah sungai',
            'kabupaten_ids' => 'kabupaten',
            'juru_ids' => 'juru',
        ]);
    }
}
