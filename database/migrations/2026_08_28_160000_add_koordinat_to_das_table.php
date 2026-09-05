<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Titik penanda DAS pada peta, ditentukan admin dinas.
 *
 * Ini sengaja hanya berupa satu titik, bukan batas wilayah. Batas DAS yang
 * sebenarnya adalah data geospasial resmi; menggambarnya sendiri berisiko
 * keliru dan tidak layak untuk sistem instansi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('das', function (Blueprint $table) {
            $table->decimal('koordinat_lat', 10, 7)->nullable()->after('deskripsi');
            $table->decimal('koordinat_lng', 10, 7)->nullable()->after('koordinat_lat');
        });
    }

    public function down(): void
    {
        Schema::table('das', function (Blueprint $table) {
            $table->dropColumn(['koordinat_lat', 'koordinat_lng']);
        });
    }
};
