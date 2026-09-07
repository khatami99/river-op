<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jejak perubahan data master untuk pertanggungjawaban: siapa mengubah apa,
 * kapan, dan dari nilai berapa menjadi berapa.
 *
 * Hanya perubahan oleh staf dinas yang dicatat. Laporan dari juru sengaja
 * tidak ikut, sebab laporan bersifat sekali kirim dan tidak pernah diubah di
 * sisi server — jejaknya sudah ada pada laporan itu sendiri.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();

            // Pelaku dibiarkan null bila akunnya kelak dihapus, karena
            // jejaknya justru tidak boleh ikut hilang. Namanya disalin agar
            // tetap terbaca meski akunnya sudah tidak ada.
            $table->foreignId('staf_dinas_id')->nullable()->constrained('staf_dinas')->nullOnDelete();
            $table->string('nama_pelaku', 150);

            $table->string('aksi', 20);
            $table->string('jenis_data', 60);
            $table->unsignedBigInteger('data_id')->nullable();
            $table->string('ringkasan', 255);
            $table->json('perubahan')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['jenis_data', 'data_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};
