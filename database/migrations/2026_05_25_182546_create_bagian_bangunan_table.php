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
        Schema::create('bagian_bangunan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_bangunan_id')->constrained('jenis_bangunan')->onDelete('restrict');
            $table->string('nama', 150);
            $table->integer('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bagian_bangunan');
    }
};
