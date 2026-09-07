<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Juru sungai bukan PNS, jadi mereka tidak punya NIP. Kolom itu dulu
 * ditambahkan tanpa dasar: blangko OP resmi pun tidak pernah memintanya —
 * kepalanya hanya menanyakan siapa yang melakukan inspeksi, lalu ditutup tanda
 * tangan bernama.
 *
 * Gantinya dua kolom yang benar-benar ada pada mereka:
 *
 * - nomor telepon, yang berguna tepat ketika dinas hendak menagih juru yang
 *   belum melapor;
 * - nomor SK pengangkatan, yang diterbitkan per angkatan per tahun sehingga
 *   satu SK memuat beberapa orang. Karena itu SK BUKAN pembeda antarjuru, dan
 *   sengaja tidak ditampilkan di tabel daftar.
 *
 * Yang membedakan juru di mata dinas adalah nama dan DAS wilayah kerjanya
 * ("juru Kintap"), dan keduanya sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('juru_sungai', function (Blueprint $table) {
            $table->string('no_telepon', 25)->nullable()->after('nama');
            $table->string('nomor_sk', 100)->nullable()->after('no_telepon');
        });

        Schema::table('juru_sungai', function (Blueprint $table) {
            $table->dropColumn('nip');
        });
    }

    public function down(): void
    {
        Schema::table('juru_sungai', function (Blueprint $table) {
            $table->string('nip', 30)->nullable()->after('nama');
            $table->dropColumn(['no_telepon', 'nomor_sk']);
        });
    }
};
