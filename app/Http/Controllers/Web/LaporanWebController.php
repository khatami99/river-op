<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JuruSungai;
use App\Models\LaporanOp;
use App\Models\WilayahSungai;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanWebController extends Controller
{
    public function index(Request $request)
    {
        $laporan = $this->queryTerfilter($request)
            ->with(['juruSungai', 'wilayahSungai'])
            ->withCount('itemInspeksi')
            ->orderByDesc('tanggal_inspeksi')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('laporan.index', [
            'laporan' => $laporan,
            'daftarWilayah' => WilayahSungai::orderBy('nama_sungai')->get(),
            'daftarJuru' => JuruSungai::orderBy('nama')->get(),
            'daftarTahun' => $this->daftarTahun(),
            'filter' => $this->filterAktif($request),
        ]);
    }

    public function show(LaporanOp $laporan)
    {
        $laporan->load([
            'juruSungai',
            'wilayahSungai.kabupaten',
            'itemInspeksi.jenisBangunan',
            'itemInspeksi.temuanKondisi.bagianBangunan',
        ]);

        return view('laporan.show', ['laporan' => $laporan]);
    }

    public function export(Request $request): StreamedResponse
    {
        $laporan = $this->queryTerfilter($request)
            ->with(['juruSungai', 'wilayahSungai.kabupaten', 'itemInspeksi.temuanKondisi'])
            ->orderByDesc('tanggal_inspeksi')
            ->get();

        $namaFile = 'Laporan_OP_'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($laporan) {
            $keluaran = fopen('php://output', 'w');

            // BOM supaya Excel membaca karakter Indonesia dengan benar.
            fwrite($keluaran, "\xEF\xBB\xBF");

            fputcsv($keluaran, [
                'Tanggal Inspeksi', 'Juru Sungai', 'NIP', 'Wilayah Sungai', 'Kabupaten',
                'Cuaca', 'Debit Air', 'Jumlah Bangunan', 'Jumlah Temuan',
                'Temuan Bermasalah', 'Catatan Umum', 'Kendala', 'Disinkronkan',
            ], ';');

            foreach ($laporan as $item) {
                $semuaTemuan = $item->itemInspeksi->flatMap->temuanKondisi;
                $bermasalah = $semuaTemuan->filter(fn ($t) => $t->adaMasalah())->count();

                fputcsv($keluaran, [
                    optional($item->tanggal_inspeksi)->format('d/m/Y'),
                    $item->juruSungai?->nama,
                    $item->juruSungai?->nip,
                    $item->wilayahSungai?->nama_sungai,
                    $item->wilayahSungai?->kabupaten?->nama ?? '',
                    ucfirst($item->cuaca ?? ''),
                    ucfirst($item->debit_air ?? ''),
                    $item->itemInspeksi->count(),
                    $semuaTemuan->count(),
                    $bermasalah,
                    $item->catatan_umum,
                    $item->kendala,
                    optional($item->synced_at)->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($keluaran);
        }, $namaFile, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function queryTerfilter(Request $request)
    {
        return LaporanOp::query()
            ->when($request->filled('bulan'), fn ($q) => $q->where('bulan', $request->integer('bulan')))
            ->when($request->filled('tahun'), fn ($q) => $q->where('tahun', $request->integer('tahun')))
            ->when($request->filled('wilayah_sungai_id'), fn ($q) => $q->where('wilayah_sungai_id', $request->integer('wilayah_sungai_id')))
            ->when($request->filled('juru_sungai_id'), fn ($q) => $q->where('juru_sungai_id', $request->integer('juru_sungai_id')))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = $request->string('cari')->toString();
                $q->where(function ($sub) use ($cari) {
                    $sub->whereHas('juruSungai', fn ($j) => $j->where('nama', 'like', "%{$cari}%"))
                        ->orWhereHas('wilayahSungai', fn ($w) => $w->where('nama_sungai', 'like', "%{$cari}%"));
                });
            });
    }

    private function filterAktif(Request $request): array
    {
        return [
            'bulan' => $request->input('bulan'),
            'tahun' => $request->input('tahun'),
            'wilayah_sungai_id' => $request->input('wilayah_sungai_id'),
            'juru_sungai_id' => $request->input('juru_sungai_id'),
            'cari' => $request->input('cari'),
        ];
    }

    private function daftarTahun(): array
    {
        $paling_awal = (int) (LaporanOp::min('tahun') ?: now()->year);
        $sekarang = now()->year;

        return range($sekarang, min($paling_awal, $sekarang));
    }
}
