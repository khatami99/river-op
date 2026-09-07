<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    ];

    public function das(): HasMany
    {
        return $this->hasMany(Das::class, 'wilayah_sungai_id');
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
