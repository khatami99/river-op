<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemuanKondisi extends Model
{
    protected $table = 'temuan_kondisi';

    protected $fillable = [
        'item_inspeksi_id',
        'bagian_bangunan_id',
        'kondisi_ditemukan',
        'tindakan_diperlukan',
        'koordinat_lat',
        'koordinat_lng',
        'keterangan',
        'foto',
    ];

    protected $casts = [
        'kondisi_ditemukan' => 'array',
        'koordinat_lat' => 'decimal:7',
        'koordinat_lng' => 'decimal:7',
    ];

    public function itemInspeksi(): BelongsTo
    {
        return $this->belongsTo(ItemInspeksi::class, 'item_inspeksi_id');
    }

    public function bagianBangunan(): BelongsTo
    {
        return $this->belongsTo(BagianBangunan::class, 'bagian_bangunan_id');
    }
}
