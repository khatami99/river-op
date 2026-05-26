<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemInspeksi extends Model
{
    protected $table = 'item_inspeksi';

    protected $fillable = [
        'uuid',
        'laporan_op_id',
        'jenis_bangunan_id',
        'nama_bangunan',
        'lokasi_km_dari',
        'lokasi_km_sampai',
        'koordinat_lat',
        'koordinat_lng',
        'tipe_konstruksi',
        'catatan',
        'foto_1',
        'foto_2',
        'foto_3',
    ];

    protected $casts = [
        'lokasi_km_dari' => 'decimal:3',
        'lokasi_km_sampai' => 'decimal:3',
        'koordinat_lat' => 'decimal:7',
        'koordinat_lng' => 'decimal:7',
    ];

    public function laporanOp(): BelongsTo
    {
        return $this->belongsTo(LaporanOp::class, 'laporan_op_id');
    }

    public function jenisBangunan(): BelongsTo
    {
        return $this->belongsTo(JenisBangunan::class, 'jenis_bangunan_id');
    }

    public function temuanKondisi(): HasMany
    {
        return $this->hasMany(TemuanKondisi::class, 'item_inspeksi_id');
    }
}
