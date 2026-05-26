<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_inspeksi', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('laporan_op_id')->constrained('laporan_op')->onDelete('cascade');
            $table->foreignId('jenis_bangunan_id')->constrained('jenis_bangunan')->onDelete('restrict');
            $table->string('nama_bangunan', 150)->nullable();
            $table->decimal('lokasi_km_dari', 8, 3)->nullable();
            $table->decimal('lokasi_km_sampai', 8, 3)->nullable();
            $table->decimal('koordinat_lat', 10, 7)->nullable();
            $table->decimal('koordinat_lng', 10, 7)->nullable();
            $table->string('tipe_konstruksi', 200)->nullable();
            $table->text('catatan')->nullable();
            $table->string('foto_1', 255)->nullable();
            $table->string('foto_2', 255)->nullable();
            $table->string('foto_3', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_inspeksi');
    }
};
