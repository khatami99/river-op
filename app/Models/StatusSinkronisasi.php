<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusSinkronisasi extends Model
{
    protected $table = 'status_sinkronisasi';

    protected $fillable = [
        'juru_sungai_id',
        'jumlah_tertunda',
        'jumlah_gagal',
        'jumlah_terkirim',
        'versi_aplikasi',
        'pesan_terakhir',
        'dilaporkan_pada',
    ];

    protected $casts = [
        'dilaporkan_pada' => 'datetime',
    ];

    public function juruSungai(): BelongsTo
    {
        return $this->belongsTo(JuruSungai::class, 'juru_sungai_id');
    }

    public function bermasalah(): bool
    {
        return $this->jumlah_gagal > 0;
    }

    /** Laporan yang sudah lama tidak diperbarui patut dicurigai. */
    public function basi(): bool
    {
        return $this->dilaporkan_pada->lt(now()->subDays(3));
    }
}
