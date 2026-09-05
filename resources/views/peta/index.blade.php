@extends('layouts.app')

@section('judul', 'Peta DAS')
@section('subjudul', $das->count() . ' DAS, ' . ($das->count() - $belumBerkoordinat->count()) . ' sudah bertitik')

@push('kepala')
    @vite('resources/js/peta.js')
@endpush

@section('konten')

    @if ($belumBerkoordinat->isNotEmpty())
        <div class="mb-3.5 flex flex-wrap items-start gap-3 rounded-2xl bg-emas-50 p-4 ring-1 ring-emas-500/20">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emas-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
            </svg>
            <div class="min-w-0 flex-1">
                <p class="text-[13px] font-semibold text-emas-900">
                    {{ $belumBerkoordinat->count() }} DAS belum punya titik dan tidak muncul di peta
                </p>
                <p class="mt-1 text-[12px] leading-relaxed text-emas-800/80">
                    @foreach ($belumBerkoordinat as $d)
                        <a href="{{ route('das.edit', $d) }}" class="font-semibold underline">{{ $d->nama_das }}</a>{{ ! $loop->last ? ', ' : '' }}
                    @endforeach
                    — buka lalu tentukan titiknya dengan mengklik peta pada formulir.
                </p>
            </div>
        </div>
    @endif

    <div class="grid gap-3.5 lg:grid-cols-3">

        {{-- Peta --}}
        <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70 lg:col-span-2">
            <div id="peta-das"
                 class="h-[560px] w-full"
                 data-das="{{ $dataPeta->toJson() }}"></div>
        </div>

        {{-- Panel keterangan --}}
        <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
            <div id="panel-kosong" class="flex h-full flex-col items-center justify-center px-6 py-16 text-center">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-sungai-50">
                    <svg class="h-6 w-6 text-sungai-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.7 16.7 12 22l-5.7-5.3a8 8 0 1111.4 0z"/>
                        <circle cx="12" cy="11" r="2.5"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">Pilih sebuah DAS</p>
                <p class="mt-1 max-w-[15rem] text-[13px] leading-relaxed text-slate-400">
                    Klik penanda di peta untuk melihat juru yang bertugas beserta ringkasan laporannya.
                </p>
            </div>

            <div id="panel-das" class="hidden"></div>
        </div>
    </div>

    {{-- Keterangan warna penanda --}}
    <div class="mt-3.5 flex flex-wrap items-center gap-x-5 gap-y-2 rounded-2xl bg-white px-5 py-3.5 text-[12px] ring-1 ring-slate-200/70">
        <span class="font-bold uppercase tracking-wider text-slate-400">Keterangan</span>
        <span class="flex items-center gap-2">
            <span class="h-3.5 w-3.5 rounded-full border-2 border-white bg-[#127cc4] shadow"></span>
            <span class="text-slate-600">Ada juru, tidak ada temuan bermasalah</span>
        </span>
        <span class="flex items-center gap-2">
            <span class="h-3.5 w-3.5 rounded-full border-2 border-white bg-[#c8102e] shadow"></span>
            <span class="text-slate-600">Ada temuan bermasalah</span>
        </span>
        <span class="flex items-center gap-2">
            <span class="h-3.5 w-3.5 rounded-full border-2 border-white bg-[#94a3b8] shadow"></span>
            <span class="text-slate-600">Belum ada juru bertugas</span>
        </span>
        <span class="text-slate-400">Angka pada penanda menunjukkan jumlah juru.</span>
    </div>

@endsection
