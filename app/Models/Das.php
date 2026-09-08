<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Daerah Aliran Sungai — satuan wilayah kerja yang dipegang seorang juru sungai.
 * Di Kalimantan Selatan pembagian tugas juru dilakukan per DAS, bukan per
 * wilayah sungai, karena wewenang provinsi hanya mencakup satu wilayah sungai.
 */
class Das extends Model
{
    use SoftDeletes;

    protected $table = 'das';

    protected $fillable = [
        'wilayah_sungai_id',
        'nama_das',
        'kode_das',
        'deskripsi',
        'koordinat_lat',
        'koordinat_lng',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'koordinat_lat' => 'decimal:7',
        'koordinat_lng' => 'decimal:7',
    ];

    /**
     * Nama tanpa awalan "DAS", khusus untuk tulisan di dalam daftar pilihan.
     *
     * Peramban punya ketik-cepat bawaan: saat daftar pilihan terbuka, menekan
     * huruf melompat ke pilihan yang diawali huruf itu. Kemampuan itu mati
     * total di sini, sebab keenam puluh dua nama DAS diawali kata yang sama —
     * hurufnya cuma satu macam, "D". Menekan "K" untuk mencari Kintap tidak
     * melakukan apa-apa.
     *
     * Tanpa awalannya, huruf pertamanya jadi dua belas macam dan mengetik
     * "kin" langsung melompat ke Kintap. Awalannya tidak hilang dari data,
     * hanya dari tulisan pilihan — dan di situ ia memang mubazir, sebab
     * labelnya sudah berkata DAS.
     */
    public function namaRingkas(): string
    {
        return preg_replace('/^DAS\s+/iu', '', $this->nama_das ?? '');
    }

    public function punyaKoordinat(): bool
    {
        return $this->koordinat_lat !== null && $this->koordinat_lng !== null;
    }

    /**
     * Batas DAS mengikuti punggung bukit, sedangkan batas kabupaten digambar
     * manusia, jadi satu DAS bisa melintasi lebih dari satu kabupaten.
     * Nilainya label saja — jangan dipakai menjumlahkan laporan per kabupaten,
     * karena DAS lintas kabupaten akan terhitung berulang.
     */
    public function kabupaten(): BelongsToMany
    {
        return $this->belongsToMany(Kabupaten::class, 'das_kabupaten', 'das_id', 'kabupaten_id')
            ->withTimestamps()
            ->orderBy('nama');
    }

    /** Daftar nama kabupaten siap tampil, atau tanda hubung bila belum diisi. */
    public function labelKabupaten(): string
    {
        $nama = $this->kabupaten->pluck('nama');

        return $nama->isEmpty() ? '-' : $nama->join(', ');
    }

    public function wilayahSungai(): BelongsTo
    {
        return $this->belongsTo(WilayahSungai::class, 'wilayah_sungai_id');
    }

    public function juruSungai(): HasMany
    {
        return $this->hasMany(JuruSungai::class, 'das_id');
    }

    public function laporanOp(): HasMany
    {
        return $this->hasMany(LaporanOp::class, 'das_id');
    }
}
