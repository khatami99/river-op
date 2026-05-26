<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisBangunan extends Model
{
    protected $table = 'jenis_bangunan';

    protected $fillable = [
        'kode',
        'nama',
        'tipe_konstruksi',
        'aktif',
        'urutan',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    public function bagianBangunan(): HasMany
    {
        return $this->hasMany(BagianBangunan::class, 'jenis_bangunan_id');
    }

    public function itemInspeksi(): HasMany
    {
        return $this->hasMany(ItemInspeksi::class, 'jenis_bangunan_id');
    }
}
