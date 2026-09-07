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
