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
        Schema::create('pilihan_kondisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bagian_bangunan_id')->constrained('bagian_bangunan')->onDelete('restrict');
            $table->string('kondisi', 100);
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
        Schema::dropIfExists('pilihan_kondisi');
    }
};
