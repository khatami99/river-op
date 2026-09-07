@extends('layouts.app')

@use('App\Support\Bulan')

@php
    $ki = 'w-full rounded-xl border-0 bg-slate-50 px-3 py-2 text-[13px] text-slate-900 ring-1 ring-inset ring-slate-200 transition focus:bg-white focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
    $adaFilter = $filter['das'] || $filter['jenis'] || $filter['bulan'] || $filter['tahun'] || $filter['bermasalah'];
@endphp

@section('judul', 'Peta Sebaran Laporan')
@section('subjudul', $dataTitik->count() . ' bangunan bertitik, ' . $jumlahBermasalah . ' di antaranya bermasalah')

@push('kepala')
    @vite('resources/js/peta.js')
@endpush

@section('konten')

    {{-- Penyaring --}}
    <form method="GET" class="mb-3.5 rounded-2xl bg-white p-4 ring-1 ring-slate-200/70">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <label class="block">
                <span class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">DAS</span>
                <select name="das" class="{{ $ki }}">
                    <option value="">Semua DAS</option>
                    @foreach ($daftarDas as $d)
                        <option value="{{ $d->id }}" @selected((string) $filter['das'] === (string) $d->id)>{{ $d->nama_das }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Jenis Bangunan</span>
                <select name="jenis" class="{{ $ki }}">
                    <option value="">Semua jenis</option>
                    @foreach ($daftarJenis as $j)
                        <option value="{{ $j->id }}" @selected((string) $filter['jenis'] === (string) $j->id)>{{ $j->nama }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Bulan</span>
                <select name="bulan" class="{{ $ki }}">
                    <option value="">Semua bulan</option>
                    @for ($b = 1; $b <= 12; $b++)
                        <option value="{{ $b }}" @selected((string) $filter['bulan'] === (string) $b)>{{ Bulan::nama($b) }}</option>
                    @endfor
                </select>
            </label>

            <label class="block">
                <span class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Tahun</span>
                <select name="tahun" class="{{ $ki }}">
                    <option value="">Semua tahun</option>
                    @foreach ($daftarTahun as $t)
                        <option value="{{ $t }}" @selected((string) $filter['tahun'] === (string) $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </label>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 rounded-xl bg-sungai-950 px-4 py-2 text-[13px] font-bold text-white transition hover:bg-sungai-900">
                    Terapkan
                </button>
                @if ($adaFilter)
                    <a href="{{ route('sebaran.index') }}"
                       class="rounded-xl bg-slate-100 px-3 py-2 text-[13px] font-bold text-slate-600 transition hover:bg-slate-200">
                        Reset
                    </a>
                @endif
            </div>
        </div>

        <label class="mt-3 flex w-fit cursor-pointer items-center gap-2.5 text-[13px] text-slate-700">
            <input type="checkbox" name="bermasalah" value="1" @checked($filter['bermasalah'])
                   class="h-4 w-4 rounded border-slate-300 text-bata-600 focus:ring-bata-500/30">
            Tampilkan hanya bangunan yang punya temuan bermasalah
        </label>
    </form>

    @if ($tanpaTitik > 0)
        <div class="mb-3.5 flex items-start gap-3 rounded-2xl bg-emas-50 p-4 ring-1 ring-emas-500/20">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emas-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
            </svg>
            <p class="text-[13px] leading-relaxed text-emas-900">
                <span class="font-semibold">{{ $tanpaTitik }} bangunan tidak muncul di peta</span>
                karena diperiksa sebelum aplikasi juru menangkap titik GPS. Koordinatnya tidak dapat
                dilengkapi belakangan, jadi peta ini hanya menggambarkan pemeriksaan sejak GPS aktif.
            </p>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-[12px]">
                <span class="flex items-center gap-2">
                    <span class="h-3.5 w-3.5 rounded-full border-2 border-white bg-[#c8102e] shadow"></span>
                    <span class="text-slate-600">Ada temuan bermasalah</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="h-3.5 w-3.5 rounded-full border-2 border-white bg-[#127cc4] shadow"></span>
                    <span class="text-slate-600">Tidak ada temuan bermasalah</span>
                </span>
            </div>

            {{-- Lapisan. Titik DAS terpisah supaya dapat dimatikan; bila batas
                 wilayah DAS sudah didapat, isinya tinggal diganti. --}}
            <label class="flex cursor-pointer items-center gap-2 text-[12px] font-semibold text-slate-600">
                <input type="checkbox" id="lapisan-das" checked
                       class="h-4 w-4 rounded border-slate-300 text-sungai-600 focus:ring-sungai-500/30">
                Tampilkan titik DAS
            </label>
        </div>

        @if ($dataTitik->isEmpty())
            <div class="border-t border-slate-100 px-6 py-20 text-center">
                <p class="text-sm font-bold text-slate-700">Tidak ada bangunan yang cocok</p>
                <p class="mt-1 text-[13px] text-slate-400">
                    {{ $adaFilter ? 'Coba longgarkan penyaringnya.' : 'Belum ada laporan dengan titik GPS.' }}
                </p>
            </div>
        @else
            <div id="peta-sebaran"
                 class="h-[600px] w-full border-t border-slate-100"
                 data-titik="{{ $dataTitik->toJson() }}"
                 data-das="{{ $dataDas->toJson() }}"></div>
        @endif
    </div>

@endsection
