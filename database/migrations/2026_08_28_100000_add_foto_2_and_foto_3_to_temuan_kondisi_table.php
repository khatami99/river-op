<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aplikasi Android mengirim sampai 3 foto per temuan, tetapi tabel hanya
 * menyimpan kolom `foto`. Akibatnya foto ke-2 dan ke-3 sudah terunggah ke
 * storage tapi URL-nya tidak pernah tersimpan sehingga menjadi file yatim.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('temuan_kondisi', function (Blueprint $table) {
            $table->string('foto_2', 255)->nullable()->after('foto');
            $table->string('foto_3', 255)->nullable()->after('foto_2');
        });
    }

    public function down(): void
    {
        Schema::table('temuan_kondisi', function (Blueprint $table) {
            $table->dropColumn(['foto_2', 'foto_3']);
        });
    }
};
