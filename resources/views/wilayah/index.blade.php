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

    <div class="mt-3.5 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
        @if ($wilayah->isEmpty())
            <x-kosong judul="Tidak ada wilayah sungai yang cocok" pesan="Coba ubah kata kunci pencarian."/>
        @else
            <div class="overflow-x-auto scrollbar-halus">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/70 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-3">Nama Wilayah Sungai</th>
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Kabupaten</th>
                            <th class="px-5 py-3 text-center">DAS</th>
                            <th class="px-5 py-3 text-center">Juru</th>
                            <th class="px-5 py-3 text-center">Laporan</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[13.5px]">
                        @foreach ($wilayah as $sungai)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emas-50">
                                            <svg class="h-4 w-4 text-emas-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M5 8l7-5 7 5M5 8v8l7 5 7-5V8"/>
                                            </svg>
                                        </span>
                                        <span class="font-semibold text-slate-800">{{ $sungai->nama_sungai }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 font-medium text-slate-500">{{ $sungai->kode_sungai }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-sungai-50 px-1.5 text-[12px] font-bold tabular-nums text-sungai-700">
                                        {{ $sungai->das_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-slate-100 px-1.5 text-[12px] font-bold tabular-nums text-slate-600">
                                        {{ $sungai->juru_sungai_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-slate-100 px-1.5 text-[12px] font-bold tabular-nums text-slate-600">
                                        {{ $sungai->laporan_op_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <x-lencana :warna="$sungai->aktif ? 'normal' : 'netral'">
                                        {{ $sungai->aktif ? 'Aktif' : 'Nonaktif' }}
                                    </x-lencana>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <a href="{{ route('wilayah.show', $sungai) }}"
                                       class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-sungai-600 transition hover:bg-sungai-50">
                                        Detail
                                    </a>
                                    @if (auth('staf')->user()?->isAdmin())
                                        <a href="{{ route('wilayah.edit', $sungai) }}"
                                           class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-slate-600 transition hover:bg-slate-100">
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('wilayah.destroy', $sungai) }}" class="inline"
                                              onsubmit="return confirm('Hapus {{ $sungai->nama_sungai }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-bata-600 transition hover:bg-bata-50">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($wilayah->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $wilayah->links() }}</div>
            @endif
        @endif
    </div>

@endsection
