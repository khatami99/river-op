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
            ->paginate(20)
            ->withQueryString();

        return view('wilayah.index', [
            'wilayah' => $wilayah,
            'filter' => ['cari' => $request->input('cari')],
        ]);
    }

    public function show(WilayahSungai $wilayah)
    {
        $wilayah->load(['das' => fn ($q) => $q->withCount(['juruSungai', 'laporanOp'])->orderBy('nama_das')]);
        $wilayah->loadCount(['das', 'juruSungai', 'laporanOp']);

        return view('wilayah.show', ['wilayah' => $wilayah]);
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
