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
        Schema::create('juru_sungai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wilayah_sungai_id')->constrained('wilayah_sungai')->onDelete('restrict');
            $table->string('nama', 100);
            $table->string('nip', 30)->nullable();
            $table->string('username', 50)->unique();
            $table->string('password', 255);
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
        Schema::dropIfExists('juru_sungai');
    }
};
