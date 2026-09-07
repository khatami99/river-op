<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Das;
use App\Models\ItemInspeksi;
use App\Models\JenisBangunan;
use App\Models\LaporanOp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Peta sebaran laporan: seluruh bangunan dari seluruh laporan dalam satu peta.
 *
 * Berbeda tujuan dari Peta DAS. Peta DAS menjawab "siapa memegang wilayah
 * mana" — pertanyaan tentang organisasi. Halaman ini menjawab "masalahnya
 * menumpuk di mana" — pertanyaan tentang kondisi lapangan, yang tidak bisa
 * dilihat dari tabel karena mata tidak menangkap pola geografis dari deretan
 * baris.
 *
 * Satu penanda mewakili satu BANGUNAN, bukan satu laporan. Koordinat memang
 * direkam per bangunan, dan masalahnya pun melekat di bangunan; satu laporan
 * bisa memuat beberapa bangunan yang berjauhan sehingga menandainya dengan
 * satu titik akan berbohong.
 */
class SebaranWebController extends Controller
{
    public function index(Request $request)
    {
        $filter = [
            'das' => $request->input('das'),
            'jenis' => $request->input('jenis'),
            'bulan' => $request->input('bulan'),
            'tahun' => $request->input('tahun'),
            'bermasalah' => $request->boolean('bermasalah'),
        ];

        $bangunan = ItemInspeksi::query()
            ->select('item_inspeksi.*')
            ->join('laporan_op', 'laporan_op.id', '=', 'item_inspeksi.laporan_op_id')
            ->whereNull('laporan_op.deleted_at')
            ->whereNotNull('item_inspeksi.koordinat_lat')
            ->whereNotNull('item_inspeksi.koordinat_lng')
            ->with(['jenisBangunan', 'laporanOp.juruSungai', 'laporanOp.das'])
            // Dihitung di SQL supaya warna penanda tidak perlu memuat seluruh
            // temuan tiap bangunan ke PHP.
            ->addSelect(['jumlah_bermasalah' => DB::table('temuan_kondisi')
                ->selectRaw('COUNT(*)')
                ->whereColumn('temuan_kondisi.item_inspeksi_id', 'item_inspeksi.id')
                ->where('temuan_kondisi.ada_masalah', true),
            ])
            ->when($filter['das'], fn ($q, $v) => $q->where('laporan_op.das_id', $v))
            ->when($filter['jenis'], fn ($q, $v) => $q->where('item_inspeksi.jenis_bangunan_id', $v))
            ->when($filter['bulan'], fn ($q, $v) => $q->where('laporan_op.bulan', $v))
            ->when($filter['tahun'], fn ($q, $v) => $q->where('laporan_op.tahun', $v))
            ->orderByDesc('laporan_op.tanggal_inspeksi')
            ->get();

        if ($filter['bermasalah']) {
            $bangunan = $bangunan->filter(fn ($b) => $b->jumlah_bermasalah > 0)->values();
        }

        $titik = $bangunan->map(fn ($b) => [
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
        ]);

        // Titik DAS ikut dikirim sebagai lapisan terpisah yang bisa dimatikan.
        // Nanti, bila poligon batas DAS sudah didapat, lapisan ini tinggal
        // diganti isinya tanpa mengubah kerangka halaman.
        $titikDas = Das::whereNotNull('koordinat_lat')
            ->whereNotNull('koordinat_lng')
            ->orderBy('nama_das')
            ->get()
            ->map(fn ($d) => [
                'nama' => $d->nama_das,
                'kode' => $d->kode_das,
                'lat' => (float) $d->koordinat_lat,
                'lng' => (float) $d->koordinat_lng,
                'url' => route('das.show', $d),
            ]);

        return view('sebaran.index', [
            'dataTitik' => $titik,
            'dataDas' => $titikDas,
            'filter' => $filter,
            'jumlahBermasalah' => $bangunan->filter(fn ($b) => $b->jumlah_bermasalah > 0)->count(),
            'daftarDas' => Das::orderBy('nama_das')->get(['id', 'nama_das']),
            'daftarJenis' => JenisBangunan::where('aktif', true)->orderBy('urutan')->get(['id', 'nama', 'kode']),
            'daftarTahun' => LaporanOp::select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun'),
            'tanpaTitik' => $this->jumlahTanpaTitik(),
        ]);
    }

    /**
     * Laporan dari sebelum aplikasi juru menangkap GPS tidak punya koordinat
     * dan tidak akan pernah punya, karena titiknya tidak bisa dikarang.
     * Jumlahnya ditampilkan supaya peta yang terlihat sepi tidak disalahartikan
     * sebagai tidak ada pekerjaan.
     */
    private function jumlahTanpaTitik(): int
    {
        return ItemInspeksi::join('laporan_op', 'laporan_op.id', '=', 'item_inspeksi.laporan_op_id')
            ->whereNull('laporan_op.deleted_at')
            ->whereNull('item_inspeksi.koordinat_lat')
            ->count();
    }
}
