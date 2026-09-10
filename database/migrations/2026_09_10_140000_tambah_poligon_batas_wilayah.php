<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Batas wilayah DAS dan wilayah sungai, disimpan sebagai GeoJSON.
 *
 * Selama ini DAS diwakili satu titik perkiraan, dan itu memang ditulis apa
 * adanya di layar: "DAS sebenarnya berupa wilayah, bukan titik". Titik itu
 * dipakai karena batas sungguhannya belum ada, bukan karena dianggap cukup.
 *
 * Kolomnya teks, bukan tipe spasial bawaan MySQL. Alasannya bukan malas:
 * yang dibutuhkan sekarang hanya menggambar batas di peta, dan Leaflet
 * memakan GeoJSON apa adanya. Tipe spasial baru berguna bila suatu saat ada
 * pertanyaan yang harus dijawab basis data sendiri — misalnya "bangunan di
 * titik ini masuk DAS mana" — dan menambahkannya belakangan jauh lebih mudah
 * daripada menebak-nebak kebutuhannya sekarang.
 *
 * Waktu perbaruan ikut disimpan supaya jelas kapan batas itu diterima dari
 * BWS, sebab batas wilayah sesekali direvisi dan yang tersimpan di sini bisa
 * jadi bukan yang terbaru.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['das', 'wilayah_sungai'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->longText('poligon')->nullable()->after('deskripsi');
                $table->timestamp('poligon_diperbarui_at')->nullable()->after('poligon');
            });
        }
    }

    public function down(): void
    {
        foreach (['das', 'wilayah_sungai'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->dropColumn(['poligon', 'poligon_diperbarui_at']);
            });
        }
    }
};
