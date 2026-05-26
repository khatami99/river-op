<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WilayahSungai extends Model
{
    use SoftDeletes;

    protected $table = 'wilayah_sungai';

    protected $fillable = [
        'kabupaten_id',
        'nama_sungai',
        'kode_sungai',
        'deskripsi',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id');
    }

    public function juruSungai(): HasMany
    {
        return $this->hasMany(JuruSungai::class, 'wilayah_sungai_id');
    }

    public function laporanOp(): HasMany
    {
        return $this->hasMany(LaporanOp::class, 'wilayah_sungai_id');
    }
}
