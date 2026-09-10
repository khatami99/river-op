<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kabupaten extends Model
{
    use SoftDeletes;

    protected $table = 'kabupaten';

    protected $fillable = [
        'nama',
        'kode',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    /**
     * Kabupaten dengan nama ini, dibuatkan bila belum ada.
     *
     * Daftar kabupaten tidak boleh terkunci di dalam kode. Pemekaran wilayah
     * itu nyata dan tidak jarang: Tanah Bumbu dan Balangan sendiri baru lahir
     * pada 2003, dan keduanya sekarang ada di daftar bawaan aplikasi ini.
     * Admin mengetik namanya, dan sistem menyediakan tempatnya.
     *
     * Pencocokannya tidak peka huruf besar-kecil dan merapikan spasi
     * berlebih, supaya "kotabaru" dan "Kotabaru " tidak melahirkan kabupaten
     * kembar yang di layar kelihatan sama persis. Yang sudah dihapus lunak
     * dihidupkan lagi, bukan digandakan.
     */
    public static function dariNama(string $nama): ?self
    {
        $nama = static::rapikanNama($nama);

        if ($nama === '') {
            return null;
        }

        $ada = static::withTrashed()
            ->get()
            ->first(fn ($k) => mb_strtolower($k->nama) === mb_strtolower($nama));

        if ($ada) {
            if ($ada->trashed()) {
                $ada->restore();
            }

            return $ada;
        }

        return static::create([
            'nama' => $nama,
            'kode' => static::kodeUntuk($nama),
            'aktif' => true,
        ]);
    }

    public static function rapikanNama(string $nama): string
    {
        return trim(preg_replace('/\s+/u', ' ', $nama));
    }

    /**
     * Kode dibangkitkan sendiri, tidak diminta kepada admin.
     *
     * Kolom ini tidak pernah tampil di layar mana pun dan tidak dipakai
     * mencari apa pun — ia cuma warisan skema yang berstatus wajib dan unik.
     * Meminta admin memikirkannya berarti menambah pertanyaan yang jawabannya
     * tidak ada gunanya bagi siapa pun.
     */
    private static function kodeUntuk(string $nama): string
    {
        $dasar = mb_substr(mb_strtoupper(preg_replace('/[^A-Za-z]/', '', $nama)), 0, 10) ?: 'KAB';
        $kode = $dasar;
        $urutan = 2;

        while (static::withTrashed()->where('kode', $kode)->exists()) {
            $ekor = (string) $urutan++;
            $kode = mb_substr($dasar, 0, 10 - mb_strlen($ekor)) . $ekor;
        }

        return $kode;
    }

    public function das(): BelongsToMany
    {
        return $this->belongsToMany(Das::class, 'das_kabupaten', 'kabupaten_id', 'das_id')
            ->withTimestamps();
    }
}
