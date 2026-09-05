<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', 'Dashboard') — Sistem Informasi OP Sungai</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white lg:h-screen lg:overflow-hidden">

@php
    $menu = [
        ['rute' => 'dashboard',     'aktif' => 'dashboard', 'label' => 'Dashboard',      'ikon' => 'M3.5 11.5 12 4l8.5 7.5M6 10v9a1 1 0 001 1h3v-5h4v5h3a1 1 0 001-1v-9'],
        ['rute' => 'laporan.index', 'aktif' => 'laporan.*', 'label' => 'Laporan OP',     'ikon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6a1 1 0 01.7.3l5.4 5.4a1 1 0 01.3.7V19a2 2 0 01-2 2z'],
        ['rute' => 'rekap.index',   'aktif' => 'rekap.*',   'label' => 'Rekapitulasi',   'ikon' => 'M9 19v-6m4 6V7m4 12v-9M5 21h14a1 1 0 001-1V4a1 1 0 00-1-1H5a1 1 0 00-1 1v16a1 1 0 001 1z'],
        ['rute' => 'juru.index',    'aktif' => 'juru.*',    'label' => 'Juru Sungai',    'ikon' => 'M17 20h5v-2a3 3 0 00-5.4-1.9M17 20H7m10 0v-2c0-.7-.1-1.3-.4-1.9M7 20H2v-2a3 3 0 015.4-1.9M7 20v-2c0-.7.1-1.3.4-1.9m0 0a5 5 0 019.2 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ['rute' => 'das.index',     'aktif' => 'das.*',     'label' => 'DAS',            'ikon' => 'M3 15c2.5-2 5.5-2 8 0s5.5 2 8 0M3 9c2.5-2 5.5-2 8 0s5.5 2 8 0'],
        ['rute' => 'wilayah.index', 'aktif' => 'wilayah.*', 'label' => 'Wilayah Sungai', 'ikon' => 'M12 3v18M5 8l7-5 7 5M5 8v8l7 5 7-5V8'],
    ];

    $staf = auth('staf')->user();

    // Manajemen pengguna hanya relevan bagi admin.
    if ($staf?->isAdmin()) {
        $menu[] = ['rute' => 'staf.index', 'aktif' => 'staf.*', 'label' => 'Pengguna', 'ikon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'];
    }
@endphp

<div class="flex min-h-screen flex-col overflow-hidden bg-white lg:h-screen lg:flex-row">

        {{-- Rel navigasi --}}
        <aside class="hidden w-[248px] shrink-0 flex-col bg-sungai-950 lg:flex">
            <div class="flex items-center gap-3 px-5 py-6">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/5 ring-1 ring-white/10">
                    <x-logo class="h-7 w-7"/>
                </div>
                <div class="leading-tight">
                    <p class="text-[13px] font-bold text-white">Sistem Informasi OP</p>
                    <p class="text-[11px] text-sungai-300/70">Dinas PUPR Kalsel</p>
                </div>
            </div>

            <nav class="flex-1 space-y-1 px-3">
                @foreach ($menu as $item)
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
            </nav>

            <div class="m-3 rounded-2xl bg-white/5 p-3 ring-1 ring-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emas-400 text-sm font-extrabold text-emas-900">
                        {{ strtoupper(substr($staf->nama ?? 'S', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1 leading-tight">
                        <p class="truncate text-[13px] font-semibold text-white">{{ $staf->nama ?? '-' }}</p>
                        <p class="text-[11px] capitalize text-sungai-300/60">{{ $staf->role ?? '' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar"
                                class="flex h-8 w-8 items-center justify-center rounded-lg text-sungai-300/60 transition hover:bg-white/10 hover:text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Area konten --}}
        <div class="flex min-w-0 flex-1 flex-col lg:overflow-hidden">

            <header class="shrink-0 border-b border-slate-100 px-5 pb-4 pt-5 sm:px-7">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="min-w-0">
                        <h1 class="truncate text-[22px] font-extrabold tracking-tight text-sungai-950">
                            @yield('judul', 'Dashboard')
                        </h1>
                        @hasSection('subjudul')
                            <p class="mt-0.5 truncate text-[13px] text-slate-500">@yield('subjudul')</p>
                        @endif
                    </div>
                    @yield('aksi-header')
                </div>

                {{-- Navigasi ringkas untuk layar kecil --}}
                <nav class="-mx-1 mt-4 flex gap-1 overflow-x-auto scrollbar-halus lg:hidden">
                    @foreach ($menu as $item)
                        <a href="{{ route($item['rute']) }}"
                           class="shrink-0 whitespace-nowrap rounded-lg px-3 py-1.5 text-[13px] font-semibold
                                  {{ request()->routeIs($item['aktif'])
                                     ? 'bg-sungai-950 text-white'
                                     : 'text-slate-500 hover:bg-slate-100' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </header>

            <main class="flex-1 bg-slate-50/60 px-5 py-5 scrollbar-halus sm:px-7 lg:overflow-y-auto">
                @if (session('sukses'))
                    <div class="mb-4 rounded-xl border border-hijau-500/20 bg-hijau-50 px-4 py-3 text-sm font-medium text-hijau-700">
                        {{ session('sukses') }}
                    </div>
                @endif

                @yield('konten')

                <p class="mt-8 text-center text-[11px] text-slate-400">
                    Sistem Informasi Operasi &amp; Pemeliharaan Sungai &middot; Dinas PUPR Provinsi Kalimantan Selatan
                </p>
            </main>
        </div>
</div>

</body>
</html>
