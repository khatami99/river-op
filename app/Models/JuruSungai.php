<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class JuruSungai extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'juru_sungai';

    protected $fillable = [
        'wilayah_sungai_id',
        'nama',
        'nip',
        'username',
        'password',
        'aktif',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'aktif' => 'boolean',
    ];

    public function wilayahSungai(): BelongsTo
    {
        return $this->belongsTo(WilayahSungai::class, 'wilayah_sungai_id');
    }

    public function laporanOp(): HasMany
    {
        return $this->hasMany(LaporanOp::class, 'juru_sungai_id');
    }
}
