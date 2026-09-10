@extends('layouts.app')

@section('judul', 'Daerah Aliran Sungai')
@section('subjudul')
    <span id="jumlah-hasil">{{ $das->total() }}{{ ' daerah aliran sungai terdaftar' }}</span>
@endsection

@section('aksi-header')
    @if (auth('staf')->user()?->isAdmin())
        <a href="{{ route('das.create') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-aksi px-4 py-2.5 text-[13px] font-bold text-aksi-teks shadow-lg shadow-aksi/25 transition hover:bg-aksi-sorot">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
            </svg>
            Tambah DAS
        </a>
    @endif
@endsection

@php
    $kelasInput = 'w-full rounded-xl border-0 bg-permukaan-lembut px-3 py-2 text-[13px] font-medium text-teks ring-1 ring-inset ring-garis transition focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
@endphp

@push('kepala')
    @vite('resources/js/peta.js')
@endpush

@section('konten')

    @if ($belumBerkoordinat->isNotEmpty())
        <div class="mb-3.5 flex flex-wrap items-start gap-3 rounded-2xl bg-peringatan-latar p-4 ring-1 ring-peringatan-teks/25">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-peringatan-teks" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
            </svg>
            <div class="min-w-0 flex-1">
                <p class="text-[13px] font-semibold text-peringatan-teks-pekat">
                    {{ $belumBerkoordinat->count() }} DAS belum punya titik dan tidak muncul di peta
                </p>
                <p class="mt-1 text-[12px] leading-relaxed text-peringatan-teks">
                    Buka DAS-nya lalu tentukan titiknya dengan mengklik peta pada formulir.
                    Titik ini perkiraan saja &mdash; DAS sebenarnya berupa wilayah, bukan titik.
                </p>
            </div>
        </div>
    @endif

    {{-- Peta. Dulu halaman tersendiri, kini menyatu dengan daftarnya. --}}
    @if ($dataPeta->isNotEmpty())
        <div class="mb-3.5 grid gap-3.5 lg:grid-cols-3">
            <div class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70 lg:col-span-2">
                <div id="peta-das" class="relative h-[clamp(18rem,42vh,26rem)] w-full" data-das="{{ $dataPeta->toJson() }}"></div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
                <div id="panel-kosong" class="flex h-full flex-col items-center justify-center px-6 py-12 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-info-latar">
                        <svg class="h-6 w-6 text-info-teks" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.7 16.7 12 22l-5.7-5.3a8 8 0 1111.4 0z"/>
                            <circle cx="12" cy="11" r="2.5"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-teks-sedang">Pilih sebuah DAS</p>
                    <p class="mt-1 max-w-[15rem] text-[13px] leading-relaxed text-teks-samar">
                        Klik penanda di peta untuk melihat juru yang bertugas beserta ringkasan laporannya.
                    </p>
                </div>
                <div id="panel-das" class="hidden"></div>
            </div>
        </div>
    @endif


    @error('hapus')
        <div class="mb-3.5 rounded-2xl bg-bahaya-latar px-4 py-3 text-[13px] font-medium text-bahaya-teks ring-1 ring-bahaya-teks/25">
            {{ $message }}
        </div>
    @enderror

    <form method="GET" data-saring data-hasil="hasil-das"
          class="rounded-2xl bg-permukaan p-4 ring-1 ring-garis/70">
        <label class="mb-1 block text-[12px] font-bold uppercase tracking-wider text-teks-lembut">Pencarian</label>
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" name="cari" value="{{ $filter['cari'] }}" placeholder="Nama atau kode DAS"
                   class="{{ $kelasInput }} max-w-md flex-1">
            <button type="submit" class="rounded-xl bg-cari px-4 py-2 text-[13px] font-bold text-cari-teks transition hover:bg-cari-sorot">
                Cari
            </button>
            @if ($filter['cari'])
                <a href="{{ route('das.index') }}" class="rounded-xl px-3 py-2 text-[13px] font-semibold text-teks-samar transition hover:bg-permukaan-sedang hover:text-teks-sedang">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <div id="hasil-das">
        @include('das._hasil')
    </div>

@endsection
