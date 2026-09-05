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
        'foto_2',
        'foto_3',
    ];

    protected $casts = [
        'kondisi_ditemukan' => 'array',
        'koordinat_lat' => 'decimal:7',
        'koordinat_lng' => 'decimal:7',
    ];

    /**
     * Android mengirim array, tapi data lama bisa saja tersimpan sebagai
     * string CSV. Selalu kembalikan array supaya view tidak perlu menebak.
     */
    public function kondisiList(): array
    {
        $nilai = $this->kondisi_ditemukan;

        if (is_array($nilai)) {
            return array_values(array_filter(array_map('trim', $nilai), fn ($v) => $v !== ''));
        }

        if (is_string($nilai) && $nilai !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $nilai)), fn ($v) => $v !== ''));
        }

        return [];
    }

    /**
     * Semua foto temuan yang terisi, siap dipakai galeri.
     */
    public function semuaFoto(): array
    {
        return array_values(array_filter([$this->foto, $this->foto_2, $this->foto_3]));
    }

    public function adaMasalah(): bool
    {
        foreach ($this->kondisiList() as $kondisi) {
            if (strcasecmp($kondisi, 'Normal') !== 0) {
                return true;
            }
        }

        return false;
    }

    public function itemInspeksi(): BelongsTo
    {
        return $this->belongsTo(ItemInspeksi::class, 'item_inspeksi_id');
    }

    public function bagianBangunan(): BelongsTo
    {
        return $this->belongsTo(BagianBangunan::class, 'bagian_bangunan_id');
    }
}
