<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JuruSungai;

class SinkronisasiWebController extends Controller
{
    public function index()
    {
        // Seluruh juru ditampilkan, termasuk yang belum pernah melapor sama
        // sekali — justru itu keadaan yang paling perlu diketahui dinas,
        // karena bisa berarti aplikasinya tidak pernah dibuka.
        $juru = JuruSungai::with(['das', 'statusSinkronisasi'])
            ->where('aktif', true)
            ->orderBy('nama')
            ->get()
            ->sortBy(fn ($j) => match (true) {
                $j->statusSinkronisasi === null => 0,
                $j->statusSinkronisasi->jumlah_gagal > 0 => 1,
                $j->statusSinkronisasi->basi() => 2,
                $j->statusSinkronisasi->jumlah_tertunda > 0 => 3,
                default => 4,
            })
            ->values();

        return view('sinkronisasi.index', [
            'juru' => $juru,
            'belumPernah' => $juru->filter(fn ($j) => $j->statusSinkronisasi === null)->count(),
            'adaGagal' => $juru->filter(fn ($j) => $j->statusSinkronisasi?->jumlah_gagal > 0)->count(),
            'adaTertunda' => $juru->filter(fn ($j) => $j->statusSinkronisasi?->jumlah_tertunda > 0)->count(),
        ]);
    }
}
