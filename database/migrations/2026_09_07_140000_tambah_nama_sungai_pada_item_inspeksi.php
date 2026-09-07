<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blangko OP menulis lokasi bangunan sebagai
 * "Sungai <nama> pada ruas antara patok Km A s/d Km B".
 *
 * Kilometernya sudah punya kolom sejak awal, tetapi nama sungainya belum ada
 * di mana pun. Tanpa itu baris Lokasi bangunan pada blangko hasil export tidak
 * pernah bisa lengkap.
 *
 * Disimpan per bangunan, bukan per laporan, karena satu kunjungan bisa
 * menyusuri lebih dari satu sungai — dan blangko pun menuliskannya per lembar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_inspeksi', function (Blueprint $table) {
            $table->string('nama_sungai', 150)->nullable()->after('nama_bangunan');
        });
    }

    public function down(): void
    {
        Schema::table('item_inspeksi', function (Blueprint $table) {
            $table->dropColumn('nama_sungai');
        });
    }
};
