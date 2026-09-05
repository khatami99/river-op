<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Das;
use App\Models\WilayahSungai;
use Illuminate\Http\Request;
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
            ->paginate(20)
            ->withQueryString();

        return view('das.index', [
            'das' => $das,
            'filter' => ['cari' => $request->input('cari')],
        ]);
    }

    public function show(Das $das)
    {
        $das->load(['wilayahSungai.kabupaten', 'juruSungai']);
        $das->loadCount('laporanOp');

        $laporan = $das->laporanOp()
            ->with('juruSungai')
            ->withCount('itemInspeksi')
            ->orderByDesc('tanggal_inspeksi')
            ->paginate(15);

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
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['aktif'] = $request->boolean('aktif');

        Das::create($data);

        return redirect()->route('das.index')->with('sukses', 'DAS berhasil ditambahkan.');
    }

    public function edit(Das $das)
    {
        return view('das.form', [
            'das' => $das,
            'daftarWilayah' => WilayahSungai::where('aktif', true)->orderBy('nama_sungai')->get(),
        ]);
    }

    public function update(Request $request, Das $das)
    {
        $data = $this->validasi($request, $das);
        $data['aktif'] = $request->boolean('aktif');

        $das->update($data);

        return redirect()->route('das.index')->with('sukses', 'Data DAS diperbarui.');
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
        ], [], [
            'nama_das' => 'nama DAS',
            'kode_das' => 'kode DAS',
            'wilayah_sungai_id' => 'wilayah sungai',
        ]);
    }
}
