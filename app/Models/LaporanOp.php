<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanOp extends Model
{
    use SoftDeletes;

    protected $table = 'laporan_op';

    protected $fillable = [
        'uuid',
        'juru_sungai_id',
        'das_id',
        'tanggal_inspeksi',
        'bulan',
        'tahun',
        'cuaca',
        'debit_air',
        'catatan_umum',
        'kendala',
        'foto_lokasi_1',
        'foto_lokasi_2',
        'sync_status',
        'dibuat_di_hp',
        'synced_at',
    ];

    protected $casts = [
        'tanggal_inspeksi' => 'date',
        'bulan' => 'integer',
        'tahun' => 'integer',
        'dibuat_di_hp' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function juruSungai(): BelongsTo
    {
        return $this->belongsTo(JuruSungai::class, 'juru_sungai_id');
    }

    public function das(): BelongsTo
    {
        return $this->belongsTo(Das::class, 'das_id');
    }

    public function itemInspeksi(): HasMany
    {
        return $this->hasMany(ItemInspeksi::class, 'laporan_op_id');
    }
}
