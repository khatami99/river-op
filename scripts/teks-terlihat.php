<?php

/**
 * Mengumpulkan seluruh teks yang benar-benar terlihat pengguna pada Blade.
 *
 * Mencari kata Inggris dengan grep biasa selalu gagal di sini: kata seperti
 * "filter", "submit", dan "password" jauh lebih sering muncul sebagai nama
 * rute, atribut HTML, atau nama kolom daripada sebagai kalimat di layar.
 * Berkas ini memisahkan keduanya, sehingga yang tersisa untuk diperiksa
 * hanyalah kalimat yang sungguh dibaca pegawai dinas.
 *
 * Dijalankan dengan: php scripts/teks-terlihat.php [--asing|--santai]
 */

$akar = dirname(__DIR__);
$saring = $argv[1] ?? null;

/** Kata Inggris yang sering menyelinap ke antarmuka. */
$asing = [
    'save', 'cancel', 'delete', 'edit', 'search', 'filter', 'export', 'import',
    'login', 'logout', 'username', 'password', 'dashboard', 'settings', 'loading',
    'success', 'failed', 'error', 'showing', 'results', 'back', 'next', 'previous',
    'close', 'add', 'update', 'create', 'view', 'detail', 'status', 'active',
    'inactive', 'submit', 'total', 'sync', 'form', 'user', 'name', 'date', 'yes',
    'no', 'has', 'been', 'taken', 'the', 'field', 'required', 'invalid',
];

/** Penanda ragam santai yang tidak pantas pada perangkat pemerintahan. */
$santai = [
    'ya', 'yaa', 'dong', 'aja', 'kok', 'nih', 'deh', 'sih', 'yuk', 'gak',
    'nggak', 'banget', 'gitu', 'kayak', 'udah', 'lagi ya', 'bang',
];

$temuan = [];

$daftar = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($akar . '/resources/views', FilesystemIterator::SKIP_DOTS)
);

foreach ($daftar as $berkas) {
    if (! $berkas->isFile() || ! str_ends_with($berkas->getFilename(), '.blade.php')) {
        continue;
    }

    $jalur = str_replace($akar . DIRECTORY_SEPARATOR, '', $berkas->getPathname());

    // Dibaca sebagai satu kesatuan, bukan baris demi baris. Teks di dalam
    // Blade kerap dipisahkan dari tag pembukanya oleh pergantian baris —
    // "Export Excel" pada halaman laporan berdiri sendirian sesudah </svg> —
    // dan pemindaian per baris melewatkannya tanpa suara.
    $isi = file_get_contents($berkas->getPathname());

    $nomorBaris = function (int $posisi) use ($isi): int {
        return substr_count(substr($isi, 0, $posisi), PHP_EOL === "
" ? "
" : "
") + 1;
    };

    $potonganBernomor = [];

    preg_match_all('/>([^<>{}]{2,})</', $isi, $m1, PREG_OFFSET_CAPTURE);
    preg_match_all('/(?:placeholder|title|alt)="([^"]{2,})"/', $isi, $m2, PREG_OFFSET_CAPTURE);

    foreach (array_merge($m1[1] ?? [], $m2[1] ?? []) as [$teks, $posisi]) {
        $potonganBernomor[] = [$teks, $nomorBaris($posisi)];
    }

    {
        $potongan = [];

        foreach ($potonganBernomor as [$t, $nomorTeks]) {
            $t = trim(html_entity_decode($t));

            // Sisa sintaksis Blade, kelas CSS, dan penggalan kode.
            if ($t === '' || str_contains($t, '@') || str_contains($t, '$')
                || preg_match('/^[\s\W\d]+$/u', $t)) {
                continue;
            }

            $potongan[$t] = $nomorTeks;
        }

        foreach ($potongan as $t => $nomorTeks) {
            $kata = preg_split('/[^A-Za-zÀ-ÿ]+/u', mb_strtolower($t), -1, PREG_SPLIT_NO_EMPTY);

            $cocokAsing = array_values(array_intersect($kata, $asing));
            $cocokSantai = array_values(array_intersect($kata, $santai));

            if ($saring === '--asing' && ! $cocokAsing) {
                continue;
            }
            if ($saring === '--santai' && ! $cocokSantai) {
                continue;
            }

            $tanda = '';
            if ($cocokAsing) {
                $tanda .= ' [asing: ' . implode(',', $cocokAsing) . ']';
            }
            if ($cocokSantai) {
                $tanda .= ' [santai: ' . implode(',', $cocokSantai) . ']';
            }

            $temuan[] = sprintf('%s:%d  %s%s', $jalur, $nomorTeks, $t, $tanda);
        }
    }
}

echo implode("\n", $temuan), "\n\n", count($temuan), " baris teks\n";
