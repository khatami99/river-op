<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kabupaten sebelumnya menempel di wilayah sungai. Itu keliru: WS
 * Cengal-Batulicin saja membentang di Kotabaru, Tanah Bumbu, dan Tanah Laut,
 * sehingga satu kolom kabupaten pasti salah untuk sebagian datanya.
 *
 * Tempatnya diturunkan ke DAS, dan dibuat banyak-ke-banyak karena batas DAS
 * mengikuti punggung bukit sedangkan batas kabupaten digambar manusia — satu
 * DAS memang bisa melintasi dua kabupaten.
 *
 * Catatan penting: nilai ini label, bukan angka. Jangan dipakai menjumlahkan
 * laporan per kabupaten, karena DAS yang melintasi dua kabupaten akan
 * terhitung dua kali. Perhitungan per kabupaten baru sahih bila sudah ada
 * poligon batas wilayah dan dinilai dari titik GPS tiap bangunan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('das_kabupaten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('das_id')->constrained('das')->cascadeOnDelete();
            $table->foreignId('kabupaten_id')->constrained('kabupaten')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['das_id', 'kabupaten_id']);
        });

        if (! Schema::hasColumn('wilayah_sungai', 'kabupaten_id')) {
            return;
        }

        // Pengetahuan yang sudah ada dipindahkan apa adanya, bukan dibuang.
        // Belum tentu benar, tetapi admin bisa mengoreksinya lewat formulir DAS.
        $pasangan = DB::table('das')
            ->join('wilayah_sungai', 'das.wilayah_sungai_id', '=', 'wilayah_sungai.id')
            ->whereNotNull('wilayah_sungai.kabupaten_id')
            ->select('das.id as das_id', 'wilayah_sungai.kabupaten_id')
            ->get()
            ->map(fn ($b) => [
                'das_id' => $b->das_id,
                'kabupaten_id' => $b->kabupaten_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($pasangan !== []) {
            DB::table('das_kabupaten')->insert($pasangan);
        }

        Schema::table('wilayah_sungai', function (Blueprint $table) {
            $table->dropForeign(['kabupaten_id']);
            $table->dropColumn('kabupaten_id');
        });
    }

    public function down(): void
    {
        Schema::table('wilayah_sungai', function (Blueprint $table) {
            $table->foreignId('kabupaten_id')->nullable()->after('id')
                ->constrained('kabupaten')->nullOnDelete();
        });

        // Satu wilayah sungai hanya menampung satu kabupaten, jadi diambil
        // kabupaten mana pun yang tercatat pada DAS di bawahnya.
        $terpilih = DB::table('das_kabupaten')
            ->join('das', 'das_kabupaten.das_id', '=', 'das.id')
            ->select('das.wilayah_sungai_id', 'das_kabupaten.kabupaten_id')
            ->get()
            ->unique('wilayah_sungai_id');

        foreach ($terpilih as $baris) {
            DB::table('wilayah_sungai')
                ->where('id', $baris->wilayah_sungai_id)
                ->update(['kabupaten_id' => $baris->kabupaten_id]);
        }

        Schema::dropIfExists('das_kabupaten');
    }
};
