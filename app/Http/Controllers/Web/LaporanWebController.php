<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\BlangkoOpExporter;
use App\Services\TabelExcel;
use App\Models\Das;
use App\Models\ItemInspeksi;
use App\Models\JenisBangunan;
use App\Models\JuruSungai;
use App\Models\LaporanOp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanWebController extends Controller
{
    /**
     * Daftar laporan, dalam dua bentuk tampilan atas penyaring yang sama.
     *
     * Peta sebaran dulu berdiri sebagai halaman sendiri dengan penyaring yang
     * hampir sama persis. Itu membuat penyaringnya harus dipelihara dua kali,
     * dan memaksa pegawai mengulang penyaringan bila ingin melihat data yang
     * sama dalam bentuk lain. Peta kini menjadi cara lain melihat daftar ini.
     */
    public function index(Request $request)
    {
        $tampilan = $request->input('tampilan') === 'peta' ? 'peta' : 'tabel';

        $bersama = [
            'daftarDas' => Das::orderBy('nama_das')->get(),
            'daftarJuru' => JuruSungai::orderBy('nama')->get(),
            'daftarJenis' => JenisBangunan::where('aktif', true)->orderBy('urutan')->get(),
            'daftarTahun' => $this->daftarTahun(),
            'filter' => $this->filterAktif($request),
            'tampilan' => $tampilan,
        ];

        if ($tampilan === 'peta') {
            return view('laporan.index', $bersama + $this->dataPeta($request) + ['laporan' => null]);
        }

        return view('laporan.index', $bersama + [
            'laporan' => $this->queryTerfilter($request)
                ->with(['juruSungai', 'das'])
                ->withCount('itemInspeksi')
                ->orderByDesc('tanggal_inspeksi')
                ->orderByDesc('id')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    /**
     * Titik bangunan untuk tampilan peta.
     *
     * Satu penanda mewakili satu BANGUNAN, bukan satu laporan: koordinat
     * direkam per bangunan dan masalahnya melekat di sana, sedangkan satu
     * laporan bisa memuat bangunan yang berjauhan.
     */
    private function dataPeta(Request $request): array
    {
        $bangunan = ItemInspeksi::query()
            ->select('item_inspeksi.*')
            ->whereIn('laporan_op_id', $this->queryTerfilter($request)->select('laporan_op.id'))
            ->whereNotNull('koordinat_lat')
            ->whereNotNull('koordinat_lng')
            ->when($request->filled('jenis_bangunan_id'),
                fn ($q) => $q->where('jenis_bangunan_id', $request->integer('jenis_bangunan_id')))
            ->with(['jenisBangunan', 'laporanOp.juruSungai', 'laporanOp.das'])
            ->addSelect(['jumlah_bermasalah' => DB::table('temuan_kondisi')
                ->selectRaw('COUNT(*)')
                ->whereColumn('temuan_kondisi.item_inspeksi_id', 'item_inspeksi.id')
                ->where('temuan_kondisi.ada_masalah', true),
            ])
            ->get();

        if ($request->boolean('bermasalah')) {
            $bangunan = $bangunan->filter(fn ($b) => $b->jumlah_bermasalah > 0)->values();
        }

        // Laporan dari sebelum aplikasi juru menangkap GPS tidak punya titik
        // dan tidak akan pernah punya, sebab koordinatnya tidak bisa dikarang.
        // Jumlahnya ditampilkan agar peta yang sepi tidak disalahartikan.
        $tanpaTitik = ItemInspeksi::whereIn('laporan_op_id', $this->queryTerfilter($request)->select('laporan_op.id'))
            ->whereNull('koordinat_lat')
            ->count();

        return [
            'dataTitik' => $bangunan->map(fn ($b) => [
                'lat' => (float) $b->koordinat_lat,
                'lng' => (float) $b->koordinat_lng,
                'jenis' => $b->jenisBangunan?->nama ?? 'Bangunan',
                'kode' => $b->jenisBangunan?->kode,
                'nama' => $b->nama_bangunan,
                'sungai' => $b->nama_sungai,
                'das' => $b->laporanOp?->das?->nama_das,
                'juru' => $b->laporanOp?->juruSungai?->nama,
                'tanggal' => optional($b->laporanOp?->tanggal_inspeksi)->format('d/m/Y'),
                'bermasalah' => (int) $b->jumlah_bermasalah,
                'url' => $b->laporanOp ? route('laporan.show', $b->laporanOp) : null,
            ]),
            'dataDas' => Das::whereNotNull('koordinat_lat')->whereNotNull('koordinat_lng')
                ->orderBy('nama_das')->get()
                ->map(fn ($d) => [
                    'nama' => $d->nama_das, 'kode' => $d->kode_das,
                    'lat' => (float) $d->koordinat_lat, 'lng' => (float) $d->koordinat_lng,
                    'url' => route('das.show', $d),
                ]),
            'jumlahBermasalah' => $bangunan->filter(fn ($b) => $b->jumlah_bermasalah > 0)->count(),
            'tanpaTitik' => $tanpaTitik,
        ];
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

    /**
     * Mengunduh laporan dalam bentuk blangko OP resmi. Dipakai dinas untuk
     * arsip, menggantikan pengetikan ulang hasil aplikasi ke Excel.
     */
    public function blangko(LaporanOp $laporan, BlangkoOpExporter $blangko)
    {
        $wb = $blangko->untukLaporan($laporan);
        $nama = $blangko->namaBerkas($laporan) . '.xlsx';

        return response()->streamDownload(function () use ($wb) {
            IOFactory::createWriter($wb, 'Xlsx')->save('php://output');
        }, $nama, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Blangko yang sama dalam bentuk PDF, untuk arsip yang tidak boleh berubah.
     * Isinya dibaca dari sumber yang sama dengan keluaran Excel supaya
     * keduanya tidak mungkin berbeda.
     */
    public function blangkoPdf(LaporanOp $laporan, BlangkoOpExporter $blangko)
    {
        $pdf = Pdf::loadView('laporan.blangko-pdf', [
            'laporan' => $laporan,
            'lembar' => $blangko->lembarUntukPdf($laporan),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($blangko->namaBerkas($laporan) . '.pdf');
    }

    public function export(Request $request)
    {
        $laporan = $this->queryTerfilter($request)
            ->with(['juruSungai', 'das.wilayahSungai',
            'das.kabupaten', 'itemInspeksi.temuanKondisi'])
            ->orderByDesc('tanggal_inspeksi')
            ->get();

        $tabel = (new TabelExcel('Laporan OP'))
            ->judul('DAFTAR LAPORAN OP SUNGAI')
            ->keterangan('Diunduh', now()->format('d/m/Y H:i'))
            ->keterangan('Jumlah laporan', (string) $laporan->count())
            ->kosong()
            ->kepala([
                'Tanggal Inspeksi', 'Juru Sungai', 'DAS', 'Wilayah Sungai', 'Kabupaten',
                'Cuaca', 'Debit Air', 'Jumlah Bangunan', 'Jumlah Temuan',
                'Temuan Bermasalah', 'Catatan Umum', 'Kendala', 'Disinkronkan',
            ]);

        foreach ($laporan as $item) {
            $semuaTemuan = $item->itemInspeksi->flatMap->temuanKondisi;

            $tabel->baris([
                optional($item->tanggal_inspeksi)->format('d/m/Y'),
                $item->juruSungai?->nama,
                $item->das?->nama_das,
                $item->das?->wilayahSungai?->nama_sungai ?? '',
                $item->das?->labelKabupaten() ?? '',
                ucfirst($item->cuaca ?? ''),
                ucfirst($item->debit_air ?? ''),
                $item->itemInspeksi->count(),
                $semuaTemuan->count(),
                $semuaTemuan->filter(fn ($t) => $t->adaMasalah())->count(),
                $item->catatan_umum,
                $item->kendala,
                optional($item->synced_at)->format('d/m/Y H:i'),
            ]);
        }

        return $this->unduhExcel($tabel->selesai(), 'Laporan_OP_'.now()->format('Y-m-d_His'));
    }

    /** Dipakai bersama oleh seluruh export Excel di controller ini. */
    private function unduhExcel(\PhpOffice\PhpSpreadsheet\Spreadsheet $wb, string $nama)
    {
        return response()->streamDownload(function () use ($wb) {
            IOFactory::createWriter($wb, 'Xlsx')->save('php://output');
        }, $nama.'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function queryTerfilter(Request $request)
    {
        return LaporanOp::query()
            ->when($request->filled('bulan'), fn ($q) => $q->where('bulan', $request->integer('bulan')))
            ->when($request->filled('tahun'), fn ($q) => $q->where('tahun', $request->integer('tahun')))
            ->when($request->filled('das_id'), fn ($q) => $q->where('das_id', $request->integer('das_id')))
            ->when($request->filled('juru_sungai_id'), fn ($q) => $q->where('juru_sungai_id', $request->integer('juru_sungai_id')))
            ->when($request->filled('jenis_bangunan_id'), fn ($q) => $q->whereHas('itemInspeksi',
                fn ($i) => $i->where('jenis_bangunan_id', $request->integer('jenis_bangunan_id'))))
            ->when($request->boolean('bermasalah'), fn ($q) => $q->whereHas('itemInspeksi.temuanKondisi',
                fn ($t) => $t->where('ada_masalah', true)))
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
            'jenis_bangunan_id' => $request->input('jenis_bangunan_id'),
            'bermasalah' => $request->boolean('bermasalah'),
        ];
    }

    private function daftarTahun(): array
    {
        $paling_awal = (int) (LaporanOp::min('tahun') ?: now()->year);
        $sekarang = now()->year;

        return range($sekarang, min($paling_awal, $sekarang));
    }
}
