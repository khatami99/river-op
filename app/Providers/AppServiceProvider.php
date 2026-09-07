<?php

namespace App\Providers;

use App\Models\Das;
use App\Models\JuruSungai;
use App\Models\StafDinas;
use App\Models\WilayahSungai;
use App\Observers\PencatatAktivitas;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Data master yang perubahannya perlu dipertanggungjawabkan. Laporan
        // dari juru tidak ikut diawasi karena bersifat sekali kirim dan tidak
        // pernah diubah di sisi server.
        foreach ([JuruSungai::class, Das::class, WilayahSungai::class, StafDinas::class] as $model) {
            $model::observe(PencatatAktivitas::class);
        }
    }
}
