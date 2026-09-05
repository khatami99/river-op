<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi perubahan data hanya untuk staf dinas berperan admin.
 * Staf berperan viewer tetap dapat melihat seluruh laporan, tetapi tidak
 * boleh menambah, mengubah, atau menghapus data master.
 */
class PastikanAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $staf = $request->user('staf');

        if (! $staf || ! $staf->isAdmin()) {
            abort(403, 'Hanya administrator yang dapat mengubah data.');
        }

        return $next($request);
    }
}
