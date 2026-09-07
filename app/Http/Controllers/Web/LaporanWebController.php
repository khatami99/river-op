<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Das;
use App\Models\JuruSungai;
use App\Models\LaporanOp;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanWebController extends Controller
{
    public function index(Request $request)
    {
        $laporan = $this->queryTerfilter($request)
            ->with(['juruSungai', 'das'])
            ->withCount('itemInspeksi')
            ->orderByDesc('tanggal_inspeksi')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('laporan.index', [
            'laporan' => $laporan,
            'daftarDas' => Das::orderBy('nama_das')->get(),
            'daftarJuru' => JuruSungai::orderBy('nama')->get(),
            'daftarTahun' => $this->daftarTahun(),
            'filter' => $this->filterAktif($request),
        ]);
    }

    public function show(LaporanOp $laporan)
    {
        $laporan->load([
            'juruSungai',
            'das.wilayahSungai',
            'das.kabupaten',
            'itemInspeksi.jenisBangunan',
            'itemInspeksi.temuanKondisi.bagianBangunan',
        ]);

        return view('laporan.show', ['laporan' => $laporan]);
    }

    public function export(Request $request): StreamedResponse
    {
        $laporan = $this->queryTerfilter($request)
            ->with(['juruSungai', 'das.wilayahSungai',
            'das.kabupaten', 'itemInspeksi.temuanKondisi'])
            ->orderByDesc('tanggal_inspeksi')
            ->get();

        $namaFile = 'Laporan_OP_'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($laporan) {
            $keluaran = fopen('php://output', 'w');

            // BOM supaya Excel membaca karakter Indonesia dengan benar.
            fwrite($keluaran, "\xEF\xBB\xBF");

            fputcsv($keluaran, [
                'Tanggal Inspeksi', 'Juru Sungai', 'NIP', 'DAS', 'Wilayah Sungai', 'Kabupaten',
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
                    $item->das?->nama_das,
                    $item->das?->wilayahSungai?->nama_sungai ?? '',
                    $item->das?->labelKabupaten() ?? '',
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
            ->when($request->filled('das_id'), fn ($q) => $q->where('das_id', $request->integer('das_id')))
            ->when($request->filled('juru_sungai_id'), fn ($q) => $q->where('juru_sungai_id', $request->integer('juru_sungai_id')))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = $request->string('cari')->toString();
                $q->where(function ($sub) use ($cari) {
                    $sub->whereHas('juruSungai', fn ($j) => $j->where('nama', 'like', "%{$cari}%"))
                        ->orWhereHas('das', fn ($d) => $d->where('nama_das', 'like', "%{$cari}%"));
                });
            });
    }

    private function filterAktif(Request $request): array
    {
        return [
            'bulan' => $request->input('bulan'),
            'tahun' => $request->input('tahun'),
            'das_id' => $request->input('das_id'),
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
