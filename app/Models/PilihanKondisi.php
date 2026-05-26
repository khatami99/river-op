<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PilihanKondisi extends Model
{
    protected $table = 'pilihan_kondisi';

    public $timestamps = false;

    protected $fillable = [
        'bagian_bangunan_id',
        'kondisi',
        'urutan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    public function bagianBangunan(): BelongsTo
    {
        return $this->belongsTo(BagianBangunan::class, 'bagian_bangunan_id');
    }
}
