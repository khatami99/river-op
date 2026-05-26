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
        Schema::create('temuan_kondisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_inspeksi_id')->constrained('item_inspeksi')->onDelete('cascade');
            $table->foreignId('bagian_bangunan_id')->constrained('bagian_bangunan')->onDelete('restrict');
            $table->json('kondisi_ditemukan')->nullable();
            $table->text('tindakan_diperlukan')->nullable();
            $table->decimal('koordinat_lat', 10, 7)->nullable();
            $table->decimal('koordinat_lng', 10, 7)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('foto', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temuan_kondisi');
    }
};
