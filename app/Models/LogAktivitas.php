<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    protected $fillable = [
        'staf_dinas_id',
        'nama_pelaku',
        'aksi',
        'jenis_data',
        'data_id',
        'ringkasan',
        'perubahan',
        'ip',
    ];

    protected $casts = [
        'perubahan' => 'array',
    ];

    public function staf(): BelongsTo
    {
        return $this->belongsTo(StafDinas::class, 'staf_dinas_id');
    }

    public function labelAksi(): string
    {
        return match ($this->aksi) {
            'dibuat' => 'Ditambahkan',
            'diubah' => 'Diubah',
            'dihapus' => 'Dihapus',
            default => ucfirst($this->aksi),
        };
    }
}
