<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Models\StafDinas;
use Illuminate\Http\Request;

class LogAktivitasWebController extends Controller
{
    public function index(Request $request)
    {
        $log = LogAktivitas::query()
            ->when($request->filled('pelaku'), fn ($q) => $q->where('staf_dinas_id', $request->input('pelaku')))
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis_data', $request->input('jenis')))
            ->when($request->filled('aksi'), fn ($q) => $q->where('aksi', $request->input('aksi')))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $data = [
            'log' => $log,
            'filter' => [
                'pelaku' => $request->input('pelaku'),
                'jenis' => $request->input('jenis'),
                'aksi' => $request->input('aksi'),
            ],
            'daftarPelaku' => StafDinas::orderBy('nama')->get(['id', 'nama']),
            'daftarJenis' => LogAktivitas::distinct()->orderBy('jenis_data')->pluck('jenis_data'),
        ];

        if ($request->boolean('fragmen')) {
            return response()->json([
                'hasil' => view('log._hasil', $data)->render(),
                'jumlah' => $data['log']->total() . ' catatan aktivitas',
            ]);
        }

        return view('log.index', $data);
    }
}
