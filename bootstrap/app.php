<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Di balik Cloudflare atau proxy hosting, permintaan sampai ke Laravel
        // sebagai HTTP biasa. Tanpa ini Laravel menuliskan alamat aset sebagai
        // http:// pada halaman yang dilayani https://, dan peramban memblokir
        // seluruhnya — dasbor terbuka tanpa gaya dan tanpa tombol yang jalan.
        $middleware->trustProxies(at: '*');

        // Dashboard dinas memakai guard 'staf'; tamu diarahkan ke halaman login web.
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));

        $middleware->alias([
            'admin' => \App\Http\Middleware\PastikanAdmin::class,
            'juru.aktif' => \App\Http\Middleware\PastikanJuruAktif::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Rute API tidak boleh dialihkan ke halaman login web.
        //
        // Bawaan Laravel mengalihkan permintaan tak terautentikasi yang tidak
        // meminta JSON, dan aplikasi juru memang tidak memintanya. Akibatnya
        // token kedaluwarsa membuat POST /api/laporan/sync berakhir di HTML
        // berkode 200: dari sisi klien terbaca sebagai berhasil, padahal
        // laporannya tidak pernah sampai.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi tidak sah atau sudah berakhir. Silakan masuk kembali.',
                ], 401);
            }
        });
    })->create();
