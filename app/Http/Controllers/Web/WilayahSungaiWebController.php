<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WilayahSungai;
use Illuminate\Http\Request;
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

        return view('wilayah.index', [
            'dataPeta' => $dataPeta,
            'wilayah' => $wilayah,
            'filter' => ['cari' => $request->input('cari')],
        ]);
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
        $bolehTambahJuru = auth('staf')->user()?->isAdmin();

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
                'url_tambah_juru' => $bolehTambahJuru ? route('juru.create', ['das_id' => $d->id]) : null,
                'daftar_juru' => $d->juruSungai->map(fn ($j) => [
                    'nama' => $j->nama,
                    'telepon' => $j->no_telepon ?: '-',
                    'inisial' => mb_strtoupper(mb_substr($j->nama, 0, 1)),
                    'url' => route('juru.show', $j),
                ])->values(),
            ]);

        return view('wilayah.show', [
            'wilayah' => $wilayah,
            'dataPeta' => $dataPeta,
            'belumBerkoordinat' => $wilayah->das->filter(fn ($d) => ! $d->punyaKoordinat())->count(),
        ]);
    }

    public function create()
    {
        return view('wilayah.form', [
            'wilayah' => new WilayahSungai(['aktif' => true]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['aktif'] = $request->boolean('aktif');

        WilayahSungai::create($data);

        return redirect()->route('wilayah.index')->with('sukses', 'Wilayah sungai berhasil ditambahkan.');
    }

    public function edit(WilayahSungai $wilayah)
    {
        return view('wilayah.form', [
            'wilayah' => $wilayah,
        ]);
    }

    public function update(Request $request, WilayahSungai $wilayah)
    {
        $data = $this->validasi($request, $wilayah);
        $data['aktif'] = $request->boolean('aktif');

        $wilayah->update($data);

        return redirect()->route('wilayah.index')->with('sukses', 'Data wilayah sungai diperbarui.');
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
        ], [], [
            'nama_sungai' => 'nama wilayah sungai',
            'kode_sungai' => 'kode',
        ]);
    }
}
