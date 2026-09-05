@extends('layouts.app')

@use('App\Support\Bulan')

@section('judul', 'Laporan OP')
@section('subjudul', $laporan->total() . ' laporan ditemukan')

@section('aksi-header')
    <a href="{{ route('laporan.export', request()->query()) }}"
       class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-sungai-950 px-4 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-sungai-950/15 transition hover:bg-sungai-900">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
        Export CSV
    </a>
@endsection

@php
    $kelasInput = 'w-full rounded-xl border-0 bg-slate-50 px-3 py-2 text-[13px] font-medium text-slate-800 ring-1 ring-inset ring-slate-200 transition focus:bg-white focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
    $adaFilter = collect($filter)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
@endphp

@section('konten')

    {{-- Filter --}}
    <form method="GET" class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/70">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Bulan</label>
                <select name="bulan" class="{{ $kelasInput }}">
                    <option value="">Semua bulan</option>
                    @foreach (Bulan::NAMA as $nomor => $nama)
                        <option value="{{ $nomor }}" @selected((string) $filter['bulan'] === (string) $nomor)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Tahun</label>
                <select name="tahun" class="{{ $kelasInput }}">
                    <option value="">Semua tahun</option>
                    @foreach ($daftarTahun as $t)
                        <option value="{{ $t }}" @selected((string) $filter['tahun'] === (string) $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Wilayah Sungai</label>
                <select name="wilayah_sungai_id" class="{{ $kelasInput }}">
                    <option value="">Semua sungai</option>
                    @foreach ($daftarWilayah as $w)
                        <option value="{{ $w->id }}" @selected((string) $filter['wilayah_sungai_id'] === (string) $w->id)>{{ $w->nama_sungai }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Juru Sungai</label>
                <select name="juru_sungai_id" class="{{ $kelasInput }}">
                    <option value="">Semua juru</option>
                    @foreach ($daftarJuru as $j)
                        <option value="{{ $j->id }}" @selected((string) $filter['juru_sungai_id'] === (string) $j->id)>{{ $j->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Pencarian</label>
                <input type="text" name="cari" value="{{ $filter['cari'] }}" placeholder="Nama juru / sungai" class="{{ $kelasInput }}">
            </div>
        </div>

        <div class="mt-3.5 flex items-center gap-2">
            <button type="submit"
                    class="rounded-xl bg-sungai-600 px-4 py-2 text-[13px] font-bold text-white transition hover:bg-sungai-700">
                Terapkan Filter
            </button>
            @if ($adaFilter)
                <a href="{{ route('laporan.index') }}"
                   class="rounded-xl px-3 py-2 text-[13px] font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                    Reset
                </a>
            @endif
        </div>
    </form>

    {{-- Tabel --}}
    <div class="mt-3.5 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
        @if ($laporan->isEmpty())
            <x-kosong judul="Tidak ada laporan yang cocok" pesan="Coba ubah atau reset filter di atas."/>
        @else
            <div class="overflow-x-auto scrollbar-halus">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/70 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Juru Sungai</th>
                            <th class="px-5 py-3">Wilayah Sungai</th>
                            <th class="px-5 py-3">Periode</th>
                            <th class="px-5 py-3 text-center">Bangunan</th>
                            <th class="px-5 py-3">Cuaca</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[13.5px]">
                        @foreach ($laporan as $item)
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3 font-semibold tabular-nums text-slate-700">
                                    {{ optional($item->tanggal_inspeksi)->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sungai-50 text-[11px] font-extrabold text-sungai-700">
                                            {{ strtoupper(substr($item->juruSungai?->nama ?? '-', 0, 1)) }}
                                        </span>
                                        <span class="font-semibold text-slate-800">{{ $item->juruSungai?->nama ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-slate-500">{{ $item->wilayahSungai?->nama_sungai ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-slate-500">
                                    {{ Bulan::singkat($item->bulan) }} {{ $item->tahun }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-slate-100 px-1.5 text-[12px] font-bold tabular-nums text-slate-600">
                                        {{ $item->item_inspeksi_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 capitalize text-slate-500">{{ $item->cuaca ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <a href="{{ route('laporan.show', $item) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-sungai-600 transition hover:bg-sungai-50">
                                        Detail
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($laporan->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $laporan->links() }}</div>
            @endif
        @endif
    </div>

@endsection
