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
        Schema::create('laporan_op', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('juru_sungai_id')->constrained('juru_sungai')->onDelete('restrict');
            $table->foreignId('wilayah_sungai_id')->constrained('wilayah_sungai')->onDelete('restrict');
            $table->date('tanggal_inspeksi');
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->enum('cuaca', ['cerah', 'berawan', 'hujan'])->nullable();
            $table->enum('debit_air', ['rendah', 'normal', 'tinggi', 'banjir'])->nullable();
            $table->text('catatan_umum')->nullable();
            $table->text('kendala')->nullable();
            $table->string('foto_lokasi_1', 255)->nullable();
            $table->string('foto_lokasi_2', 255)->nullable();
            $table->enum('sync_status', ['synced', 'conflict'])->default('synced');
            $table->timestamp('dibuat_di_hp')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_op');
    }
};
