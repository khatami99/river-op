<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dua kekurangan yang saling berkaitan, diperbaiki sekaligus.
 *
 * PERTAMA, tabel kabupaten cuma berisi tiga baris — Tanah Laut, Tanah Bumbu,
 * dan Kotabaru — yaitu yang disinggung WS Cengal-Batulicin. Padahal tidak ada
 * satu pun layar untuk menambah kabupaten. Begitu dinas menambah wilayah
 * sungai di Tabalong, admin membuka formulir DAS dan Tabalong tidak ada di
 * sana, tanpa jalan apa pun untuk mengadakannya. Ketiga belas kabupaten dan
 * kota Kalimantan Selatan dimasukkan sekarang; daftarnya ditetapkan
 * undang-undang dan berubah sekali dalam satu dekade, jadi tidak perlu menu
 * pengelolaan, cukup ada.
 *
 * KEDUA, dengan tiga belas pilihan, formulir DAS jadi tiga belas kotak
 * centang yang harus dibaca satu per satu untuk memilih satu atau dua — dan
 * tidak ada yang mencegah DAS di Batulicin dicentang Tabalong, yang mustahil
 * secara geografis. Maka kabupaten diberi rumah di wilayah sungai lebih dulu.
 * Wilayah sungai adalah gabungan DAS di bawahnya, sehingga kabupaten yang
 * disinggung sebuah DAS pasti termasuk yang disinggung wilayah sungainya.
 * Formulir DAS kini hanya menawarkan kabupaten milik wilayah sungainya:
 * daftarnya pendek, dan kombinasi yang mustahil tidak bisa dipilih.
 *
 * Ini TIDAK mengembalikan keadaan sebelum kabupaten dipindah ke DAS. Dulu
 * wilayah sungai memegang satu kabupaten dan DAS tidak memegang apa-apa,
 * sehingga sebagian datanya pasti salah. Sekarang keduanya memegang beberapa,
 * dan yang di wilayah sungai berperan sebagai batas pilihan, bukan pengganti.
 */
return new class extends Migration
{
    /**
     * Sebelas kabupaten dan dua kota, sesuai pembagian resmi Kalimantan
     * Selatan. Kolom kode selebar sepuluh huruf, sehingga dua kota memakai
     * singkatan yang sudah lazim dipakai orang di sini.
     */
    private const KALSEL = [
        ['Tanah Laut', 'TALA'],
        ['Kotabaru', 'KOTABARU'],
        ['Banjar', 'BANJAR'],
        ['Barito Kuala', 'BATOLA'],
        ['Tapin', 'TAPIN'],
        ['Hulu Sungai Selatan', 'HSS'],
        ['Hulu Sungai Tengah', 'HST'],
        ['Hulu Sungai Utara', 'HSU'],
        ['Tabalong', 'TABALONG'],
        ['Tanah Bumbu', 'TANBU'],
        ['Balangan', 'BALANGAN'],
        ['Kota Banjarmasin', 'BJM'],
        ['Kota Banjarbaru', 'BJB'],
    ];

    public function up(): void
    {
        $sudahAda = DB::table('kabupaten')->pluck('kode')->all();

        $baru = collect(self::KALSEL)
            ->reject(fn ($k) => in_array($k[1], $sudahAda, true))
            ->map(fn ($k) => [
                'nama' => $k[0],
                'kode' => $k[1],
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($baru !== []) {
            DB::table('kabupaten')->insert($baru);
        }

        Schema::create('wilayah_sungai_kabupaten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wilayah_sungai_id')->constrained('wilayah_sungai')->cascadeOnDelete();
            $table->foreignId('kabupaten_id')->constrained('kabupaten')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['wilayah_sungai_id', 'kabupaten_id']);
        });

        // Diisi dari bawah ke atas: kabupaten yang sudah tercatat pada DAS
        // dinaikkan menjadi milik wilayah sungainya. Tanpa ini, DAS yang
        // kabupatennya sudah benar mendadak kehilangan pilihannya di formulir
        // — nilainya masih tersimpan, tetapi tidak lagi ditawarkan.
        $pasangan = DB::table('das_kabupaten')
            ->join('das', 'das_kabupaten.das_id', '=', 'das.id')
            ->select('das.wilayah_sungai_id', 'das_kabupaten.kabupaten_id')
            ->distinct()
            ->get()
            ->map(fn ($b) => [
                'wilayah_sungai_id' => $b->wilayah_sungai_id,
                'kabupaten_id' => $b->kabupaten_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($pasangan !== []) {
            DB::table('wilayah_sungai_kabupaten')->insert($pasangan);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah_sungai_kabupaten');

        // Kabupaten yang ditambahkan sengaja tidak dihapus. Sebagiannya bisa
        // saja sudah dipakai DAS, dan menghapusnya akan menyeret data itu
        // ikut hilang lewat cascade — kerugian yang jauh lebih besar daripada
        // tiga belas baris rujukan yang tertinggal.
    }
};
