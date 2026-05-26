<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BagianBangunan extends Model
{
    protected $table = 'bagian_bangunan';

    public $timestamps = false;

    protected $fillable = [
        'jenis_bangunan_id',
        'nama',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    public function jenisBangunan(): BelongsTo
    {
        return $this->belongsTo(JenisBangunan::class, 'jenis_bangunan_id');
    }

    public function pilihanKondisi(): HasMany
    {
        return $this->hasMany(PilihanKondisi::class, 'bagian_bangunan_id');
    }

    public function temuanKondisi(): HasMany
    {
        return $this->hasMany(TemuanKondisi::class, 'bagian_bangunan_id');
    }
}
