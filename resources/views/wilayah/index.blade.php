@extends('layouts.app')

@section('judul', 'Wilayah Sungai')
@section('subjudul')
    <span id="jumlah-hasil">{{ $wilayah->total() }}{{ ' wilayah sungai terdaftar' }}</span>
@endsection

@php
    $kelasInput = 'w-full rounded-xl border-0 bg-permukaan-lembut px-3 py-2 text-[13px] font-medium text-teks ring-1 ring-inset ring-garis transition focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
@endphp

@push('kepala')
    @vite('resources/js/peta.js')
@endpush

@section('konten')

    <x-aksi-halaman>
    @if (auth('staf')->user()?->isAdmin())
        <a href="{{ route('wilayah.create') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-aksi px-4 py-2.5 text-[13px] font-bold text-aksi-teks shadow-lg shadow-aksi/25 transition hover:bg-aksi-sorot">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
            </svg>
            Tambah Wilayah
        </a>
    @endif
    </x-aksi-halaman>

    @error('hapus')
        <div class="mb-3.5 rounded-2xl bg-bahaya-latar px-4 py-3 text-[13px] font-medium text-bahaya-teks ring-1 ring-bahaya-teks/25">
            {{ $message }}
        </div>
    @enderror

    {{-- Peta, disusun sama seperti halaman DAS: peta di kiri, panel keterangan
         di kanan, daftar di bawahnya.

         Penandanya satu per WILAYAH SUNGAI, bukan per DAS. Wilayah sungai
         sendiri tidak punya koordinat — ia wilayah, bukan titik — sehingga
         penandanya diletakkan pada rata-rata titik DAS yang bernaung di
         bawahnya. Angka pada penanda menunjukkan jumlah DAS yang dinaunginya. --}}
    @if ($dataPeta->isNotEmpty())
        <div class="mb-3.5 grid gap-3.5 lg:grid-cols-3">
            <div class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70 lg:col-span-2">
                <div id="peta-ws" class="relative h-[clamp(18rem,42vh,26rem)] w-full"
                     data-ws="{{ $dataPeta->toJson() }}"></div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
                <div id="panel-ws-kosong" class="flex h-full flex-col items-center justify-center px-6 py-12 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-peringatan-latar">
                        <svg class="h-6 w-6 text-peringatan-teks" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M5 8l7-5 7 5M5 8v8l7 5 7-5V8"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-teks-sedang">Pilih sebuah wilayah sungai</p>
                    <p class="mt-1 max-w-[15rem] text-[13px] leading-relaxed text-teks-samar">
                        Klik penanda di peta untuk melihat ringkasan wilayah sungai tersebut.
                    </p>
                </div>
                <div id="panel-ws" class="hidden"></div>
            </div>
        </div>
    @endif

    <form method="GET" data-saring data-hasil="hasil-wilayah"
          class="rounded-2xl bg-permukaan p-4 ring-1 ring-garis/70">
        <label class="mb-1 block text-[12px] font-bold uppercase tracking-wider text-teks-lembut">Pencarian</label>
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" name="cari" value="{{ $filter['cari'] }}" placeholder="Nama atau kode wilayah sungai"
                   class="{{ $kelasInput }} max-w-md flex-1">
            <button type="submit" class="rounded-xl bg-cari px-4 py-2 text-[13px] font-bold text-cari-teks transition hover:bg-cari-sorot">
                Cari
            </button>
            @if ($filter['cari'])
                <a href="{{ route('wilayah.index') }}" class="rounded-xl px-3 py-2 text-[13px] font-semibold text-teks-samar transition hover:bg-permukaan-sedang hover:text-teks-sedang">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <div id="hasil-wilayah">
        @include('wilayah._hasil')
    </div>

@endsection
