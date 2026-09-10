<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Pekerjaan terjadwal
|--------------------------------------------------------------------------
|
| Agar baris di bawah benar-benar berjalan, server harus punya satu entri
| cron yang memanggil penjadwal Laravel tiap menit:
|
|     * * * * * cd /jalur/ke/river-op && php artisan schedule:run >> /dev/null 2>&1
|
| Tanpa entri itu tidak ada yang terjadi, dan tidak ada pesan galat apa pun
| yang memberitahukannya — cadangan cuma diam-diam tidak pernah dibuat.
| Karena itu sesudah memasang, periksa sekali: `php artisan schedule:list`.
|
*/

// Dini hari, saat tidak ada juru yang menyinkronkan laporan.
Schedule::command('riverop:cadangkan')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->onOneServer();
