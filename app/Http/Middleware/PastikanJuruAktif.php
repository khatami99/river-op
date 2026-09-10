<?php

namespace App\Http\Middleware;

use App\Models\JuruSungai;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menolak permintaan dari juru yang sudah dinonaktifkan.
 *
 * Sebelum ini kolom `aktif` hanya diperiksa sekali, yaitu pada saat login
 * (AuthController::login). Sesudah token terbit, kolom itu tidak pernah
 * dilihat lagi sampai tokennya kedaluwarsa tiga puluh hari kemudian.
 *
 * Akibatnya PANDUAN-ADMIN.md berbohong. Di sana tertulis bahwa juru yang
 * sudah tidak bertugas sebaiknya "dinonaktifkan", padahal menonaktifkan
 * tidak mengerjakan apa pun terhadap HP yang sudah memegang token: juru itu
 * tetap dapat membuka aplikasi, tetap mencatat, dan laporannya tetap
 * DITERIMA server sebagai laporan resmi.
 *
 * Yang dikembalikan 401, bukan 403, dan itu disengaja: 401 adalah satu-satunya
 * kode yang sudah dikenali aplikasi juru sebagai "sesimu habis, masuk lagi".
 * Memakai 403 berarti aplikasi hanya menandai laporannya gagal terkirim lalu
 * mencobanya lagi tiap lima belas menit sampai kiamat, tanpa pernah memberi
 * tahu pemakainya apa yang sebenarnya terjadi.
 *
 * Tokennya sengaja TIDAK ikut dihapus. Penonaktifan kerap bersifat sementara
 * — juru cuti panjang, atau salah pencet — dan bila tokennya dimusnahkan,
 * mengaktifkan kembali akun itu tidak cukup untuk memulihkan keadaan: juru
 * harus dihubungi dan diminta masuk ulang. Cukup ditolak selama nonaktif,
 * dan hidup kembali sendiri begitu diaktifkan.
 */
class PastikanJuruAktif
{
    public function handle(Request $request, Closure $next): Response
    {
        $juru = $request->user();

        if ($juru instanceof JuruSungai && ! $juru->aktif) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini sudah dinonaktifkan oleh dinas. '
                    . 'Hubungi petugas dinas bila ini keliru.',
            ], 401);
        }

        return $next($request);
    }
}
