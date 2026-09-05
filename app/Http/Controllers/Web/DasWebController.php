<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Das;
use Illuminate\Http\Request;

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
}
