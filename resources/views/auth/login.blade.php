<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — Sistem Informasi OP Sungai</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('gambar/ikon-layar-depan.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-sungai-100 via-sungai-50 to-emas-50">

<div class="flex min-h-screen items-center justify-center p-4 lg:p-6">
    <div class="grid w-full max-w-4xl overflow-hidden rounded-[26px] bg-white shadow-2xl shadow-sungai-950/10 lg:grid-cols-2">

        {{-- Panel identitas --}}
        <div class="relative hidden flex-col justify-between overflow-hidden bg-sungai-950 p-9 lg:flex">
            {{-- Gelombang dekoratif --}}
            <svg class="pointer-events-none absolute -bottom-10 -left-16 w-[130%] opacity-25" viewBox="0 0 400 160" fill="none">
                <path d="M0 60c40-26 80-26 120 0s80 26 120 0 80-26 120 0 80 26 120 0" stroke="#5db8f8" stroke-width="10" stroke-linecap="round"/>
                <path d="M0 96c40-26 80-26 120 0s80 26 120 0 80-26 120 0 80 26 120 0" stroke="#127cc4" stroke-width="10" stroke-linecap="round"/>
                <path d="M0 132c40-26 80-26 120 0s80 26 120 0 80-26 120 0 80 26 120 0" stroke="#12639e" stroke-width="10" stroke-linecap="round"/>
            </svg>

            <div class="relative">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/5 ring-1 ring-white/10">
                    <x-logo class="h-11"/>
                </div>
                <h1 class="mt-6 text-[26px] font-extrabold leading-tight tracking-tight text-white">
                    Sistem Informasi<br>Operasi &amp; Pemeliharaan Sungai
                </h1>
                <p class="mt-3 max-w-xs text-sm leading-relaxed text-sungai-200/70">
                    Pusat pemantauan laporan inspeksi sungai dari lapangan sampai meja dinas.
                </p>
            </div>

            {{-- Lambang provinsi ditaruh di sini, bukan berdampingan dengan logo
                 aplikasi di atas. Bagian atas menyatakan "aplikasi apa ini",
                 bagian ini menyatakan "di bawah lembaga mana" — dua pernyataan
                 berbeda yang tidak perlu berebut tempat. --}}
            <div class="relative flex items-center gap-3">
                <x-logo-kalsel class="h-10 shrink-0"/>
                <div class="text-[11px] font-semibold uppercase leading-snug tracking-widest text-emas-400">
                    Dinas PUPR<br>Provinsi Kalimantan Selatan
                </div>
            </div>
        </div>

        {{-- Formulir --}}
        <div class="p-8 sm:p-10">
            <div class="mb-7 flex items-center gap-3 lg:hidden">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sungai-950">
                    <x-logo class="h-8"/>
                </div>
                <div class="leading-tight">
                    <p class="text-sm font-bold text-sungai-950">Sistem Informasi OP</p>
                    <p class="text-xs text-slate-500">Dinas PUPR Kalsel</p>
                </div>
                <x-logo-kalsel class="ml-auto h-9 shrink-0"/>
            </div>

            <h2 class="text-xl font-extrabold tracking-tight text-sungai-950">Masuk ke akun staf</h2>
            <p class="mt-1 text-sm text-slate-500">Gunakan akun yang diberikan administrator.</p>

            @if ($errors->any())
                <div class="mt-5 flex items-start gap-2.5 rounded-xl border border-bata-500/20 bg-bata-50 px-3.5 py-3">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-bata-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
                    </svg>
                    <p class="text-[13px] font-medium text-bata-700">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('login.proses') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="username" class="block text-[13px] font-semibold text-slate-700">Username</label>
                    <input id="username" name="username" type="text" required autofocus
                           value="{{ old('username') }}" autocomplete="username"
                           class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900
                                  ring-1 ring-inset ring-slate-200 transition placeholder:text-slate-400
                                  focus:bg-white focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none">
                </div>

                <div>
                    <label for="password" class="block text-[13px] font-semibold text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900
                                  ring-1 ring-inset ring-slate-200 transition placeholder:text-slate-400
                                  focus:bg-white focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none">
                </div>

                <label class="flex items-center gap-2 text-[13px] text-slate-600">
                    <input type="checkbox" name="ingat_saya" value="1"
                           class="h-4 w-4 rounded border-slate-300 text-sungai-600 focus:ring-sungai-500/30">
                    Ingat saya di perangkat ini
                </label>

                <button type="submit"
                        class="w-full rounded-xl bg-sungai-950 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-sungai-950/20
                               transition hover:bg-sungai-900 focus:outline-none focus:ring-2 focus:ring-sungai-500 focus:ring-offset-2">
                    Masuk
                </button>
            </form>

            <p class="mt-6 border-t border-slate-100 pt-5 text-center text-xs leading-relaxed text-slate-400">
                Juru sungai mengirim laporan melalui aplikasi Android, bukan halaman ini.
            </p>
        </div>
    </div>
</div>

</body>
</html>
