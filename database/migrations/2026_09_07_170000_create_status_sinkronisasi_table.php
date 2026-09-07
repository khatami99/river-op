<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laporan status sinkronisasi dari tiap HP juru.
 *
 * Tabel ini ada karena server tidak mungkin mengetahuinya sendiri: laporan
 * yang GAGAL terkirim, menurut definisinya, tidak pernah sampai ke server.
 * Status TERTUNDA dan GAGAL hanya hidup di HP juru, sehingga aplikasi harus
 * melaporkannya secara terpisah.
 *
 * Satu baris per juru, ditimpa setiap kali melapor. Riwayatnya tidak disimpan
 * karena yang ditanyakan dinas selalu "sekarang ada yang tersendat atau
 * tidak", bukan "minggu lalu bagaimana".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_sinkronisasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('juru_sungai_id')->unique()->constrained('juru_sungai')->cascadeOnDelete();
            $table->unsignedInteger('jumlah_tertunda')->default(0);
            $table->unsignedInteger('jumlah_gagal')->default(0);
            $table->unsignedInteger('jumlah_terkirim')->default(0);
            $table->string('versi_aplikasi', 30)->nullable();
            $table->string('pesan_terakhir', 255)->nullable();
            $table->timestamp('dilaporkan_pada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_sinkronisasi');
    }
};
