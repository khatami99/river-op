@extends('layouts.app')

@section('judul', 'Juru Sungai')
@section('subjudul')
    <span id="jumlah-hasil">{{ $juru->total() }}{{ ' juru terdaftar' }}</span>
@endsection

@section('aksi-header')
    @if (auth('staf')->user()?->isAdmin())
        <a href="{{ route('juru.create') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-aksi px-4 py-2.5 text-[13px] font-bold text-aksi-teks shadow-lg shadow-aksi/25 transition hover:bg-aksi-sorot">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
            </svg>
            Tambah Juru
        </a>
    @endif
@endsection

@php
    $kelasInput = 'w-full rounded-xl border-0 bg-permukaan-lembut px-3 py-2 text-[13px] font-medium text-teks ring-1 ring-inset ring-garis transition focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
    $adaFilter = collect($filter)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
@endphp

@section('konten')

    @error('hapus')
        <div class="mb-3.5 rounded-2xl bg-bahaya-latar px-4 py-3 text-[13px] font-medium text-bahaya-teks ring-1 ring-bahaya-teks/25">
            {{ $message }}
        </div>
    @enderror

    <form method="GET" data-saring data-hasil="hasil-juru"
          class="rounded-2xl bg-permukaan p-4 ring-1 ring-garis/70">
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-[12px] font-bold uppercase tracking-wider text-teks-lembut">Pencarian</label>
                <input type="text" name="cari" value="{{ $filter['cari'] }}" placeholder="Nama, telepon, atau username" class="{{ $kelasInput }}">
            </div>
            <div>
                <label class="mb-1 block text-[12px] font-bold uppercase tracking-wider text-teks-lembut">DAS</label>
                <select name="das_id" class="{{ $kelasInput }}">
                    <option value="">Semua DAS</option>
                    @foreach ($daftarDas as $w)
                        <option value="{{ $w->id }}" @selected((string) $filter['das_id'] === (string) $w->id)>{{ $w->namaRingkas() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3.5 flex items-center gap-2">
            <button type="submit" class="rounded-xl bg-cari px-4 py-2 text-[13px] font-bold text-cari-teks transition hover:bg-cari-sorot">
                Terapkan Filter
            </button>
            @if ($adaFilter)
                <a href="{{ route('juru.index') }}" class="rounded-xl px-3 py-2 text-[13px] font-semibold text-teks-samar transition hover:bg-permukaan-sedang hover:text-teks-sedang">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <div id="hasil-juru">
        @include('juru._hasil')
    </div>

@endsection
