<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

/**
 * Menyalin seluruh isi sistem ke satu berkas zip.
 *
 * Sampai perintah ini ada, aplikasi ini tidak punya cadangan sama sekali.
 * Satu disk mati berarti laporan lapangan berbulan-bulan hilang — pekerjaan
 * yang tidak bisa diulang, sebab bangunannya sudah diperiksa dan keadaannya
 * sudah berubah sejak itu.
 *
 * Dump-nya ditulis dengan PHP, bukan memanggil mysqldump. Alasannya
 * ketahanan: server dinas belum tentu punya berkas itu di PATH, dan hosting
 * berbagi kerap mematikan exec() sama sekali. Basis data ini berisi ribuan
 * baris, bukan jutaan, jadi menuliskannya sendiri sepenuhnya sanggup — dan
 * yang tidak sanggup adalah menemukan cadangan gagal justru pada hari
 * dibutuhkan.
 *
 * PERINGATAN YANG HARUS DIBACA: cadangan yang tersimpan di server yang sama
 * tidak melindungi dari kerusakan disk. Ia melindungi dari penghapusan tak
 * sengaja dan kekeliruan yang bisa ditarik balik, dan itu saja. Berkasnya
 * HARUS disalin keluar — ke Google Drive, ke hard disk kantor, ke mana pun
 * asal bukan mesin yang sama.
 */
class CadangkanData extends Command
{
    protected $signature = 'riverop:cadangkan
                            {--simpan=14 : Berapa cadangan terakhir yang disimpan}
                            {--tanpa-foto : Hanya basis data, tanpa foto laporan}';

    protected $description = 'Menyalin basis data dan foto laporan ke satu berkas zip';

