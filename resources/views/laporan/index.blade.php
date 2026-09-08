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


@php
    $kelasIsian = 'rounded-xl border-0 bg-permukaan-lembut px-3 py-2 text-[13px] font-medium text-teks ring-1 ring-inset ring-garis transition focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
    $adaFilter = collect($filter)->filter(fn ($v) => $v !== null && $v !== '' && $v !== false && $v !== [])->isNotEmpty();

    $kelasInput = 'w-full ' . $kelasIsian;
@endphp

@section('konten')

    {{-- Penyaring.

         Tidak ada tombol "terapkan": hasil di bawah berubah begitu penyaringnya
         diubah, dan begitu pengguna berhenti mengetik. Bentuknya tetap formulir
         sungguhan supaya tetap bekerja bila JavaScript gagal dimuat. --}}
    <form method="GET" id="saringan-laporan" class="rounded-2xl bg-permukaan p-4 ring-1 ring-garis/70">
        {{-- Periode berdiri di barisnya sendiri.

             Ia satu-satunya isian yang butuh empat pilihan, dan ketika ditaruh
             sebaris dengan yang lain, bagian "s/d" menjuntai ke bawah sehingga
             labelnya tidak lagi sejajar dengan kolom lain. Dipisah dengan
             garis, keduanya sama-sama rapi. --}}
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
            <label class="w-full text-[12px] font-bold uppercase tracking-wider text-teks-lembut sm:w-auto sm:pr-1">
                Periode
            </label>

            <select name="bulan" class="{{ $kelasIsian }} min-w-[9.5rem] flex-1 sm:max-w-[12rem]">
                <option value="">Semua bulan</option>
                @foreach (Bulan::NAMA as $nomor => $nama)
                    <option value="{{ $nomor }}" @selected((string) $filter['bulan'] === (string) $nomor)>{{ $nama }}</option>
                @endforeach
            </select>

            <select name="tahun" class="{{ $kelasIsian }} min-w-[7rem] flex-1 sm:max-w-[9rem]">
                <option value="">Semua tahun</option>
                @foreach ($daftarTahun as $t)
                    <option value="{{ $t }}" @selected((string) $filter['tahun'] === (string) $t)>{{ $t }}</option>
                @endforeach
            </select>

            <span class="px-1 text-[12px] font-semibold text-teks-samar">s/d</span>

            <select name="bulan_sampai" class="{{ $kelasIsian }} min-w-[9.5rem] flex-1 sm:max-w-[12rem]">
                <option value="">&mdash; satu bulan saja</option>
                @foreach (Bulan::NAMA as $nomor => $nama)
                    <option value="{{ $nomor }}" @selected((string) $filter['bulan_sampai'] === (string) $nomor)>{{ $nama }}</option>
                @endforeach
            </select>

            <select name="tahun_sampai" class="{{ $kelasIsian }} min-w-[7rem] flex-1 sm:max-w-[9rem]">
                <option value="">Tahun sama</option>
                @foreach ($daftarTahun as $t)
                    <option value="{{ $t }}" @selected((string) $filter['tahun_sampai'] === (string) $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>

        <div class="mt-3.5 grid items-start gap-3 border-t border-garis-halus pt-3.5 sm:grid-cols-2 lg:grid-cols-3">

            <div>
                <label class="mb-1 block text-[12px] font-bold uppercase tracking-wider text-teks-lembut">Juru Sungai</label>

                {{-- Boleh dipilih lebih dari satu.

                     Daftarnya mengambang di atas isi halaman, bukan melebar ke
                     dalam alurnya, supaya perilakunya sama dengan dropdown DAS
                     dan Jenis Bangunan di sebelahnya. Panel yang melebar akan
                     mendorong seluruh isi di bawahnya turun setiap kali dibuka. --}}
                <details class="relative" data-panel-pilihan
                         @if (count($filter['juru_sungai_id'])) open @endif>
                    <summary class="{{ $kelasInput }} flex cursor-pointer select-none items-center justify-between">
                        @if (count($filter['juru_sungai_id']))
                            <span class="text-teks">{{ count($filter['juru_sungai_id']) }} juru dipilih</span>
                        @else
                            <span class="text-teks-samar">Semua juru</span>
                        @endif
                        <svg class="h-4 w-4 shrink-0 text-teks-samar" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                        </svg>
                    </summary>

                    <div class="scrollbar-halus absolute left-0 right-0 z-30 mt-1 max-h-56 overflow-y-auto rounded-xl bg-permukaan px-3 py-2 shadow-lg shadow-sungai-950/10 ring-1 ring-garis">
                        @foreach ($daftarJuru as $j)
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg px-1 py-1.5 text-[13px] text-teks-sedang hover:bg-permukaan-lembut">
                                <input type="checkbox" name="juru_sungai_id[]" value="{{ $j->id }}"
                                       @checked(in_array((string) $j->id, array_map('strval', $filter['juru_sungai_id']), true))
                                       class="h-4 w-4 rounded border-garis-kuat text-info-teks focus:ring-sungai-500/30">
                                {{ $j->nama }}
                            </label>
                        @endforeach
                    </div>
                </details>
            </div>

            <div>
                <label class="mb-1 block text-[12px] font-bold uppercase tracking-wider text-teks-lembut">DAS</label>
                <select name="das_id" class="{{ $kelasInput }}">
                    <option value="">Semua DAS</option>
                    @foreach ($daftarDas as $w)
                        <option value="{{ $w->id }}" @selected((string) $filter['das_id'] === (string) $w->id)>{{ $w->nama_das }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-[12px] font-bold uppercase tracking-wider text-teks-lembut">Jenis Bangunan</label>
                <select name="jenis_bangunan_id" class="{{ $kelasInput }}">
                    <option value="">Semua jenis</option>
                    @foreach ($daftarJenis as $jb)
                        <option value="{{ $jb->id }}" @selected((string) $filter['jenis_bangunan_id'] === (string) $jb->id)>{{ $jb->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Pencarian bebas berdiri paling bawah karena cakupannya paling luas:
             ia menelusuri seluruh isi laporan, bukan satu kolom tertentu. --}}
        <div class="mt-3.5 border-t border-garis-halus pt-3.5">
            <label class="mb-1 block text-[12px] font-bold uppercase tracking-wider text-teks-lembut">
                Pencarian
                <span class="ml-1 font-medium normal-case tracking-normal text-teks-samar">
                    &mdash; juru, DAS, wilayah sungai, cuaca, debit air, bulan, tahun, jumlah bangunan, catatan
                </span>
            </label>
            <input type="search" name="cari" value="{{ $filter['cari'] }}" autocomplete="off"
                   placeholder="Ketik apa saja&hellip;" class="{{ $kelasInput }}">
        </div>

        <input type="hidden" name="tampilan" id="tampilan-aktif" value="{{ $tampilan }}">

        <div class="mt-3.5 flex flex-wrap items-center justify-between gap-3 border-t border-garis-halus pt-3.5">
            <label class="flex cursor-pointer items-center gap-2.5 text-[13px] text-teks-sedang">
                <input type="checkbox" name="bermasalah" value="1" @checked($filter['bermasalah'])
                       class="h-4 w-4 rounded border-garis-kuat text-bahaya-teks focus:ring-bata-500/30">
                Hanya yang punya temuan bermasalah
            </label>

            <div class="flex items-center gap-2">
                {{-- Tanpa JavaScript, tombol ini yang menerapkan penyaring. --}}
                <noscript>
                    <button type="submit" class="rounded-xl bg-cari px-4 py-2 text-[13px] font-bold text-cari-teks">
                        Terapkan Filter
                    </button>
                </noscript>
                @if ($adaFilter)
                    <a href="{{ route('laporan.index', ['tampilan' => $tampilan]) }}"
                       class="rounded-xl px-3 py-2 text-[13px] font-semibold text-teks-samar transition hover:bg-permukaan-sedang hover:text-teks-sedang">
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Baris kendali hasil, tepat di antara penyaring dan hasilnya.

         Tombol export ditaruh di sini, bukan di kepala halaman, karena yang
         terunduh selalu mengikuti penyaring di atasnya. Menaruhnya berdekatan
         membuat hubungan itu terbaca tanpa perlu dijelaskan.

         Pemilih Tabel/Peta juga bukan penyaring: ia mengubah BENTUK, bukan
         data mana yang muncul. --}}
    <div class="mt-3.5 flex flex-wrap items-center justify-between gap-3">
        <div id="pemilih-tampilan" class="flex w-fit items-center gap-1 rounded-xl bg-permukaan-sedang p-1">
            @foreach (['tabel' => 'Tabel', 'peta' => 'Peta'] as $nilai => $label)
                <button type="button" data-tampilan="{{ $nilai }}"
                        class="rounded-lg px-4 py-1.5 text-[13px] font-bold transition {{ $tampilan === $nilai ? 'bg-permukaan text-judul shadow-sm' : 'text-teks-samar hover:text-teks-sedang' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <a href="{{ route('laporan.export', request()->query()) }}" data-unduh id="tautan-export"
           class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-aksi px-4 py-2.5 text-[13px] font-bold text-aksi-teks shadow-lg shadow-aksi/25 transition hover:bg-aksi-sorot">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            Export Excel <span class="font-medium opacity-70">sesuai filter</span>
        </a>
    </div>

    <div id="hasil-laporan">
        @include('laporan._hasil')
    </div>

@endsection
