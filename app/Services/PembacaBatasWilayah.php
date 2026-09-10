<?php

namespace App\Services;

use RuntimeException;
use Shapefile\Geometry\Geometry;
use Shapefile\Shapefile;
use Shapefile\ShapefileReader;
use ZipArchive;

/**
 * Membaca berkas batas wilayah dari BWS menjadi daftar poligon siap pakai.
 *
 * Kelas ini ditulis dengan satu anggapan: yang memakainya nanti bukan
 * programmer, dan tidak ada programmer yang bisa dimintai tolong. Karena itu
 * hampir separuh isinya pemeriksaan, dan tiap penolakan menyebutkan apa yang
 * salah sekaligus apa yang harus dikerjakan untuk membetulkannya.
 *
 * Yang SENGAJA tidak dikerjakan di sini adalah reproyeksi. Berkas dari
 * instansi kerap memakai koordinat UTM — angka ratusan ribu meter, bukan
 * derajat — dan mengubahnya butuh pustaka proyeksi yang tidak ada di PHP.
 * Menerimanya begitu saja jauh lebih buruk daripada menolak: petanya akan
 * tergambar entah di mana tanpa ada yang menyadari batasnya salah. Jadi
 * berkas semacam itu ditolak, dan pesannya menyebutkan langkah persis di
 * QGIS untuk mengubahnya.
 */
class PembacaBatasWilayah
{
    /**
     * Kotak Kalimantan Selatan, dilebihkan sedikit ke segala arah.
     *
     * Gunanya menangkap dua kekeliruan sekaligus: berkas provinsi lain yang
     * tertukar, dan berkas yang proyeksinya salah sehingga jatuh di lepas
     * pantai Afrika. Keduanya jauh lebih mudah dibetulkan sekarang daripada
     * ditemukan berbulan-bulan kemudian.
     */
    private const BATAS_KALSEL = ['lng' => [113.5, 117.5], 'lat' => [-5.5, -0.5]];

    /**
     * Anggaran titik per poligon.
     *
     * Bukan angka keramat, melainkan tawar-menawar: batas DAS dari BWS bisa
     * berisi puluhan ribu titik — teliti sampai sentimeter, dan sama sekali
     * tidak berguna pada peta selebar layar yang satu pikselnya saja mewakili
     * puluhan meter. Enam puluh dua poligon sebesar itu akan membuat halaman
     * DAS memuat berpuluh megabita sebelum apa pun tergambar.
     */
    private const TITIK_MAKSIMAL = 800;

    /**
     * @return array<int, array{atribut: array<string, string>, geometri: array, jumlah_titik: int}>
     */
    public function baca(string $jalurBerkas, string $namaAsli): array
    {
        $ekstensi = strtolower(pathinfo($namaAsli, PATHINFO_EXTENSION));

        $fitur = match ($ekstensi) {
            'zip' => $this->dariShapefile($jalurBerkas),
            'geojson', 'json' => $this->dariGeoJson(file_get_contents($jalurBerkas)),
            default => throw new RuntimeException(
                'Berkas harus berupa .zip (shapefile dari BWS) atau .geojson.'
            ),
        };

        if ($fitur === []) {
            throw new RuntimeException('Berkas terbaca, tetapi tidak berisi satu pun batas wilayah.');
        }

        $this->pastikanDiKalsel($fitur);

        return $fitur;
    }

    // ---------------------------------------------------------------
    // Shapefile
    // ---------------------------------------------------------------

    private function dariShapefile(string $jalurZip): array
    {
        $petiUnggah = storage_path('app/batas-sementara/' . uniqid('shp_', true));

        $zip = new ZipArchive();
        if ($zip->open($jalurZip) !== true) {
            throw new RuntimeException('Berkas ZIP tidak dapat dibuka. Mungkin rusak saat diunduh.');
        }

        // Diekstrak datar: pengarsip kerap menaruh berkasnya di dalam folder,
        // dan pembaca shapefile perlu .shp, .dbf, dan .shx bersebelahan.
        @mkdir($petiUnggah, 0755, true);
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nama = $zip->getNameIndex($i);
            if (str_ends_with($nama, '/')) {
                continue;
            }

            $isi = $zip->getFromIndex($i);
            if ($isi !== false) {
                file_put_contents($petiUnggah . '/' . basename($nama), $isi);
            }
        }
        $zip->close();

