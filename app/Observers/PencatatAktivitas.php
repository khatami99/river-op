<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use Illuminate\Database\Eloquent\Model;

/**
 * Mencatat perubahan data master ke log aktivitas.
 *
 * Ditulis sebagai satu observer untuk semua model yang diawasi, bukan satu
 * observer per model, karena yang dicatat selalu bentuk yang sama: siapa,
 * apa, dan nilai sebelum-sesudah.
 *
 * Pencatatan hanya berjalan bila ada staf yang sedang masuk. Perubahan dari
 * seeder atau perintah artisan sengaja tidak dicatat — tidak ada orang yang
 * bertanggung jawab di sana, dan mencatatnya hanya membuat log penuh oleh
 * baris yang tidak berarti.
 */
class PencatatAktivitas
{
    /** Kolom yang tidak boleh masuk log, apa pun modelnya. */
    private const RAHASIA = ['password', 'remember_token'];

    public function created(Model $model): void
    {
        $this->catat($model, 'dibuat', $this->bersihkan($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $berubah = $this->bersihkan($model->getChanges());
        unset($berubah['updated_at']);

        if ($berubah === []) {
            return;
        }

        $sebelum = [];
        foreach (array_keys($berubah) as $kolom) {
            $sebelum[$kolom] = $model->getOriginal($kolom);
        }

        $this->catat($model, 'diubah', [
            'sebelum' => $this->bersihkan($sebelum),
            'sesudah' => $berubah,
        ]);
    }

    public function deleted(Model $model): void
    {
        $this->catat($model, 'dihapus', null);
    }

    private function catat(Model $model, string $aksi, ?array $perubahan): void
    {
        $staf = auth('staf')->user();

        if (! $staf) {
            return;
        }

        LogAktivitas::create([
            'staf_dinas_id' => $staf->id,
            'nama_pelaku' => $staf->nama,
            'aksi' => $aksi,
            'jenis_data' => class_basename($model),
            'data_id' => $model->getKey(),
            'ringkasan' => $this->ringkas($model),
            'perubahan' => $perubahan,
            'ip' => request()->ip(),
        ]);
    }

    /** Nama yang dikenali orang, bukan sekadar nomor baris. */
    private function ringkas(Model $model): string
    {
        foreach (['nama_das', 'nama_sungai', 'nama', 'username'] as $kolom) {
            $nilai = $model->getAttribute($kolom);

            if (is_string($nilai) && $nilai !== '') {
                return mb_substr($nilai, 0, 255);
            }
        }

        return class_basename($model) . ' #' . $model->getKey();
    }

    private function bersihkan(array $data): array
    {
        return array_diff_key($data, array_flip(self::RAHASIA));
    }
}
