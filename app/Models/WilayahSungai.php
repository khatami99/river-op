<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class WilayahSungai extends Model
{
    use SoftDeletes;

    protected $table = 'wilayah_sungai';

    protected $fillable = [
        'nama_sungai',
        'kode_sungai',
        'deskripsi',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'poligon_diperbarui_at' => 'datetime',
    ];

    /** Lihat alasannya pada Das::getLabelAttribute(). */
    public function getLabelAttribute(): string
    {
        return $this->nama_sungai ?? '';
    }

    public function das(): HasMany
    {
        return $this->hasMany(Das::class, 'wilayah_sungai_id');
    }

    /**
     * Kabupaten yang disinggung wilayah sungai ini.
     *
     * Perannya membatasi, bukan menggantikan: inilah kabupaten yang boleh
     * dipilih oleh DAS di bawahnya. Wilayah sungai adalah gabungan DAS-nya,
     * jadi sebuah DAS mustahil berada di kabupaten yang tidak disinggung
     * wilayah sungainya.
     */
    public function kabupaten(): BelongsToMany
    {
        return $this->belongsToMany(Kabupaten::class, 'wilayah_sungai_kabupaten')
            ->withTimestamps()
            ->orderBy('nama');
    }

    /** Juru sungai kini bernaung di bawah DAS, bukan langsung di wilayah sungai. */
    public function juruSungai(): HasManyThrough
    {
        return $this->hasManyThrough(JuruSungai::class, Das::class, 'wilayah_sungai_id', 'das_id');
    }

    public function laporanOp(): HasManyThrough
    {
        return $this->hasManyThrough(LaporanOp::class, Das::class, 'wilayah_sungai_id', 'das_id');
    }
}
