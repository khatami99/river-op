<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
