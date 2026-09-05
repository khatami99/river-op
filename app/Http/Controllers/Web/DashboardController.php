<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JuruSungai;
use App\Models\LaporanOp;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $juruAktif = JuruSungai::where('aktif', true)->count();

        // Juru yang sudah mengirim minimal satu laporan pada periode ini.
        $idSudahLapor = LaporanOp::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->distinct()
            ->pluck('juru_sungai_id');

        $sudahLapor = $idSudahLapor->count();
        $belumLapor = max($juruAktif - $sudahLapor, 0);

        $juruBelumLapor = JuruSungai::with('das')
            ->where('aktif', true)
            ->whereNotIn('id', $idSudahLapor)
            ->orderBy('nama')
            ->get();

        $persentase = $juruAktif > 0 ? (int) round($sudahLapor / $juruAktif * 100) : 0;

        $laporanTerbaru = LaporanOp::with(['juruSungai', 'das'])
            ->latest('synced_at')
            ->limit(8)
            ->get();

        $totalLaporanPeriode = LaporanOp::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->count();

        return view('dashboard.index', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'juruAktif' => $juruAktif,
            'sudahLapor' => $sudahLapor,
            'belumLapor' => $belumLapor,
            'persentase' => $persentase,
            'juruBelumLapor' => $juruBelumLapor,
            'laporanTerbaru' => $laporanTerbaru,
            'totalLaporanPeriode' => $totalLaporanPeriode,
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
