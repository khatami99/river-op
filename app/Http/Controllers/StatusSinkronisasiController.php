<?php

namespace App\Http\Controllers;

use App\Models\StatusSinkronisasi;
use Illuminate\Http\Request;

/**
 * Menerima laporan status sinkronisasi dari aplikasi juru.
 *
 * Dipisah dari endpoint sinkronisasi laporan karena justru dipanggil ketika
 * pengiriman laporan gagal — kalau digabung, kabar tentang kegagalan ikut
 * gagal terkirim.
 */
class StatusSinkronisasiController extends Controller
{
    public function lapor(Request $request)
    {
        $data = $request->validate([
            'jumlah_tertunda' => ['required', 'integer', 'min:0'],
            'jumlah_gagal' => ['required', 'integer', 'min:0'],
            'jumlah_terkirim' => ['required', 'integer', 'min:0'],
            'versi_aplikasi' => ['nullable', 'string', 'max:30'],
            'pesan_terakhir' => ['nullable', 'string', 'max:255'],
        ]);

        StatusSinkronisasi::updateOrCreate(
            ['juru_sungai_id' => $request->user()->id],
            $data + ['dilaporkan_pada' => now()]
        );

        return response()->json([
            'success' => true,
            'message' => 'Status sinkronisasi tercatat.',
        ]);
    }
}
