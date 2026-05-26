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
        Schema::create('wilayah_sungai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kabupaten_id')->constrained('kabupaten')->onDelete('restrict');
            $table->string('nama_sungai', 150);
            $table->string('kode_sungai', 30)->unique();
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wilayah_sungai');
    }
};
