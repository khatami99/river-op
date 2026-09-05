@extends('layouts.app')

@use('App\Support\Bulan')

@section('judul', 'Dashboard')
@section('subjudul', 'Ringkasan pelaporan ' . Bulan::nama($bulan) . ' ' . $tahun)

@section('aksi-header')
    <form method="GET" class="flex shrink-0 items-center gap-1.5 rounded-xl bg-slate-100 p-1">
        <select name="bulan" onchange="this.form.submit()"
                class="rounded-lg border-0 bg-transparent py-1.5 pl-2.5 pr-7 text-[13px] font-semibold text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sungai-500">
            @foreach (Bulan::NAMA as $nomor => $nama)
                <option value="{{ $nomor }}" @selected($nomor === $bulan)>{{ $nama }}</option>
            @endforeach
        </select>
        <select name="tahun" onchange="this.form.submit()"
                class="rounded-lg border-0 bg-transparent py-1.5 pl-2.5 pr-7 text-[13px] font-semibold text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sungai-500">
            @foreach ($daftarTahun as $t)
                <option value="{{ $t }}" @selected($t === $tahun)>{{ $t }}</option>
            @endforeach
        </select>
    </form>
@endsection

@section('konten')

    @php
        $keliling = 2 * M_PI * 42;
        $sisa = $keliling * (1 - $persentase / 100);
    @endphp

    {{-- Bento ringkasan --}}
    <div class="grid grid-cols-2 gap-3.5 xl:grid-cols-4">

        {{-- Kartu utama: progres pelaporan --}}
        <div class="relative col-span-2 overflow-hidden rounded-2xl bg-sungai-950 p-6 xl:row-span-2">
            <svg class="pointer-events-none absolute -bottom-8 -right-10 w-72 opacity-20" viewBox="0 0 400 120" fill="none">
                <path d="M0 40c40-24 80-24 120 0s80 24 120 0 80-24 120 0 80 24 120 0" stroke="#5db8f8" stroke-width="9" stroke-linecap="round"/>
                <path d="M0 76c40-24 80-24 120 0s80 24 120 0 80-24 120 0 80 24 120 0" stroke="#127cc4" stroke-width="9" stroke-linecap="round"/>
            </svg>

            <div class="relative flex h-full flex-col justify-between gap-6">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-emas-400">Progres Pelaporan</p>
                    <p class="mt-1 text-lg font-bold text-white">{{ Bulan::nama($bulan) }} {{ $tahun }}</p>
                </div>

                <div class="flex items-center gap-6">
                    <div class="relative h-[104px] w-[104px] shrink-0">
                        <svg class="h-full w-full -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="42" fill="none" stroke="currentColor" class="text-white/10" stroke-width="11"/>
                            <circle cx="50" cy="50" r="42" fill="none" stroke="currentColor" class="text-emas-400"
                                    stroke-width="11" stroke-linecap="round"
                                    stroke-dasharray="{{ $keliling }}" stroke-dashoffset="{{ $sisa }}"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-extrabold tabular-nums text-white">{{ $persentase }}<span class="text-sm">%</span></span>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[34px] font-extrabold leading-none tabular-nums text-white">
                            {{ $sudahLapor }}<span class="text-lg font-bold text-sungai-300/50">/{{ $juruAktif }}</span>
                        </p>
                        <p class="mt-2 text-[13px] leading-relaxed text-sungai-200/70">
                            juru sungai sudah<br>mengirim laporan
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Belum melapor: satu-satunya kartu beraksen penuh, karena ini yang perlu ditindaklanjuti --}}
        <div class="relative overflow-hidden rounded-2xl bg-emas-400 p-5">
            <p class="text-[11px] font-bold uppercase tracking-widest text-emas-900/70">Belum Melapor</p>
            <p class="mt-2 text-[40px] font-extrabold leading-none tabular-nums text-emas-950">{{ $belumLapor }}</p>
            <p class="mt-1.5 text-[12px] font-semibold text-emas-900/70">perlu ditindaklanjuti</p>
            <svg class="absolute -bottom-4 -right-3 h-20 w-20 text-emas-900/10" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
            </svg>
        </div>

        {{-- Sudah melapor --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200/70">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Sudah Melapor</p>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-hijau-50">
                    <svg class="h-4 w-4 text-hijau-600" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-[40px] font-extrabold leading-none tabular-nums text-hijau-600">{{ $sudahLapor }}</p>
            <p class="mt-1.5 text-[12px] font-medium text-slate-400">periode ini</p>
        </div>

        {{-- Juru aktif --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200/70">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Juru Aktif</p>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sungai-50">
                    <svg class="h-4 w-4 text-sungai-600" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.4-1.9M17 20H7m10 0v-2c0-.7-.1-1.3-.4-1.9M7 20H2v-2a3 3 0 015.4-1.9M7 20v-2c0-.7.1-1.3.4-1.9m0 0a5 5 0 019.2 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-[40px] font-extrabold leading-none tabular-nums text-sungai-950">{{ $juruAktif }}</p>
            <p class="mt-1.5 text-[12px] font-medium text-slate-400">terdaftar di sistem</p>
        </div>

        {{-- Total laporan --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200/70">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Total Laporan</p>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100">
                    <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6a1 1 0 01.7.3l5.4 5.4a1 1 0 01.3.7V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-[40px] font-extrabold leading-none tabular-nums text-sungai-950">{{ $totalLaporanPeriode }}</p>
            <p class="mt-1.5 text-[12px] font-medium text-slate-400">masuk periode ini</p>
        </div>
    </div>

    {{-- Dua panel bawah --}}
    <div class="mt-3.5 grid gap-3.5 lg:grid-cols-5">

        {{-- Laporan terbaru --}}
        <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70 lg:col-span-3">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="text-sm font-bold text-sungai-950">Laporan Terbaru</h2>
                <a href="{{ route('laporan.index') }}"
                   class="inline-flex items-center gap-1 text-[13px] font-semibold text-sungai-600 transition hover:text-sungai-800">
                    Lihat semua
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            @if ($laporanTerbaru->isEmpty())
                <x-kosong judul="Belum ada laporan masuk" pesan="Laporan dari aplikasi juru akan muncul di sini."/>
            @else
                <ul class="divide-y divide-slate-100 border-t border-slate-100">
                    @foreach ($laporanTerbaru as $item)
                        <li>
                            <a href="{{ route('laporan.show', $item) }}"
                               class="flex items-center gap-3.5 px-5 py-3 transition hover:bg-slate-50">
                                <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-xl bg-sungai-50 leading-none">
                                    <span class="text-[13px] font-extrabold tabular-nums text-sungai-800">
                                        {{ optional($item->tanggal_inspeksi)->format('d') }}
                                    </span>
                                    <span class="mt-0.5 text-[9px] font-bold uppercase tracking-wide text-sungai-500">
                                        {{ Bulan::singkat($item->bulan) }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[13.5px] font-semibold text-slate-800">{{ $item->juruSungai?->nama ?? '-' }}</p>
                                    <p class="truncate text-[12px] text-slate-400">{{ $item->wilayahSungai?->nama_sungai ?? '-' }}</p>
                                </div>
                                <svg class="h-4 w-4 shrink-0 text-slate-300" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Belum melapor --}}
        <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70 lg:col-span-2">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="text-sm font-bold text-sungai-950">Belum Melapor</h2>
                <x-lencana warna="emas">{{ $juruBelumLapor->count() }} juru</x-lencana>
            </div>

            @if ($juruBelumLapor->isEmpty())
                <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-hijau-50">
                        <svg class="h-6 w-6 text-hijau-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-hijau-700">Semua juru sudah melapor</p>
                    <p class="mt-1 text-[13px] text-slate-400">Periode {{ Bulan::nama($bulan) }} {{ $tahun }}</p>
                </div>
            @else
                <ul class="max-h-[340px] divide-y divide-slate-100 overflow-y-auto border-t border-slate-100 scrollbar-halus">
                    @foreach ($juruBelumLapor as $orang)
                        <li>
                            <a href="{{ route('juru.show', $orang) }}"
                               class="flex items-center gap-3 px-5 py-3 transition hover:bg-slate-50">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emas-100 text-[12px] font-extrabold text-emas-800">
                                    {{ strtoupper(substr($orang->nama, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[13.5px] font-semibold text-slate-800">{{ $orang->nama }}</p>
                                    <p class="truncate text-[12px] text-slate-400">{{ $orang->wilayahSungai?->nama_sungai ?? '-' }}</p>
                                </div>
                                <svg class="h-4 w-4 shrink-0 text-slate-300" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

@endsection
