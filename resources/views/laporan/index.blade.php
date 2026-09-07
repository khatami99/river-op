@extends('layouts.app')

@use('App\Support\Bulan')

@section('judul', 'Laporan OP')
@section('subjudul')
    <span id="jumlah-hasil">{{ $tampilan === 'peta'
        ? $dataTitik->count() . ' bangunan bertitik, ' . $jumlahBermasalah . ' di antaranya bermasalah'
        : $laporan->total() . ' laporan ditemukan' }}</span>
@endsection

@push('kepala')
    @vite('resources/js/peta.js')
@endpush

@section('aksi-header')
    <a href="{{ route('laporan.export', request()->query()) }}" data-unduh id="tautan-export"
       class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-sungai-950 px-4 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-sungai-950/15 transition hover:bg-sungai-900">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
        Export Excel
    </a>
@endsection

@php
    $kelasInput = 'w-full rounded-xl border-0 bg-slate-50 px-3 py-2 text-[13px] font-medium text-slate-800 ring-1 ring-inset ring-slate-200 transition focus:bg-white focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
    $adaFilter = collect($filter)->filter(fn ($v) => $v !== null && $v !== '' && $v !== false && $v !== [])->isNotEmpty();
@endphp

@section('konten')

    {{-- Penyaring.

         Tidak ada tombol "terapkan": hasil di bawah berubah begitu penyaringnya
         diubah, dan begitu pengguna berhenti mengetik. Bentuknya tetap formulir
         sungguhan supaya tetap bekerja bila JavaScript gagal dimuat. --}}
    <form method="GET" id="saringan-laporan" class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/70">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Periode. Bagian "sampai" boleh dikosongkan; bila diisi, yang
                 dicari adalah rentangnya, misalnya Juli sampai September. --}}
            <div class="sm:col-span-2">
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Periode</label>
                <div class="flex items-center gap-2">
                    <select name="bulan" class="{{ $kelasInput }}">
                        <option value="">Semua bulan</option>
                        @foreach (Bulan::NAMA as $nomor => $nama)
                            <option value="{{ $nomor }}" @selected((string) $filter['bulan'] === (string) $nomor)>{{ $nama }}</option>
                        @endforeach
                    </select>
                    <select name="tahun" class="{{ $kelasInput }} max-w-[7.5rem]">
                        <option value="">Semua tahun</option>
                        @foreach ($daftarTahun as $t)
                            <option value="{{ $t }}" @selected((string) $filter['tahun'] === (string) $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-2 flex items-center gap-2">
                    <span class="shrink-0 text-[12px] font-semibold text-slate-400">s/d</span>
                    <select name="bulan_sampai" class="{{ $kelasInput }}">
                        <option value="">&mdash; satu bulan saja</option>
                        @foreach (Bulan::NAMA as $nomor => $nama)
                            <option value="{{ $nomor }}" @selected((string) $filter['bulan_sampai'] === (string) $nomor)>{{ $nama }}</option>
                        @endforeach
                    </select>
                    <select name="tahun_sampai" class="{{ $kelasInput }} max-w-[7.5rem]">
                        <option value="">Tahun sama</option>
                        @foreach ($daftarTahun as $t)
                            <option value="{{ $t }}" @selected((string) $filter['tahun_sampai'] === (string) $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">DAS</label>
                <select name="das_id" class="{{ $kelasInput }}">
                    <option value="">Semua DAS</option>
                    @foreach ($daftarDas as $w)
                        <option value="{{ $w->id }}" @selected((string) $filter['das_id'] === (string) $w->id)>{{ $w->nama_das }}</option>
                    @endforeach
                </select>

                <label class="mb-1 mt-2.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Jenis Bangunan</label>
                <select name="jenis_bangunan_id" class="{{ $kelasInput }}">
                    <option value="">Semua jenis</option>
                    @foreach ($daftarJenis as $jb)
                        <option value="{{ $jb->id }}" @selected((string) $filter['jenis_bangunan_id'] === (string) $jb->id)>{{ $jb->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Pencarian</label>
                <input type="search" name="cari" value="{{ $filter['cari'] }}" autocomplete="off"
                       placeholder="Nama juru / sungai" class="{{ $kelasInput }}">

                {{-- Juru boleh dipilih lebih dari satu. Ditaruh di panel yang
                     bisa dilipat karena daftarnya memanjang seiring
                     bertambahnya juru, dan sebagian besar waktu tidak dipakai. --}}
                <details class="mt-2.5 rounded-xl bg-slate-50 ring-1 ring-inset ring-slate-200"
                         @if (count($filter['juru_sungai_id'])) open @endif>
                    <summary class="cursor-pointer select-none px-3 py-2 text-[13px] font-medium text-slate-700">
                        Juru Sungai
                        @if (count($filter['juru_sungai_id']))
                            <span class="ml-1 rounded-md bg-sungai-100 px-1.5 py-0.5 text-[11px] font-bold text-sungai-700">
                                {{ count($filter['juru_sungai_id']) }} dipilih
                            </span>
                        @else
                            <span class="ml-1 text-[12px] text-slate-400">semua</span>
                        @endif
                    </summary>
                    <div class="scrollbar-halus max-h-40 overflow-y-auto border-t border-slate-200 px-3 py-2">
                        @foreach ($daftarJuru as $j)
                            <label class="flex cursor-pointer items-center gap-2 py-1 text-[13px] text-slate-700">
                                <input type="checkbox" name="juru_sungai_id[]" value="{{ $j->id }}"
                                       @checked(in_array((string) $j->id, array_map('strval', $filter['juru_sungai_id']), true))
                                       class="h-4 w-4 rounded border-slate-300 text-sungai-600 focus:ring-sungai-500/30">
                                {{ $j->nama }}
                            </label>
                        @endforeach
                    </div>
                </details>
            </div>
        </div>

        <input type="hidden" name="tampilan" id="tampilan-aktif" value="{{ $tampilan }}">

        <div class="mt-3.5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3.5">
            <label class="flex cursor-pointer items-center gap-2.5 text-[13px] text-slate-700">
                <input type="checkbox" name="bermasalah" value="1" @checked($filter['bermasalah'])
                       class="h-4 w-4 rounded border-slate-300 text-bata-600 focus:ring-bata-500/30">
                Hanya yang punya temuan bermasalah
            </label>

            <div class="flex items-center gap-2">
                {{-- Tanpa JavaScript, tombol ini yang menerapkan penyaring. --}}
                <noscript>
                    <button type="submit" class="rounded-xl bg-sungai-600 px-4 py-2 text-[13px] font-bold text-white">
                        Terapkan Filter
                    </button>
                </noscript>
                @if ($adaFilter)
                    <a href="{{ route('laporan.index', ['tampilan' => $tampilan]) }}"
                       class="rounded-xl px-3 py-2 text-[13px] font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Pemilih bentuk tampilan, tepat di atas yang dikendalikannya.
         Ini bukan penyaring: ia mengubah BENTUK, bukan data mana yang muncul. --}}
    <div id="pemilih-tampilan" class="mt-3.5 flex w-fit items-center gap-1 rounded-xl bg-slate-100 p-1">
        @foreach (['tabel' => 'Tabel', 'peta' => 'Peta'] as $nilai => $label)
            <button type="button" data-tampilan="{{ $nilai }}"
                    class="rounded-lg px-4 py-1.5 text-[13px] font-bold transition {{ $tampilan === $nilai ? 'bg-white text-sungai-950 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div id="hasil-laporan">
        @include('laporan._hasil')
    </div>

@endsection