        $shp = glob($petiUnggah . '/*.[sS][hH][pP]');

        try {
            if ($shp === false || $shp === []) {
                throw new RuntimeException(
                    'Di dalam ZIP tidak ada berkas .shp. Pastikan seluruh isi shapefile ikut '
                    . 'dikompres: .shp, .dbf, dan .shx sekaligus, bukan .shp saja.'
                );
            }

            $dasar = substr($shp[0], 0, -4);
            foreach (['dbf' => 'nama wilayahnya', 'shx' => 'urutan bentuknya'] as $wajib => $guna) {
                if (! file_exists($dasar . '.' . $wajib) && ! file_exists($dasar . '.' . strtoupper($wajib))) {
                    throw new RuntimeException(
                        "Berkas .{$wajib} tidak ikut di dalam ZIP, padahal di situlah {$guna} tersimpan. "
                        . 'Kompres ulang seluruh berkas shapefile sekaligus.'
                    );
                }
            }

            return $this->kumpulkanDariShapefile($shp[0]);
        } finally {
            $this->hapusIsi($petiUnggah);
        }
    }

    private function kumpulkanDariShapefile(string $jalurShp): array
    {
        try {
            $pembaca = new ShapefileReader($jalurShp, [
                // Poligon dari instansi kerap punya cincin yang tidak tertutup
                // rapi. Ditertibkan di sini, sebab kalau tidak, sebagian bentuk
                // ditolak mentah-mentah dan berkasnya seolah kosong.
                Shapefile::OPTION_ENFORCE_POLYGON_CLOSED_RINGS => true,

                // Ketinggian dan nilai ukur dibuang: peta ini dua dimensi, dan
                // membawanya hanya memperbesar isi yang tersimpan.
                Shapefile::OPTION_SUPPRESS_Z => true,
                Shapefile::OPTION_SUPPRESS_M => true,

                // Nama wilayah di .dbf sering berkode Latin-1.
                Shapefile::OPTION_DBF_CONVERT_TO_UTF8 => true,
            ]);
        } catch (\Throwable $e) {
            throw new RuntimeException('Berkas .shp tidak dapat dibaca: ' . $e->getMessage());
        }

        $hasil = [];

        while ($bentuk = $pembaca->fetchRecord()) {
            if ($bentuk->isDeleted()) {
                continue;
            }

            // getGeoJSON(false) mengembalikan geometrinya saja tanpa bbox —
            // yang dibungkus jadi Feature nanti oleh pemakainya, bukan di sini.
            $geometri = json_decode($bentuk->getGeoJSON(false), true);

            if (! $this->berupaPoligon($geometri)) {
                continue;
            }

            $hasil[] = $this->rapikan($geometri, $this->bersihkanAtribut($bentuk->getDataArray()));
        }

        if ($hasil === []) {
            throw new RuntimeException(
                'Berkas ini tidak berisi poligon. Batas wilayah harus berupa bidang (Polygon), '
                . 'bukan titik atau garis.'
            );
        }

        return $hasil;
    }

    // ---------------------------------------------------------------
    // GeoJSON
    // ---------------------------------------------------------------

    private function dariGeoJson(string $isi): array
    {
        $data = json_decode($isi, true);

        if (! is_array($data)) {
            throw new RuntimeException('Berkas GeoJSON tidak dapat dibaca: isinya bukan JSON yang sah.');
        }

        $fitur = match ($data['type'] ?? null) {
            'FeatureCollection' => $data['features'] ?? [],
            'Feature' => [$data],
            default => throw new RuntimeException(
                'GeoJSON harus berupa FeatureCollection. Bila mengekspor dari QGIS, '
                . 'pilih "GeoJSON" apa adanya tanpa mengubah setelan lain.'
            ),
        };

        $hasil = [];

        foreach ($fitur as $f) {
            $geometri = $f['geometry'] ?? null;

            if (! $this->berupaPoligon($geometri)) {
                continue;
            }

            $hasil[] = $this->rapikan($geometri, $this->bersihkanAtribut($f['properties'] ?? []));
        }

        if ($hasil === []) {
            throw new RuntimeException(
                'Berkas ini tidak berisi poligon. Batas wilayah harus berupa bidang (Polygon), '
                . 'bukan titik atau garis.'
            );
        }

        return $hasil;
    }

    // ---------------------------------------------------------------
    // Pemeriksaan dan perapian
    // ---------------------------------------------------------------

    private function berupaPoligon(?array $geometri): bool
    {
        return in_array($geometri['type'] ?? '', ['Polygon', 'MultiPolygon'], true);
    }

    /** @param array<string, mixed> $atribut */
    private function bersihkanAtribut(array $atribut): array
    {
        $bersih = [];

        foreach ($atribut as $kunci => $nilai) {
            if (is_scalar($nilai) && trim((string) $nilai) !== '') {
                // Berkas dari instansi kerap berkode Latin-1, dan nama seperti
                // "Barito Kuala" bisa membawa bita yang bukan UTF-8 sah.
                $teks = trim((string) $nilai);
                $bersih[(string) $kunci] = mb_check_encoding($teks, 'UTF-8')
                    ? $teks
                    : mb_convert_encoding($teks, 'UTF-8', 'ISO-8859-1');
            }
        }

        return $bersih;
    }

    private function rapikan(array $geometri, array $atribut): array
    {
        $semula = $this->hitungTitik($geometri['coordinates']);

        if ($semula > self::TITIK_MAKSIMAL) {
            $geometri['coordinates'] = $this->pangkasSampaiMuat($geometri['coordinates']);
        }

        return [
            'atribut' => $atribut,
            'geometri' => $geometri,
            'jumlah_titik' => $this->hitungTitik($geometri['coordinates']),
            'titik_asli' => $semula,
        ];
    }

    /**
     * Menaikkan toleransi sedikit demi sedikit sampai jumlah titiknya muat.
     *
     * Toleransi tetap akan salah di kedua arah: terlalu longgar untuk batas
     * yang berkelok-kelok, dan terlalu ketat untuk yang sudah sederhana —
     * lingkaran mulus dengan enam ribu titik sempat tersisa enam puluh lima.
     * Mengejar anggaran titik membuat tiap poligon dipangkas sependek yang
     * memang perlu, tidak lebih.
     */
    private function pangkasSampaiMuat(array $koordinat): array
    {
        $toleransi = 0.00002;   // sekitar dua meter
        $hasil = $koordinat;

        // Dua puluh kali penggandaan sudah mencapai sekitar dua derajat;
        // di luar itu tidak ada bentuk yang tersisa untuk dipangkas.
        for ($i = 0; $i < 20; $i++) {
            $hasil = $this->sederhanakan($koordinat, $toleransi);

            if ($this->hitungTitik($hasil) <= self::TITIK_MAKSIMAL) {
                return $hasil;
            }

            $toleransi *= 2;
        }

        return $hasil;
    }

    /**
     * Menolak berkas yang koordinatnya jelas bukan derajat, atau yang jatuh di
     * luar Kalimantan Selatan.
     */
    private function pastikanDiKalsel(array $fitur): void
    {
        $contoh = null;

        foreach ($fitur as $f) {
            $contoh = $this->titikPertama($f['geometri']['coordinates']);
            if ($contoh !== null) {
                break;
            }
        }

        if ($contoh === null) {
            throw new RuntimeException('Poligon di berkas ini tidak punya koordinat.');
        }

        [$lng, $lat] = $contoh;

        if (abs($lng) > 180 || abs($lat) > 90) {
            throw new RuntimeException(
                'Koordinat berkas ini bukan derajat lintang-bujur, melainkan satuan proyeksi '
                . '(UTM). Buka di QGIS, klik kanan layernya, pilih Export → Save Features As, '
                . 'pilih format GeoJSON, dan pada bagian CRS pilih EPSG:4326 - WGS 84. '
                . 'Unggah berkas hasilnya ke sini.'
            );
        }

        $diLuar = $lng < self::BATAS_KALSEL['lng'][0] || $lng > self::BATAS_KALSEL['lng'][1]
            || $lat < self::BATAS_KALSEL['lat'][0] || $lat > self::BATAS_KALSEL['lat'][1];

        if ($diLuar) {
            throw new RuntimeException(sprintf(
                'Batas pada berkas ini terletak di %.4f, %.4f — jauh di luar Kalimantan Selatan. '
                . 'Kemungkinan berkasnya tertukar dengan wilayah lain, atau proyeksinya belum '
                . 'diubah ke EPSG:4326 - WGS 84.',
                $lat,
                $lng
            ));
        }
    }

    private function titikPertama(array $koordinat): ?array
    {
        if (isset($koordinat[0]) && is_numeric($koordinat[0]) && is_numeric($koordinat[1] ?? null)) {
            return [(float) $koordinat[0], (float) $koordinat[1]];
        }

        foreach ($koordinat as $bagian) {
            if (is_array($bagian)) {
                $titik = $this->titikPertama($bagian);
                if ($titik !== null) {
                    return $titik;
                }
            }
        }

        return null;
    }

    private function hitungTitik(array $koordinat): int
    {
        if (isset($koordinat[0]) && is_numeric($koordinat[0])) {
            return 1;
        }

        return array_sum(array_map(
            fn ($bagian) => is_array($bagian) ? $this->hitungTitik($bagian) : 0,
            $koordinat
        ));
    }

    /**
     * Penyederhanaan Douglas-Peucker, dijalankan pada tiap cincin.
     *
     * Batas DAS dari BWS bisa berisi puluhan ribu titik — teliti sampai
     * sentimeter, dan sama sekali tidak berguna pada peta selebar layar yang
     * satu pikselnya saja mewakili puluhan meter. Yang berguna cuma bentuknya,
     * dan bentuk itu bertahan sesudah dipangkas.
     */
    private function sederhanakan(array $koordinat, float $toleransi): array
    {
        // Sebuah cincin: daftar titik [lng, lat].
        if (isset($koordinat[0][0]) && is_numeric($koordinat[0][0])) {
            return count($koordinat) > 4
                ? $this->pangkasCincin($koordinat, $toleransi)
                : $koordinat;
        }

        return array_map(fn ($bagian) => $this->sederhanakan($bagian, $toleransi), $koordinat);
    }

    private function pangkasCincin(array $titik, float $toleransi): array
    {
        $hasil = $this->douglasPeucker($titik, $toleransi);

        // Cincin harus tetap tertutup dan tetap berupa bidang.
        if (count($hasil) < 4) {
            return $titik;
        }

        $awal = $hasil[0];
        $akhir = $hasil[count($hasil) - 1];
        if ($awal[0] !== $akhir[0] || $awal[1] !== $akhir[1]) {
            $hasil[] = $awal;
        }

        return array_values($hasil);
    }

    private function douglasPeucker(array $titik, float $toleransi): array
    {
        $jumlah = count($titik);
        if ($jumlah < 3) {
            return $titik;
        }

        $terjauh = 0.0;
        $indeks = 0;

        for ($i = 1; $i < $jumlah - 1; $i++) {
            $jarak = $this->jarakKeGaris($titik[$i], $titik[0], $titik[$jumlah - 1]);
            if ($jarak > $terjauh) {
                $terjauh = $jarak;
                $indeks = $i;
            }
        }

        if ($terjauh <= $toleransi) {
            return [$titik[0], $titik[$jumlah - 1]];
        }

        $kiri = $this->douglasPeucker(array_slice($titik, 0, $indeks + 1), $toleransi);
        $kanan = $this->douglasPeucker(array_slice($titik, $indeks), $toleransi);

        array_pop($kiri);

        return array_merge($kiri, $kanan);
    }

    private function jarakKeGaris(array $titik, array $awal, array $akhir): float
    {
        $dx = $akhir[0] - $awal[0];
        $dy = $akhir[1] - $awal[1];

        if ($dx == 0.0 && $dy == 0.0) {
            return hypot($titik[0] - $awal[0], $titik[1] - $awal[1]);
        }

        $t = (($titik[0] - $awal[0]) * $dx + ($titik[1] - $awal[1]) * $dy) / ($dx * $dx + $dy * $dy);
        $t = max(0.0, min(1.0, $t));

        return hypot(
            $titik[0] - ($awal[0] + $t * $dx),
            $titik[1] - ($awal[1] + $t * $dy)
        );
    }

    private function hapusIsi(string $direktori): void
    {
        foreach (glob($direktori . '/*') ?: [] as $berkas) {
            @unlink($berkas);
        }

        @rmdir($direktori);
    }
}
