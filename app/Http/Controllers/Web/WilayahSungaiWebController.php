<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WilayahSungai;
use Illuminate\Http\Request;

class WilayahSungaiWebController extends Controller
{
    public function index(Request $request)
    {
        $wilayah = WilayahSungai::with('kabupaten')
            ->withCount(['das', 'juruSungai', 'laporanOp'])
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
        $wilayah->load(['kabupaten', 'das' => fn ($q) => $q->withCount(['juruSungai', 'laporanOp'])->orderBy('nama_das')]);
        $wilayah->loadCount(['das', 'juruSungai', 'laporanOp']);

        return view('wilayah.show', ['wilayah' => $wilayah]);
    }
}
