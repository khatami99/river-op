@extends('layouts.app')

@use('App\Support\Bulan')

@section('judul', $das->nama_das)
@section('subjudul', $das->wilayahSungai?->nama_sungai ?? 'Wilayah sungai belum diatur')

@section('aksi-header')
    <a href="{{ route('das.index') }}"
       class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-slate-100 px-3.5 py-2.5 text-[13px] font-bold text-slate-700 transition hover:bg-slate-200">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>
@endsection

@section('konten')

    <div class="grid gap-3.5 lg:grid-cols-3">

        <div class="relative overflow-hidden rounded-2xl bg-sungai-950 p-5">
            <svg class="pointer-events-none absolute -bottom-6 -right-8 w-56 opacity-20" viewBox="0 0 400 120" fill="none">
                <path d="M0 40c40-24 80-24 120 0s80 24 120 0 80-24 120 0 80 24 120 0" stroke="#5db8f8" stroke-width="9" stroke-linecap="round"/>
                <path d="M0 76c40-24 80-24 120 0s80 24 120 0 80-24 120 0 80 24 120 0" stroke="#127cc4" stroke-width="9" stroke-linecap="round"/>
            </svg>

            <div class="relative">
                <p class="text-[11px] font-bold uppercase tracking-widest text-emas-400">Daerah Aliran Sungai</p>
                <p class="mt-1.5 text-lg font-extrabold text-white">{{ $das->nama_das }}</p>

                <dl class="mt-4 space-y-3 border-t border-white/10 pt-4">
                    @php
                        $info = [
                            'Kode DAS' => $das->kode_das,
                            'Wilayah Sungai' => $das->wilayahSungai?->nama_sungai ?? '-',
                            'Kabupaten' => $das->labelKabupaten(),
                            'Total Laporan' => $das->laporan_op_count,
                        ];
                    @endphp
                    @foreach ($info as $label => $nilai)
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-[12px] text-sungai-300/60">{{ $label }}</dt>
                            <dd class="truncate text-right text-[13px] font-semibold text-white">{{ $nilai }}</dd>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-[12px] text-sungai-300/60">Status</dt>
                        <dd>
                            <x-lencana :warna="$das->aktif ? 'normal' : 'netral'">
                                {{ $das->aktif ? 'Aktif' : 'Nonaktif' }}
                            </x-lencana>
                        </dd>
                    </div>
                </dl>

                @if ($das->deskripsi)
                    <p class="mt-4 border-t border-white/10 pt-4 text-[13px] leading-relaxed text-sungai-200/70">
                        {{ $das->deskripsi }}
                    </p>
                @endif
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70 lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-sungai-950">Juru Sungai Bertugas</h2>
                    <x-lencana warna="sungai">{{ $das->juruSungai->count() }} orang</x-lencana>
                </div>
                @if (auth('staf')->user()?->isAdmin())
                    <a href="{{ route('juru.create', ['das_id' => $das->id]) }}"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-1.5 text-[13px] font-bold text-slate-700 transition hover:bg-slate-200">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                        </svg>
                        Tambah Juru ke DAS Ini
                    </a>
                @endif
            </div>

            @if ($das->juruSungai->isEmpty())
                <x-kosong judul="Belum ada juru ditugaskan" pesan="DAS ini belum memiliki juru sungai."/>
            @else
                <ul class="divide-y divide-slate-100 border-t border-slate-100">
                    @foreach ($das->juruSungai as $orang)
                        <li>
                            <a href="{{ route('juru.show', $orang) }}" class="flex items-center gap-3 px-5 py-3 transition hover:bg-slate-50">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sungai-50 text-[12px] font-extrabold text-sungai-700">
                                    {{ strtoupper(substr($orang->nama, 0, 1)) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[13.5px] font-semibold text-slate-800">{{ $orang->nama }}</p>
                                    <p class="truncate text-[12px] text-slate-400">{{ $orang->nip ?: $orang->username }}</p>
                                </div>
                                <x-lencana :warna="$orang->aktif ? 'normal' : 'netral'">
                                    {{ $orang->aktif ? 'Aktif' : 'Nonaktif' }}
                                </x-lencana>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="mt-3.5 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
        <div class="px-5 py-4">
            <h2 class="text-sm font-bold text-sungai-950">Laporan di DAS Ini</h2>
        </div>

        @if ($laporan->isEmpty())
            <x-kosong judul="Belum ada laporan" pesan="Laporan untuk DAS ini akan muncul di sini."/>
        @else
            <div class="overflow-x-auto scrollbar-halus">
                <table class="w-full">
                    <thead>
                        <tr class="border-t border-slate-100 bg-slate-50/70 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Juru Sungai</th>
                            <th class="px-5 py-3">Periode</th>
                            <th class="px-5 py-3 text-center">Bangunan</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[13.5px]">
                        @foreach ($laporan as $item)
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3 font-semibold tabular-nums text-slate-700">
                                    {{ optional($item->tanggal_inspeksi)->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-3 font-semibold text-slate-800">{{ $item->juruSungai?->nama ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-slate-500">
                                    {{ Bulan::singkat($item->bulan) }} {{ $item->tahun }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-slate-100 px-1.5 text-[12px] font-bold tabular-nums text-slate-600">
                                        {{ $item->item_inspeksi_count }}
                                    </span>
                                </td>
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
