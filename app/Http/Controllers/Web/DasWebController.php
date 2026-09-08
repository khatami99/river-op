<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Das;
use App\Models\Kabupaten;
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

        return view('das.index', [
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
        ]);
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
            'daftarWilayah' => WilayahSungai::where('aktif', true)->orderBy('nama_sungai')->get(),
            'daftarKabupaten' => Kabupaten::where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['aktif'] = $request->boolean('aktif');

        $das = Das::create(Arr::except($data, 'kabupaten_ids'));
        $das->kabupaten()->sync($data['kabupaten_ids'] ?? []);

        return redirect()->route('das.index')->with('sukses', 'DAS berhasil ditambahkan.');
    }

    public function edit(Das $das)
    {
        $das->load('kabupaten');

        return view('das.form', [
            'das' => $das,
            'daftarWilayah' => WilayahSungai::where('aktif', true)->orderBy('nama_sungai')->get(),
            'daftarKabupaten' => Kabupaten::where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Das $das)
    {
        $data = $this->validasi($request, $das);
        $data['aktif'] = $request->boolean('aktif');

        $das->update(Arr::except($data, 'kabupaten_ids'));
        $das->kabupaten()->sync($data['kabupaten_ids'] ?? []);

        return redirect()->route('das.index')->with('sukses', 'Data DAS diperbarui.');
    }

    /**
     * Pengisian kabupaten untuk seluruh DAS dalam satu layar.
     *
     * Kabupaten ikut tercetak pada blangko OP yang diserahkan ke dinas, dan
     * saat ini hampir seluruhnya kosong. Mengisinya lewat formulir edit
     * berarti 62 kali buka, centang, simpan — pekerjaan yang tidak akan
     * pernah selesai.
     *
     * Centang, bukan pilihan tunggal: satu DAS bisa membentang di dua
     * kabupaten.
     */
    public function kabupaten()
    {
        return view('das.kabupaten', [
            'daftarDas' => Das::with('kabupaten')->orderBy('nama_das')->get(),
            'daftarKabupaten' => Kabupaten::orderBy('nama')->get(),
        ]);
    }

    public function simpanKabupaten(Request $request)
    {
        $terpilih = (array) $request->input('kabupaten', []);
        $sah = Kabupaten::pluck('id')->all();
        $berubah = 0;

        DB::transaction(function () use ($terpilih, $sah, &$berubah) {
            foreach (Das::with('kabupaten')->get() as $das) {
                // Hanya id kabupaten yang benar-benar ada yang diterima; sisanya
                // dibuang, sebab isian ini datang dari formulir.
                $baru = array_values(array_intersect(
                    array_map('intval', (array) ($terpilih[$das->id] ?? [])),
                    $sah
                ));

                $lama = $das->kabupaten->pluck('id')->sort()->values()->all();
                sort($baru);

                if ($lama !== $baru) {
                    $das->kabupaten()->sync($baru);
                    $berubah++;
                }
            }
        });

        return redirect()->route('das.kabupaten')->with(
            'sukses',
            $berubah === 0
                ? 'Tidak ada perubahan kabupaten.'
                : "Kabupaten diperbarui pada {$berubah} DAS."
        );
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
            'kabupaten_ids' => ['nullable', 'array'],
            'kabupaten_ids.*' => ['integer', 'exists:kabupaten,id'],
        ], [], [
            'nama_das' => 'nama DAS',
            'kode_das' => 'kode DAS',
            'wilayah_sungai_id' => 'wilayah sungai',
            'kabupaten_ids' => 'kabupaten',
        ]);
    }
}