    public function handle(): int
    {
        $mulai = microtime(true);
        $direktori = storage_path('app/cadangan');

        if (! is_dir($direktori) && ! mkdir($direktori, 0755, true)) {
            $this->error("Tidak dapat membuat direktori {$direktori}.");

            return self::FAILURE;
        }

        $nama = 'riverop-' . now()->format('Y-m-d-Hi') . '.zip';
        $jalur = $direktori . '/' . $nama;

        $this->info('Mencadangkan basis data…');
        $sql = $this->dumpBasisData();

        $zip = new ZipArchive();
        if ($zip->open($jalur, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Tidak dapat menulis {$jalur}.");

            return self::FAILURE;
        }

        $zip->addFromString('basis-data.sql', $sql);
        $zip->addFromString('KETERANGAN.txt', $this->keterangan());

        $jumlahFoto = 0;

        if (! $this->option('tanpa-foto')) {
            $this->info('Menyalin foto laporan…');
            $jumlahFoto = $this->masukkanFoto($zip);
        }

        $zip->close();

        $ukuran = filesize($jalur);
        $this->newLine();
        $this->line("  Berkas  : {$jalur}");
        $this->line('  Ukuran  : ' . $this->ukuranTerbaca($ukuran));
        $this->line("  Foto    : {$jumlahFoto} berkas");
        $this->line('  Lama    : ' . round(microtime(true) - $mulai, 1) . ' detik');

        $dibuang = $this->buangYangLama($direktori, (int) $this->option('simpan'));
        if ($dibuang > 0) {
            $this->line("  Dibuang : {$dibuang} cadangan lama");
        }

        $this->newLine();
        $this->warn('  Cadangan ini masih di server yang sama. Salin keluar secara berkala,');
        $this->warn('  sebab cadangan yang ikut mati bersama servernya tidak menyelamatkan apa pun.');

        return self::SUCCESS;
    }

    /**
     * Menulis seluruh tabel sebagai pernyataan SQL.
     *
     * Dibaca bertahap seribu baris sekali jalan supaya memori tetap datar
     * berapa pun besarnya tabel. Nilai dikutip lewat PDO, bukan dirangkai
     * sendiri, sebab catatan juru berisi tanda petik dan baris baru — dan
     * dump yang tidak bisa dimuat kembali sama saja dengan tidak ada.
     */
    private function dumpBasisData(): string
    {
        $pdo = DB::connection()->getPdo();
        $nama = DB::connection()->getDatabaseName();

        $baris = [
            '-- Cadangan RiverOP',
            '-- Basis data : ' . $nama,
            '-- Dibuat     : ' . now()->format('d/m/Y H:i:s'),
            '',
            'SET FOREIGN_KEY_CHECKS=0;',
            'SET NAMES utf8mb4;',
            '',
        ];

        $tabel = array_map(
            fn ($t) => array_values((array) $t)[0],
            DB::select('SHOW TABLES')
        );

        $bar = $this->output->createProgressBar(count($tabel));
        $bar->start();

        foreach ($tabel as $t) {
            $buat = (array) DB::selectOne('SHOW CREATE TABLE `' . $t . '`');
            $baris[] = "DROP TABLE IF EXISTS `{$t}`;";
            $baris[] = array_values($buat)[1] . ';';
            $baris[] = '';

            $jumlah = DB::table($t)->count();

            for ($lewati = 0; $lewati < $jumlah; $lewati += 1000) {
                $isi = DB::table($t)->offset($lewati)->limit(1000)->get();

                foreach ($isi as $satu) {
                    $kolom = array_keys((array) $satu);
                    $nilai = array_map(function ($v) use ($pdo) {
                        return $v === null ? 'NULL' : $pdo->quote((string) $v);
                    }, array_values((array) $satu));

                    $baris[] = 'INSERT INTO `' . $t . '` (`' . implode('`, `', $kolom) . '`) VALUES ('
                        . implode(', ', $nilai) . ');';
                }
            }

            $baris[] = '';
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $baris[] = 'SET FOREIGN_KEY_CHECKS=1;';

        return implode("\n", $baris);
    }

    private function masukkanFoto(ZipArchive $zip): int
    {
        $akar = storage_path('app/public');

        if (! is_dir($akar)) {
            return 0;
        }

        $jumlah = 0;
        $daftar = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($akar, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($daftar as $berkas) {
            if ($berkas->isFile()) {
                $zip->addFile(
                    $berkas->getPathname(),
                    'foto/' . str_replace('\\', '/', substr($berkas->getPathname(), strlen($akar) + 1))
                );
                $jumlah++;
            }
        }

        return $jumlah;
    }

    /**
     * Membuang cadangan terlama.
     *
     * Tanpa ini penyimpanan server penuh perlahan-lahan sampai aplikasinya
     * berhenti menerima laporan — kegagalan yang sebabnya paling tidak
     * terduga bagi orang yang menemukannya.
     */
    private function buangYangLama(string $direktori, int $simpan): int
    {
        if ($simpan < 1) {
            return 0;
        }

        $berkas = glob($direktori . '/riverop-*.zip') ?: [];
        rsort($berkas);

        $dibuang = 0;
        foreach (array_slice($berkas, $simpan) as $lama) {
            if (@unlink($lama)) {
                $dibuang++;
            }
        }

        return $dibuang;
    }

    private function keterangan(): string
    {
        return implode("\n", [
            'CADANGAN SISTEM INFORMASI OP SUNGAI',
            'Dinas PUPR Provinsi Kalimantan Selatan',
            '',
            'Dibuat: ' . now()->format('d F Y, H:i'),
            '',
            'ISI',
            '  basis-data.sql   Seluruh data: laporan, juru, DAS, wilayah sungai, pengguna.',
            '  foto/            Foto bangunan dan temuan yang dikirim juru dari lapangan.',
            '',
            'CARA MENGEMBALIKAN',
            '  1. Siapkan basis data MySQL kosong.',
            '  2. Muat basis-data.sql ke dalamnya.',
            '  3. Salin seluruh isi foto/ ke storage/app/public/ pada server.',
            '  4. Jalankan: php artisan storage:link',
            '',
            'PENTING',
            '  Berkas ini harus disalin ke tempat lain — Google Drive, hard disk kantor,',
            '  atau apa pun yang bukan server yang sama. Cadangan yang tersimpan di',
            '  mesin yang sama akan ikut hilang bila mesin itu rusak.',
        ]);
    }

    private function ukuranTerbaca(int $bita): string
    {
        foreach (['B', 'KB', 'MB', 'GB'] as $satuan) {
            if ($bita < 1024) {
                return round($bita, 1) . ' ' . $satuan;
            }
            $bita /= 1024;
        }

        return round($bita, 1) . ' TB';
    }
}
