<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Das;
use Illuminate\Support\Facades\DB;

class PetaWebController extends Controller
{
    public function index()
    {
        $das = Das::with(['wilayahSungai', 'juruSungai' => fn ($q) => $q->where('aktif', true)->orderBy('nama')])
            ->withCount([
                'juruSungai as jumlah_juru' => fn ($q) => $q->where('aktif', true),
                'laporanOp as jumlah_laporan',
            ])
            ->addSelect(['temuan_bermasalah' => DB::table('temuan_kondisi')
                ->selectRaw('COUNT(*)')
                ->join('item_inspeksi', 'item_inspeksi.id', '=', 'temuan_kondisi.item_inspeksi_id')
                ->join('laporan_op', 'laporan_op.id', '=', 'item_inspeksi.laporan_op_id')
                ->where('temuan_kondisi.ada_masalah', true)
                ->whereNull('laporan_op.deleted_at')
                ->whereColumn('laporan_op.das_id', 'das.id'),
            ])
            ->orderBy('nama_das')
            ->get();

        $bolehTambahJuru = auth('staf')->user()?->isAdmin();

        // Disiapkan sebagai JSON agar peta tidak perlu memanggil server lagi
        // setiap kali sebuah penanda diklik.
        $untukPeta = $das->map(fn ($d) => [
            'id' => $d->id,
            'nama' => $d->nama_das,
            'kode' => $d->kode_das,
            'wilayah' => $d->wilayahSungai?->nama_sungai ?? '-',
            'lat' => $d->koordinat_lat !== null ? (float) $d->koordinat_lat : null,
            'lng' => $d->koordinat_lng !== null ? (float) $d->koordinat_lng : null,
            'juru' => $d->jumlah_juru,
            'laporan' => $d->jumlah_laporan,
            'bermasalah' => (int) $d->temuan_bermasalah,
            'url' => route('das.show', $d),
            'url_tambah_juru' => $bolehTambahJuru ? route('juru.create', ['das_id' => $d->id]) : null,
            'daftar_juru' => $d->juruSungai->map(fn ($j) => [
                'nama' => $j->nama,
                'nip' => $j->nip ?: $j->username,
                'inisial' => mb_strtoupper(mb_substr($j->nama, 0, 1)),
                'url' => route('juru.show', $j),
            ])->values(),
        ]);

        return view('peta.index', [
            'das' => $das,
            'dataPeta' => $untukPeta,
            'belumBerkoordinat' => $das->filter(fn ($d) => ! $d->punyaKoordinat()),
        ]);
    }
}
