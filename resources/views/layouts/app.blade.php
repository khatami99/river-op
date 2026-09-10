<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">

    {{-- Dijalankan sebelum apa pun digambar.

         Pilihan tema disimpan di peramban, dan membacanya lewat modul biasa
         berarti halaman sempat tergambar terang lebih dulu lalu berubah
         gelap — kedip putih yang menyakitkan mata, persis pada orang yang
         memilih mode gelap karena silau. Karena itu skrip ini kecil, sinkron,
         dan berdiri di sini, bukan di berkas modul.

         Tanpa isi localStorage, tidak ada atribut yang dipasang dan CSS yang
         memutuskan lewat setelan perangkat. --}}
    <script>
        (function () {
            try {
                var pilihan = localStorage.getItem('tema');
                if (pilihan === 'gelap' || pilihan === 'terang') {
                    document.documentElement.dataset.tema = pilihan;
                }
            } catch (e) {}
        })();
    </script>
    <title>@yield('judul', 'Dashboard') — Sistem Informasi OP Sungai</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('gambar/ikon-layar-depan.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('kepala')
</head>
<body class="bg-permukaan lg:h-screen lg:overflow-hidden">

@php
    /**
     * Menu dikelompokkan, bukan dideret rata. Sebelas tautan sejajar tanpa
     * pemisah memaksa mata memindai seluruhnya setiap kali; tiga kelompok
     * membuatnya cukup memindai satu bagian.
     *
     * Peta DAS dan Peta Sebaran tidak lagi berdiri sendiri: keduanya cara
     * melihat data yang sudah punya menunya, jadi peta DAS menyatu ke halaman
     * DAS dan peta sebaran menjadi bentuk tampilan Laporan OP.
     */
    $kelompok = [
        'Pemantauan' => [
            ['rute' => 'dashboard',     'aktif' => 'dashboard', 'label' => 'Dashboard',      'ikon' => 'M3.5 11.5 12 4l8.5 7.5M6 10v9a1 1 0 001 1h3v-5h4v5h3a1 1 0 001-1v-9'],
            ['rute' => 'laporan.index', 'aktif' => 'laporan.*', 'label' => 'Laporan OP',     'ikon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6a1 1 0 01.7.3l5.4 5.4a1 1 0 01.3.7V19a2 2 0 01-2 2z'],
            ['rute' => 'rekap.index',   'aktif' => 'rekap.*',   'label' => 'Rekapitulasi',   'ikon' => 'M9 19v-6m4 6V7m4 12v-9M5 21h14a1 1 0 001-1V4a1 1 0 00-1-1H5a1 1 0 00-1 1v16a1 1 0 001 1z'],
        ],
        // Master Data disusun menurut wadahnya: wilayah sungai menaungi DAS,
        // dan DAS menaungi juru. Menunya jadi ikut menerangkan susunan
        // datanya, dan itu pula urutan pengisiannya — DAS tidak bisa dibuat
        // sebelum wilayah sungainya ada, juru tidak bisa ditugaskan sebelum
        // DAS-nya ada.
        'Master Data' => [
            ['rute' => 'wilayah.index', 'aktif' => 'wilayah.*', 'label' => 'Wilayah Sungai',       'ikon' => 'M12 3v18M5 8l7-5 7 5M5 8v8l7 5 7-5V8'],
            ['rute' => 'das.index',     'aktif' => 'das.*',     'label' => 'Daerah Aliran Sungai', 'ikon' => 'M3 15c2.5-2 5.5-2 8 0s5.5 2 8 0M3 9c2.5-2 5.5-2 8 0s5.5 2 8 0'],
            ['rute' => 'juru.index',    'aktif' => 'juru.*',    'label' => 'Juru Sungai',          'ikon' => 'M17 20h5v-2a3 3 0 00-5.4-1.9M17 20H7m10 0v-2c0-.7-.1-1.3-.4-1.9M7 20H2v-2a3 3 0 015.4-1.9M7 20v-2c0-.7.1-1.3.4-1.9m0 0a5 5 0 019.2 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ],
        'Sistem' => [
            ['rute' => 'sinkronisasi.index', 'aktif' => 'sinkronisasi.*', 'label' => 'Sinkronisasi', 'ikon' => 'M4 4v5h5M20 20v-5h-5M20 9A8 8 0 006.3 5.3L4 8m16 8-2.3 2.7A8 8 0 014 15'],
        ],
    ];

    $staf = auth('staf')->user();

    // Manajemen pengguna dan jejak perubahan hanya relevan bagi admin.
    if ($staf?->isAdmin()) {
        $kelompok['Sistem'][] = ['rute' => 'staf.index', 'aktif' => 'staf.*', 'label' => 'Pengguna', 'ikon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'];
        $kelompok['Sistem'][] = ['rute' => 'log.index', 'aktif' => 'log.*', 'label' => 'Log Aktivitas', 'ikon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6a1 1 0 01.7.3l4.4 4.4a1 1 0 01.3.7V19a2 2 0 01-2 2z'];
    }

    // Menu ringkas layar kecil tetap butuh deretan datar.
    $menu = collect($kelompok)->flatten(1)->all();
@endphp

<div class="flex min-h-screen flex-col bg-permukaan lg:h-screen lg:flex-row lg:overflow-hidden">

        {{-- Rel navigasi --}}
        <aside class="hidden w-[248px] shrink-0 flex-col bg-sungai-950 lg:flex">
            <div class="flex items-center gap-3 px-5 py-6">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/5 ring-1 ring-white/10">
                    <x-logo class="h-9"/>
                </div>
                <div class="leading-tight">
                    <p class="text-[13px] font-bold text-white">Sistem Informasi OP</p>
                    <p class="text-[11px] text-sungai-300/70">Dinas PUPR Kalsel</p>
                </div>
            </div>

            <nav class="flex-1 space-y-5 px-3">
                @foreach ($kelompok as $namaKelompok => $isiKelompok)
                <div class="space-y-1">
                    <p class="px-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-sungai-300/70">
                        {{ $namaKelompok }}
                    </p>
                @foreach ($isiKelompok as $item)
                    @php $sedangAktif = request()->routeIs($item['aktif']); @endphp
                    <a href="{{ route($item['rute']) }}"
                       class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] font-semibold transition
                              {{ $sedangAktif
                                 ? 'bg-white/10 text-white'
                                 : 'text-sungai-200/60 hover:bg-white/5 hover:text-white' }}">
                        @if ($sedangAktif)
                            <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-emas-400"></span>
                        @endif
                        <svg class="h-[18px] w-[18px] shrink-0 {{ $sedangAktif ? 'text-emas-400' : '' }}"
                             fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['ikon'] }}"/>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
                </div>
                @endforeach
            </nav>

        </aside>

        {{-- Area konten --}}
        <div class="flex min-w-0 flex-1 flex-col lg:overflow-hidden">

            {{-- Kop halaman melekat.

                 Isinya kini cuma dua pertanyaan: aku sedang di halaman apa,
                 dan sedang masuk sebagai siapa. Tombol tindakan sudah pindah
                 ke badan halaman lewat komponen aksi-halaman — kop bukan
                 tempat kendali, dan alasan itu sama persis dengan alasan
                 pemilih bulan dulu dikeluarkan dari sini.

                 Tetap melekat walau isinya menyusut, sebab di layar sempit
                 seluruh halaman menggulung dan navigasi ringkasnya ikut hilang
                 bersama kop. Orientasi dan jalan keluar harus selalu ada, tak
                 peduli sudah berapa jauh digulung.

                 z-index-nya sengaja tinggi. Leaflet memberi kontrol petanya
                 z-index 800, dan kontainer peta tidak membentuk konteks
                 penumpukan sendiri — jadi tanpa angka di atas itu, tombol
                 perbesar peta akan tergambar menembus kop yang melekat. --}}
            <header class="sticky top-0 z-[1000] shrink-0 border-b border-garis-halus bg-permukaan px-5 pb-4 pt-5 sm:px-7">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h1 class="truncate text-[22px] font-extrabold tracking-tight text-judul">
                            @yield('judul', 'Dashboard')
                        </h1>
                        @hasSection('subjudul')
                            <p class="mt-0.5 truncate text-[13px] text-teks-samar">@yield('subjudul')</p>
                        @endif
                    </div>
                    <div class="flex shrink-0 items-center gap-2.5">
                        <x-pengalih-tema/>
                        <x-menu-akun :staf="$staf"/>
                    </div>
                </div>

                {{-- Navigasi ringkas untuk layar kecil --}}
                <nav class="scrollbar-halus -mx-1 mt-4 flex gap-1 overflow-x-auto lg:hidden">
                    @foreach ($menu as $item)
                        <a href="{{ route($item['rute']) }}"
                           class="shrink-0 whitespace-nowrap rounded-lg px-3 py-1.5 text-[13px] font-semibold
                                  {{ request()->routeIs($item['aktif'])
                                     ? 'bg-aksi text-aksi-teks'
                                     : 'text-teks-samar hover:bg-permukaan-sedang' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </header>

            <main class="flex-1 bg-dasar px-5 py-5 scrollbar-halus sm:px-7 lg:overflow-y-auto">
                @if (session('sukses'))
                    <div data-pesan-sesaat="sukses" class="mb-4 rounded-xl border border-hijau-500/20 bg-aman-latar px-4 py-3 text-sm font-medium text-aman-teks">
                        {{ session('sukses') }}
                    </div>
                @endif

                @yield('konten')

                <p class="mt-8 text-center text-[11px] text-teks-samar">
                    Sistem Informasi Operasi &amp; Pemeliharaan Sungai &middot; Dinas PUPR Provinsi Kalimantan Selatan
                </p>
            </main>
        </div>
</div>

</body>
</html>
