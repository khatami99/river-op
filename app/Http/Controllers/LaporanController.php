<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LaporanOp;
use App\Models\ItemInspeksi;
use App\Models\TemuanKondisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LaporanController extends Controller
{
    public function uploadFoto(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        try {
            $file = $request->file('file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('foto_laporan', $filename, 'public');
            $url = Storage::url($path);

            return response()->json([
                'status' => 'success',
                'url' => $url
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'url' => null,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sync(Request $request)
    {
        // ... semua kode sync yang sudah ada, tidak berubah sama sekali
        $request->validate([
            'laporan' => 'required|array',
            'laporan.*.uuid' => 'required|uuid',
        ]);

        $juruSungai = $request->user();
        $syncedUuids = [];

        DB::beginTransaction();

        try {
            foreach ($request->input('laporan') as $laporanData) {
                $uuid = $laporanData['uuid'];

                $exists = LaporanOp::where('uuid', $uuid)->exists();

                if ($exists) {
                    $syncedUuids[] = $uuid;
                    continue;
                }

                $laporanOp = LaporanOp::create([
                    'uuid' => $uuid,
                    'juru_sungai_id' => $juruSungai->id,
                    'wilayah_sungai_id' => $laporanData['wilayah_sungai_id'] ?? $juruSungai->wilayah_sungai_id,
                    'tanggal_inspeksi' => $laporanData['tanggal_inspeksi'],
                    'bulan' => $laporanData['bulan'],
                    'tahun' => $laporanData['tahun'],
                    'cuaca' => $laporanData['cuaca'] ?? null,
                    'debit_air' => $laporanData['debit_air'] ?? null,
                    'catatan_umum' => $laporanData['catatan_umum'] ?? null,
                    'kendala' => $laporanData['kendala'] ?? null,
                    'foto_lokasi_1' => $laporanData['foto_lokasi_1'] ?? null,
                    'foto_lokasi_2' => $laporanData['foto_lokasi_2'] ?? null,
                    'sync_status' => 'synced',
                    'dibuat_di_hp' => $laporanData['dibuat_di_hp'] ?? null,
                    'synced_at' => now(),
                ]);

                if (isset($laporanData['item_inspeksi']) && is_array($laporanData['item_inspeksi'])) {
                    foreach ($laporanData['item_inspeksi'] as $itemData) {
                        $itemUuid = $itemData['uuid'];

                        $itemExists = ItemInspeksi::where('uuid', $itemUuid)->exists();
                        if ($itemExists) {
                            continue;
                        }

                        $itemInspeksi = ItemInspeksi::create([
                            'uuid' => $itemUuid,
                            'laporan_op_id' => $laporanOp->id,
                            'jenis_bangunan_id' => $itemData['jenis_bangunan_id'],
                            'nama_bangunan' => $itemData['nama_bangunan'] ?? null,
                            'lokasi_km_dari' => $itemData['lokasi_km_dari'] ?? null,
                            'lokasi_km_sampai' => $itemData['lokasi_km_sampai'] ?? null,
                            'koordinat_lat' => $itemData['koordinat_lat'] ?? null,
                            'koordinat_lng' => $itemData['koordinat_lng'] ?? null,
                            'tipe_konstruksi' => $itemData['tipe_konstruksi'] ?? null,
                            'catatan' => $itemData['catatan'] ?? null,
                            'foto_1' => $itemData['foto_1'] ?? null,
                            'foto_2' => $itemData['foto_2'] ?? null,
                            'foto_3' => $itemData['foto_3'] ?? null,
                        ]);

                        if (isset($itemData['temuan_kondisi']) && is_array($itemData['temuan_kondisi'])) {
                            foreach ($itemData['temuan_kondisi'] as $temuanData) {
                                TemuanKondisi::create([
                                    'item_inspeksi_id' => $itemInspeksi->id,
                                    'bagian_bangunan_id' => $temuanData['bagian_bangunan_id'],
                                    'kondisi_ditemukan' => $temuanData['kondisi_ditemukan'] ?? null,
                                    'tindakan_diperlukan' => $temuanData['tindakan_diperlukan'] ?? null,
                                    'koordinat_lat' => $temuanData['koordinat_lat'] ?? null,
                                    'koordinat_lng' => $temuanData['koordinat_lng'] ?? null,
                                    'keterangan' => $temuanData['keterangan'] ?? null,
                                    'foto' => $temuanData['foto'] ?? null,
                                    'foto_2' => $temuanData['foto_2'] ?? null,
                                    'foto_3' => $temuanData['foto_3'] ?? null,
                                ]);
                            }
                        }
                    }
                }

                $syncedUuids[] = $uuid;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi berhasil.',
                'synced_uuids' => $syncedUuids
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Sinkronisasi gagal: ' . $e->getMessage(),
                'synced_uuids' => []
            ], 500);
        }
    }
}
