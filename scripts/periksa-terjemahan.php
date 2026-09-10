<?php

/**
 * Memastikan tidak ada kunci terjemahan yang tertinggal berbahasa Inggris.
 *
 * Dijalankan dengan: php scripts/periksa-terjemahan.php
 *
 * Kunci yang tidak diterjemahkan tidak menimbulkan galat apa pun — Laravel
 * diam-diam jatuh kembali ke bahasa cadangan, sehingga satu kalimat Inggris
 * bisa bertahan bertahun-tahun di layar pengguna tanpa ada yang menyadarinya.
 * Berkas ini menjadikan hal itu terlihat.
 */

$akar = dirname(__DIR__);

$rata = function (array $a, string $awalan = '') use (&$rata): array {
    $hasil = [];
    foreach ($a as $kunci => $nilai) {
        $hasil = is_array($nilai)
            ? array_merge($hasil, $rata($nilai, "{$awalan}{$kunci}."))
            : array_merge($hasil, ["{$awalan}{$kunci}"]);
    }

    return $hasil;
};

$adaMasalah = false;

foreach (glob($akar . '/lang/en/*.php') as $berkasInggris) {
    $nama = basename($berkasInggris);
    $berkasIndonesia = $akar . '/lang/id/' . $nama;

    if (! file_exists($berkasIndonesia)) {
        echo "HILANG  lang/id/{$nama}\n";
        $adaMasalah = true;
        continue;
    }

    $inggris = $rata(require $berkasInggris);
    $indonesia = $rata(require $berkasIndonesia);

    // Bagian 'custom' dan 'attributes' memang berisi kunci yang berbeda di
    // tiap bahasa; keduanya bukan terjemahan melainkan daftar milik proyek.
    $tertinggal = array_filter(
        array_diff($inggris, $indonesia),
        fn ($k) => ! str_starts_with($k, 'custom.') && ! str_starts_with($k, 'attributes.')
    );

    printf("%-16s %3d kunci, %d tertinggal\n", $nama, count($inggris), count($tertinggal));

    foreach ($tertinggal as $kunci) {
        echo "        belum: {$kunci}\n";
        $adaMasalah = true;
    }
}

exit($adaMasalah ? 1 : 0);
