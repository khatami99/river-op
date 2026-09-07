@extends('layouts.app')

@section('judul', 'Wilayah Sungai')
@section('subjudul', $wilayah->total() . ' wilayah sungai terdaftar')

@section('aksi-header')
    @if (auth('staf')->user()?->isAdmin())
        <a href="{{ route('wilayah.create') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-sungai-950 px-4 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-sungai-950/15 transition hover:bg-sungai-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
            </svg>
            Tambah Wilayah
        </a>
    @endif
@endsection

@php
    $kelasInput = 'w-full rounded-xl border-0 bg-slate-50 px-3 py-2 text-[13px] font-medium text-slate-800 ring-1 ring-inset ring-slate-200 transition focus:bg-white focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
@endphp

@push('kepala')
    @vite('resources/js/peta.js')
@endpush

@section('konten')

    @error('hapus')
        <div class="mb-3.5 rounded-2xl bg-bata-50 px-4 py-3 text-[13px] font-medium text-bata-700 ring-1 ring-bata-500/20">
            {{ $message }}
        </div>
    @enderror

    {{-- Catatan ini menjawab kebingungan yang wajar: halaman master data yang
         isinya cuma satu baris terlihat seperti data yang belum lengkap.

         Ditampilkan hanya selagi memang satu, sehingga ia pensiun sendiri
         begitu wilayah sungai kedua ditambahkan — bukan berubah jadi
         keterangan yang keliru. --}}
    @if ($wilayah->total() === 1 && ! request()->filled('cari'))
        <div class="mb-3.5 flex items-start gap-3 rounded-2xl bg-teal-50 p-4 ring-1 ring-teal-600/15">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-teal-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-[13px] leading-relaxed text-teal-900">
                Baru satu wilayah sungai yang menjadi wewenang Provinsi Kalimantan Selatan.
                Pembagian tugas juru sungai dilakukan per <a href="{{ route('das.index') }}" class="font-bold underline">DAS</a> di bawahnya.
            </p>
        </div>
    @endif

    <form method="GET" class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/70">
        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Pencarian</label>
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" name="cari" value="{{ $filter['cari'] }}" placeholder="Nama atau kode wilayah sungai"
                   class="{{ $kelasInput }} max-w-md flex-1">
            <button type="submit" class="rounded-xl bg-sungai-600 px-4 py-2 text-[13px] font-bold text-white transition hover:bg-sungai-700">
                Cari
            </button>
            @if ($filter['cari'])
                <a href="{{ route('wilayah.index') }}" class="rounded-xl px-3 py-2 text-[13px] font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                    Reset
                </a>
            @endif
        </div>
    </form>

    {{-- Satu kartu, satu peta, per wilayah sungai.

         Bentuk ini dipilih supaya halaman tidak perlu dirombak ketika wilayah
         sungai kedua muncul: yang baru langsung mendapat kartu dan petanya
         sendiri. Peta gabungan seluruh wilayah sengaja tidak dipakai, sebab
         hasilnya akan sama persis dengan peta pada halaman DAS. --}}
    @if ($wilayah->isEmpty())
        <div class="mt-3.5 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
            <x-kosong judul="Tidak ada wilayah sungai yang cocok" pesan="Coba ubah kata kunci pencarian."/>
        </div>
    @else
        <div class="mt-3.5 space-y-3.5">
            @foreach ($wilayah as $sungai)
                @php $titik = $titikPerWilayah[$sungai->id] ?? collect(); @endphp

                <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
                    <div class="grid lg:grid-cols-5">

                        <div class="lg:col-span-2">
                            @if ($titik->isNotEmpty())
                                <div class="peta-wilayah relative h-[15rem] w-full lg:h-full lg:min-h-[16rem]"
                                     data-titik="{{ $titik->toJson() }}"></div>
                            @else
                                <div class="flex h-[15rem] items-center justify-center bg-slate-50 px-6 text-center lg:h-full">
                                    <p class="text-[13px] leading-relaxed text-slate-400">
                                        Belum ada DAS bertitik di wilayah ini,<br>jadi petanya belum bisa digambar.
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="p-5 lg:col-span-3">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-bold uppercase tracking-widest text-emas-600">{{ $sungai->kode_sungai }}</p>
                                    <h2 class="mt-0.5 text-base font-extrabold text-sungai-950">{{ $sungai->nama_sungai }}</h2>
                                </div>
                                <x-lencana :warna="$sungai->aktif ? 'hijau' : 'abu'">
                                    {{ $sungai->aktif ? 'Aktif' : 'Nonaktif' }}
                                </x-lencana>
                            </div>

                            @if ($sungai->deskripsi)
                                <p class="mt-2 text-[13px] leading-relaxed text-slate-500">{{ $sungai->deskripsi }}</p>
                            @endif

                            <dl class="mt-4 grid grid-cols-3 divide-x divide-slate-100 rounded-xl bg-slate-50/70 py-3">
                                @foreach ([
                                    ['DAS', $sungai->das_count],
                                    ['Juru Sungai', $sungai->juru_sungai_count],
                                    ['Laporan', $sungai->laporan_op_count],
                                ] as [$label, $nilai])
                                    <div class="px-3 text-center">
                                        <dd class="text-lg font-extrabold tabular-nums text-sungai-950">{{ $nilai }}</dd>
                                        <dt class="text-[11px] font-medium text-slate-400">{{ $label }}</dt>
                                    </div>
                                @endforeach
                            </dl>

                            @if ($titik->isNotEmpty() && $titik->count() < $sungai->das_count)
                                <p class="mt-2 text-[12px] text-slate-400">
                                    {{ $titik->count() }} dari {{ $sungai->das_count }} DAS punya titik dan muncul di peta.
                                </p>
                            @endif

                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <a href="{{ route('wilayah.show', $sungai) }}"
                                   class="rounded-xl bg-slate-100 px-3.5 py-2 text-[13px] font-bold text-slate-700 transition hover:bg-slate-200">
                                    Detail
                                </a>
                                @if (auth('staf')->user()?->isAdmin())
                                    <a href="{{ route('wilayah.edit', $sungai) }}"
                                       class="rounded-xl px-3 py-2 text-[13px] font-semibold text-sungai-700 transition hover:bg-sungai-50">
                                        Ubah
                                    </a>
                                    <form method="POST" action="{{ route('wilayah.destroy', $sungai) }}"
                                          onsubmit="return confirm('Hapus wilayah sungai ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-xl px-3 py-2 text-[13px] font-semibold text-bata-600 transition hover:bg-bata-50">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($wilayah->hasPages())
            <div class="mt-3.5 rounded-2xl bg-white px-5 py-3.5 ring-1 ring-slate-200/70">{{ $wilayah->links() }}</div>
        @endif
    @endif

@endsection
