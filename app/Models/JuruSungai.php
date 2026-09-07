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
        'das_id',
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

    /** Laporan status terakhir dari HP juru ini; null bila belum pernah melapor. */
    public function statusSinkronisasi(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StatusSinkronisasi::class, 'juru_sungai_id');
    }

    public function das(): BelongsTo
    {
        return $this->belongsTo(Das::class, 'das_id');
    }

    public function laporanOp(): HasMany
    {
        return $this->hasMany(LaporanOp::class, 'juru_sungai_id');
    }
}
