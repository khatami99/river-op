<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JuruSungai;
use App\Models\LaporanOp;
use App\Models\WilayahSungai;
use Illuminate\Http\Request;

class JuruSungaiWebController extends Controller
{
    public function index(Request $request)
    {
        $juru = JuruSungai::with('wilayahSungai')
            ->withCount('laporanOp')
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = $request->string('cari')->toString();
                $q->where(fn ($sub) => $sub->where('nama', 'like', "%{$cari}%")
                    ->orWhere('nip', 'like', "%{$cari}%")
                    ->orWhere('username', 'like', "%{$cari}%"));
            })
            ->when($request->filled('wilayah_sungai_id'), fn ($q) => $q->where('wilayah_sungai_id', $request->integer('wilayah_sungai_id')))
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return view('juru.index', [
            'juru' => $juru,
            'daftarWilayah' => WilayahSungai::orderBy('nama_sungai')->get(),
            'filter' => [
                'cari' => $request->input('cari'),
                'wilayah_sungai_id' => $request->input('wilayah_sungai_id'),
            ],
        ]);
    }

    public function show(Request $request, JuruSungai $juru)
    {
        $juru->load('wilayahSungai.kabupaten');

        $tahun = (int) $request->input('tahun', now()->year);

        $laporan = LaporanOp::where('juru_sungai_id', $juru->id)
            ->with('wilayahSungai')
            ->withCount('itemInspeksi')
            ->orderByDesc('tanggal_inspeksi')
            ->paginate(15)
            ->withQueryString();

        // Bulan mana saja yang sudah dilaporkan pada tahun terpilih.
        $bulanTerlapor = LaporanOp::where('juru_sungai_id', $juru->id)
            ->where('tahun', $tahun)
            ->distinct()
            ->pluck('bulan')
            ->all();

        return view('juru.show', [
            'juru' => $juru,
            'laporan' => $laporan,
            'tahun' => $tahun,
            'bulanTerlapor' => $bulanTerlapor,
            'daftarTahun' => $this->daftarTahun(),
        ]);
    }

    private function daftarTahun(): array
    {
        $paling_awal = (int) (LaporanOp::min('tahun') ?: now()->year);
        $sekarang = now()->year;

        return range($sekarang, min($paling_awal, $sekarang));
    }
}
