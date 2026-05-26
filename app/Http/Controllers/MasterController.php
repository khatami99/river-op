<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use App\Models\WilayahSungai;
use App\Models\JenisBangunan;
use App\Models\BagianBangunan;
use App\Models\PilihanKondisi;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function sync()
    {
        $kabupaten = Kabupaten::where('aktif', 1)->get();
        $wilayahSungai = WilayahSungai::where('aktif', 1)->get();
        $jenisBangunan = JenisBangunan::where('aktif', 1)->orderBy('urutan')->get();
        $bagianBangunan = BagianBangunan::where('aktif', 1)->orderBy('urutan')->get();
        $pilihanKondisi = PilihanKondisi::where('aktif', 1)->orderBy('urutan')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data master berhasil diambil.',
            'data' => [
                'kabupaten' => $kabupaten,
                'wilayah_sungai' => $wilayahSungai,
                'jenis_bangunan' => $jenisBangunan,
                'bagian_bangunan' => $bagianBangunan,
                'pilihan_kondisi' => $pilihanKondisi,
            ]
        ], 200);
    }

    public function profil(Request $request)
    {
        $juruSungai = $request->user();

        $juruSungai->load('wilayahSungai.kabupaten');

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil.',
            'data' => $juruSungai
        ], 200);
    }
}
