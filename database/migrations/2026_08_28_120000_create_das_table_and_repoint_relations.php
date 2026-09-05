<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyisipkan tingkat DAS antara wilayah sungai dan juru sungai.
 *
 * Di Kalimantan Selatan hanya ada satu wilayah sungai yang menjadi wewenang
 * provinsi, yaitu Wilayah Sungai Cengal, dan pembagian tugas juru dilakukan
 * per DAS. Sebelumnya tabel `wilayah_sungai` justru diisi sungai satuan yang
 * sebenarnya adalah DAS, sehingga juru melihat label yang keliru.
 *
 * Baris `wilayah_sungai` yang lama dipindahkan menjadi DAS dengan id yang sama
 * persis, sehingga pengisian ulang relasi cukup menyalin nilai kolom lama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('das', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wilayah_sungai_id')->constrained('wilayah_sungai')->onDelete('restrict');
            $table->string('nama_das', 150);
            $table->string('kode_das', 30)->unique();
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Pada instalasi baru migration berjalan sebelum seeder, sehingga tabel
        // masih kosong dan tidak ada apa pun yang perlu dipindahkan.
        $lama = DB::table('wilayah_sungai')->get();

        if ($lama->isNotEmpty()) {
            // Wilayah sungai wewenang Provinsi Kalimantan Selatan.
            $idCengal = DB::table('wilayah_sungai')->insertGetId([
                'kabupaten_id' => $lama->first()->kabupaten_id,
                'nama_sungai' => 'Wilayah Sungai Cengal',
                'kode_sungai' => 'WS-CENGAL',
                'deskripsi' => 'Satu-satunya wilayah sungai yang menjadi wewenang Provinsi Kalimantan Selatan.',
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Baris lama dipindahkan menjadi DAS dengan id yang sama persis,
            // sehingga pengisian ulang relasi cukup menyalin nilai kolom lama.
            foreach ($lama as $baris) {
                DB::table('das')->insert([
                    'id' => $baris->id,
                    'wilayah_sungai_id' => $idCengal,
                    'nama_das' => $baris->nama_sungai,
                    'kode_das' => str_replace('WS-', 'DAS-', $baris->kode_sungai),
                    'deskripsi' => $baris->deskripsi,
                    'aktif' => $baris->aktif,
                    'created_at' => $baris->created_at ?? now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Arahkan juru sungai ke DAS.
        Schema::table('juru_sungai', function (Blueprint $table) {
            $table->foreignId('das_id')->nullable()->after('id')->constrained('das')->onDelete('restrict');
        });
        DB::statement('UPDATE juru_sungai SET das_id = wilayah_sungai_id');
        Schema::table('juru_sungai', function (Blueprint $table) {
            $table->dropForeign(['wilayah_sungai_id']);
            $table->dropColumn('wilayah_sungai_id');
        });

        // Arahkan laporan ke DAS.
        Schema::table('laporan_op', function (Blueprint $table) {
            $table->foreignId('das_id')->nullable()->after('juru_sungai_id')->constrained('das')->onDelete('restrict');
        });
        DB::statement('UPDATE laporan_op SET das_id = wilayah_sungai_id');
        Schema::table('laporan_op', function (Blueprint $table) {
            $table->dropForeign(['wilayah_sungai_id']);
            $table->dropColumn('wilayah_sungai_id');
        });

        // Baris lama sudah tidak dirujuk siapa pun.
        if ($lama->isNotEmpty()) {
            DB::table('wilayah_sungai')->whereIn('id', $lama->pluck('id'))->delete();
        }
    }

    public function down(): void
    {
        $das = DB::table('das')->get();

        foreach ($das as $baris) {
            DB::table('wilayah_sungai')->insert([
                'id' => $baris->id,
                'kabupaten_id' => DB::table('kabupaten')->value('id'),
                'nama_sungai' => $baris->nama_das,
                'kode_sungai' => str_replace('DAS-', 'WS-', $baris->kode_das),
                'deskripsi' => $baris->deskripsi,
                'aktif' => $baris->aktif,
                'created_at' => $baris->created_at,
                'updated_at' => now(),
            ]);
        }

        Schema::table('juru_sungai', function (Blueprint $table) {
            $table->foreignId('wilayah_sungai_id')->nullable()->after('id')->constrained('wilayah_sungai')->onDelete('restrict');
        });
        DB::statement('UPDATE juru_sungai SET wilayah_sungai_id = das_id');
        Schema::table('juru_sungai', function (Blueprint $table) {
            $table->dropForeign(['das_id']);
            $table->dropColumn('das_id');
        });

        Schema::table('laporan_op', function (Blueprint $table) {
            $table->foreignId('wilayah_sungai_id')->nullable()->after('juru_sungai_id')->constrained('wilayah_sungai')->onDelete('restrict');
        });
        DB::statement('UPDATE laporan_op SET wilayah_sungai_id = das_id');
        Schema::table('laporan_op', function (Blueprint $table) {
            $table->dropForeign(['das_id']);
            $table->dropColumn('das_id');
        });

        // Tabel das harus dibuang lebih dulu, karena selama masih ada
        // foreign key-nya menghalangi penghapusan baris Cengal.
        Schema::dropIfExists('das');
        DB::table('wilayah_sungai')->where('kode_sungai', 'WS-CENGAL')->delete();
    }
};
