<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Das;
use App\Models\JuruSungai;
use App\Models\LaporanOp;
use App\Support\Bulan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RekapWebController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $rekap = $this->rekapPerDas($bulan, $tahun);

        return view('rekap.index', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'rekap' => $rekap,
            'total' => $this->totalkan($rekap),
            'juruBelumLapor' => $this->juruBelumLapor($bulan, $tahun),
            'daftarTahun' => $this->daftarTahun(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $rekap = $this->rekapPerDas($bulan, $tahun);
        $total = $this->totalkan($rekap);
        $belum = $this->juruBelumLapor($bulan, $tahun);

        $namaFile = 'Rekap_OP_'.Bulan::nama($bulan).'_'.$tahun.'.csv';

        return response()->streamDownload(function () use ($rekap, $total, $belum, $bulan, $tahun) {
            $keluaran = fopen('php://output', 'w');
            fwrite($keluaran, "\xEF\xBB\xBF");

            fputcsv($keluaran, ['REKAPITULASI LAPORAN OP SUNGAI'], ';');
            fputcsv($keluaran, ['Periode', Bulan::nama($bulan).' '.$tahun], ';');
            fputcsv($keluaran, [], ';');

            fputcsv($keluaran, [
                'DAS', 'Jumlah Juru', 'Sudah Lapor', 'Belum Lapor',
                'Jumlah Laporan', 'Bangunan Diperiksa', 'Temuan Bermasalah',
            ], ';');

            foreach ($rekap as $baris) {
                fputcsv($keluaran, [
                    $baris->nama_das,
                    $baris->jumlah_juru,
                    $baris->sudah_lapor,
                    $baris->belum_lapor,
                    $baris->jumlah_laporan,
                    $baris->jumlah_bangunan,
                    $baris->temuan_bermasalah,
                ], ';');
            }

            fputcsv($keluaran, [
                'TOTAL', $total['juru'], $total['sudah'], $total['belum'],
                $total['laporan'], $total['bangunan'], $total['bermasalah'],
            ], ';');

            if ($belum->isNotEmpty()) {
                fputcsv($keluaran, [], ';');
                fputcsv($keluaran, ['DAFTAR JURU BELUM MELAPOR'], ';');
                fputcsv($keluaran, ['Nama', 'NIP', 'DAS'], ';');
                foreach ($belum as $orang) {
                    fputcsv($keluaran, [$orang->nama, $orang->nip, $orang->das?->nama_das], ';');
                }
            }

            fclose($keluaran);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Seluruh angka dihitung lewat subquery agar tidak perlu memuat
     * ribuan baris laporan ke PHP hanya untuk dijumlahkan.
     */
    private function rekapPerDas(int $bulan, int $tahun)
    {
        $laporanPeriode = fn ($q) => $q->where('laporan_op.bulan', $bulan)
            ->where('laporan_op.tahun', $tahun)
            ->whereNull('laporan_op.deleted_at');

        return Das::query()
            ->select('das.id', 'das.nama_das', 'das.kode_das')
            ->withCount(['juruSungai as jumlah_juru' => fn ($q) => $q->where('aktif', true)])
            ->addSelect([
                'sudah_lapor' => JuruSungai::selectRaw('COUNT(DISTINCT juru_sungai.id)')
                    ->whereColumn('juru_sungai.das_id', 'das.id')
                    ->where('juru_sungai.aktif', true)
                    ->whereNull('juru_sungai.deleted_at')
                    ->whereExists(fn ($q) => $q->from('laporan_op')
                        ->whereColumn('laporan_op.juru_sungai_id', 'juru_sungai.id')
                        ->tap($laporanPeriode)),

                'jumlah_laporan' => LaporanOp::selectRaw('COUNT(*)')
                    ->whereColumn('laporan_op.das_id', 'das.id')
                    ->tap($laporanPeriode),

                'jumlah_bangunan' => DB::table('item_inspeksi')
                    ->selectRaw('COUNT(*)')
                    ->join('laporan_op', 'laporan_op.id', '=', 'item_inspeksi.laporan_op_id')
                    ->whereColumn('laporan_op.das_id', 'das.id')
                    ->tap($laporanPeriode),

                'temuan_bermasalah' => DB::table('temuan_kondisi')
                    ->selectRaw('COUNT(*)')
                    ->join('item_inspeksi', 'item_inspeksi.id', '=', 'temuan_kondisi.item_inspeksi_id')
                    ->join('laporan_op', 'laporan_op.id', '=', 'item_inspeksi.laporan_op_id')
                    ->where('temuan_kondisi.ada_masalah', true)
                    ->whereColumn('laporan_op.das_id', 'das.id')
                    ->tap($laporanPeriode),
            ])
            ->orderBy('das.nama_das')
            ->get()
            ->each(function ($baris) {
                $baris->belum_lapor = max($baris->jumlah_juru - $baris->sudah_lapor, 0);
            });
    }

    private function totalkan($rekap): array
    {
        return [
            'juru' => $rekap->sum('jumlah_juru'),
            'sudah' => $rekap->sum('sudah_lapor'),
            'belum' => $rekap->sum('belum_lapor'),
            'laporan' => $rekap->sum('jumlah_laporan'),
            'bangunan' => $rekap->sum('jumlah_bangunan'),
            'bermasalah' => $rekap->sum('temuan_bermasalah'),
        ];
    }

    private function juruBelumLapor(int $bulan, int $tahun)
    {
        $sudah = LaporanOp::where('bulan', $bulan)->where('tahun', $tahun)->distinct()->pluck('juru_sungai_id');

        return JuruSungai::with('das')
            ->where('aktif', true)
            ->whereNotIn('id', $sudah)
            ->orderBy('nama')
            ->get();
    }

    private function daftarTahun(): array
    {
        $paling_awal = (int) (LaporanOp::min('tahun') ?: now()->year);
        $sekarang = now()->year;

        return range($sekarang, min($paling_awal, $sekarang));
    }
}
