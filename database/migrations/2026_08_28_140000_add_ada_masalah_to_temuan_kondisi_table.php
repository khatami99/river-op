<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menandai temuan yang kondisinya bukan Normal.
 *
 * Sebelumnya penilaian ini harus dilakukan di PHP dengan membaca kolom JSON
 * kondisi_ditemukan baris per baris, sehingga rekapitulasi tidak dapat
 * diagregasi lewat SQL. Penandanya kini disimpan saat laporan disinkronkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('temuan_kondisi', function (Blueprint $table) {
            $table->boolean('ada_masalah')->default(false)->after('kondisi_ditemukan')->index();
        });

        // Isi ulang penanda untuk data yang sudah ada.
        DB::table('temuan_kondisi')
            ->select('id', 'kondisi_ditemukan')
            ->orderBy('id')
            ->chunk(500, function ($baris) {
                foreach ($baris as $item) {
                    if ($this->adaMasalah($item->kondisi_ditemukan)) {
                        DB::table('temuan_kondisi')->where('id', $item->id)->update(['ada_masalah' => true]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('temuan_kondisi', function (Blueprint $table) {
            $table->dropColumn('ada_masalah');
        });
    }

    /**
     * Data lama bisa tersimpan sebagai array JSON maupun string CSV,
     * jadi keduanya ditangani.
     */
    private function adaMasalah(?string $mentah): bool
    {
        if (blank($mentah)) {
            return false;
        }

        $nilai = json_decode($mentah, true);

        if (! is_array($nilai)) {
            $nilai = explode(',', is_string($nilai) ? $nilai : $mentah);
        }

        foreach ($nilai as $kondisi) {
            $kondisi = trim((string) $kondisi);
            if ($kondisi !== '' && strcasecmp($kondisi, 'Normal') !== 0) {
                return true;
            }
        }

        return false;
    }
};
