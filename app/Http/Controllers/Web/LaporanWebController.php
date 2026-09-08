<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\BlangkoOpExporter;
use App\Support\Bulan;
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

        $data = $bersama + ($tampilan === 'peta'
            ? $this->dataPeta($request) + ['laporan' => null]
            : ['laporan' => $this->queryTerfilter($request)
                ->with(['juruSungai', 'das'])
                ->withCount('itemInspeksi')
                ->orderByDesc('tanggal_inspeksi')
                ->orderByDesc('id')
                ->paginate(10)
                ->withQueryString()]);

        // Permintaan potongan datang dari penyaring yang berubah di layar.
        // Hanya area hasilnya yang dikirim ulang, sehingga kursor pengguna
        // tidak lepas dari kotak pencarian dan halaman tidak berkedip.
        if ($request->boolean('fragmen')) {
            return response()->json([
                'hasil' => view('laporan._hasil', $data)->render(),
                'jumlah' => $tampilan === 'peta'
                    ? $data['dataTitik']->count() . ' bangunan bertitik, ' . $data['jumlahBermasalah'] . ' di antaranya bermasalah'
                    : $data['laporan']->total() . ' laporan ditemukan',
            ]);
        }

        return view('laporan.index', $data);
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

        return $this->unduhExcel($wb, substr($nama, 0, -5));
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

        return $pdf->download($blangko->namaBerkas($laporan) . '.pdf')
            ->withCookie(cookie('unduhan_siap', '1', 1, '/', null, false, false));
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

    /**
     * Dipakai bersama oleh seluruh export Excel di controller ini.
     *
     * Berkasnya dibangun ke memori lebih dulu, bukan dialirkan langsung.
     * Dengan aliran, kepala tanggapan terkirim sebelum berkasnya selesai
     * dibuat, sehingga kuki penanda selesai sampai ke peramban terlalu awal
     * dan penanda di tombol berhenti sebelum waktunya.
     */
    private function unduhExcel(\PhpOffice\PhpSpreadsheet\Spreadsheet $wb, string $nama)
    {
        ob_start();
        IOFactory::createWriter($wb, 'Xlsx')->save('php://output');
        $isi = ob_get_clean();

        return response($isi, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$nama.'.xlsx"',
            'Content-Length' => strlen($isi),
        ])->cookie('unduhan_siap', '1', 1, '/', null, false, false);
    }

    /**
     * Periode diperbandingkan sebagai satu angka tahun-bulan, misalnya Agustus
     * 2026 menjadi 202608. Tanpa itu, rentang yang melewati pergantian tahun
     * seperti November 2026 sampai Februari 2027 akan salah, sebab bulan 11
     * lebih besar daripada bulan 2.
     */
    private function queryTerfilter(Request $request)
    {
        $juru = array_filter((array) $request->input('juru_sungai_id', []));

        return $this->saringPeriode(LaporanOp::query(), $request)

            ->when($request->filled('das_id'), fn ($q) => $q->where('das_id', $request->integer('das_id')))
            ->when($juru !== [], fn ($q) => $q->whereIn('juru_sungai_id', $juru))
            ->when($request->filled('jenis_bangunan_id'), fn ($q) => $q->whereHas('itemInspeksi',
                fn ($i) => $i->where('jenis_bangunan_id', $request->integer('jenis_bangunan_id'))))
            ->when($request->boolean('bermasalah'), fn ($q) => $q->whereHas('itemInspeksi.temuanKondisi',
                fn ($t) => $t->where('ada_masalah', true)))
            ->when($request->filled('cari'), fn ($q) => $this->cariBebas($q, $request->string('cari')->toString()));
    }

    /**
     * Pencarian bebas: satu kotak untuk seluruh isi laporan.
     *
     * Pegawai tidak perlu memilih dulu hendak mencari di kolom mana. Yang
     * ditelusuri: nama juru, nama DAS, nama wilayah sungai, cuaca, debit air,
     * catatan, kendala, nama bulan, tahun, dan jumlah bangunan.
     *
     * Nama bulan hanya dicocokkan bila ketikannya minimal tiga huruf dan
     * menjadi awalan nama bulan. Tanpa batas itu, mengetik "ma" akan menyeret
     * seluruh laporan bulan Maret dan Mei tanpa pengguna memintanya.
     */
    private function cariBebas($query, string $cari)
    {
        $suku = trim($cari);

        return $query->where(function ($sub) use ($suku) {
            $sub->whereHas('juruSungai', fn ($j) => $j->where('nama', 'like', "%{$suku}%"))
                ->orWhereHas('das', fn ($d) => $d->where('nama_das', 'like', "%{$suku}%"))
                ->orWhereHas('das.wilayahSungai', fn ($w) => $w->where('nama_sungai', 'like', "%{$suku}%"))
                ->orWhere('cuaca', 'like', "%{$suku}%")
                ->orWhere('debit_air', 'like', "%{$suku}%")
                ->orWhere('catatan_umum', 'like', "%{$suku}%")
                ->orWhere('kendala', 'like', "%{$suku}%");

            $bulan = $this->bulanYangCocok($suku);
            if ($bulan !== []) {
                $sub->orWhereIn('bulan', $bulan);
            }

            // Tahun selalu empat angka, sedangkan jumlah bangunan tidak pernah
            // sebanyak itu, sehingga keduanya tidak mungkin saling tertukar.
            if (ctype_digit($suku)) {
                strlen($suku) === 4
                    ? $sub->orWhere('tahun', (int) $suku)
                    : $sub->orHas('itemInspeksi', '=', (int) $suku);
            }
        });
    }

    /** Nomor bulan yang namanya diawali ketikan pengguna. */
    private function bulanYangCocok(string $suku): array
    {
        if (mb_strlen($suku) < 3) {
            return [];
        }

        $cocok = [];

        foreach (Bulan::NAMA as $nomor => $nama) {
            if (str_starts_with(mb_strtolower($nama), mb_strtolower($suku))) {
                $cocok[] = $nomor;
            }
        }

        return $cocok;
    }

    /**
     * Menyaring periode. Ada empat kemungkinan isian, dan semuanya harus jalan
     * karena pengguna bebas mengisi sebagian saja:
     *
     * - bulan dan tahun          -> bulan itu saja
     * - bulan, tahun, dan sampai -> rentang, boleh melewati pergantian tahun
     * - tahun saja               -> sepanjang tahun itu
     * - bulan saja               -> bulan itu pada tahun mana pun
     *
     * Rentang diperbandingkan sebagai satu angka tahun-bulan, misalnya Agustus
     * 2026 menjadi 202608. Tanpa itu, rentang November 2026 sampai Februari
     * 2027 akan salah, sebab bulan 11 lebih besar daripada bulan 2.
     */
    private function saringPeriode($query, Request $request)
    {
        $adaBulan = $request->filled('bulan');
        $adaTahun = $request->filled('tahun');

        if ($adaBulan && $adaTahun) {
            $awal = $this->kodePeriode($request->input('tahun'), $request->input('bulan'));

            // Tanpa batas akhir, yang dicari bulan itu saja — bukan bulan itu
            // dan seterusnya.
            $akhir = $request->filled('bulan_sampai')
                ? $this->kodePeriode(
                    $request->input('tahun_sampai') ?: $request->input('tahun'),
                    $request->input('bulan_sampai')
                )
                : $awal;

            return $query
                ->whereRaw('(tahun * 100 + bulan) >= ?', [$awal])
                ->whereRaw('(tahun * 100 + bulan) <= ?', [$akhir]);
        }

        if ($adaTahun) {
            return $query->where('tahun', $request->integer('tahun'));
        }

        if ($adaBulan) {
            return $request->filled('bulan_sampai')
                ? $query->whereBetween('bulan', [$request->integer('bulan'), $request->integer('bulan_sampai')])
                : $query->where('bulan', $request->integer('bulan'));
        }

        return $query;
    }

    /** Mengubah tahun dan bulan menjadi angka yang bisa diurutkan, atau null. */
    private function kodePeriode(mixed $tahun, mixed $bulan): ?int
    {
        if (! $tahun || ! $bulan) {
            return null;
        }

        return ((int) $tahun) * 100 + ((int) $bulan);
    }

    private function filterAktif(Request $request): array
    {
        return [
            'bulan' => $request->input('bulan'),
            'tahun' => $request->input('tahun'),
            'bulan_sampai' => $request->input('bulan_sampai'),
            'tahun_sampai' => $request->input('tahun_sampai'),
            'das_id' => $request->input('das_id'),
            'juru_sungai_id' => array_filter((array) $request->input('juru_sungai_id', [])),
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
