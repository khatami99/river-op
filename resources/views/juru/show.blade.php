@extends('layouts.app')

@use('App\Support\Bulan')

@section('judul', $juru->nama)
@section('subjudul', $juru->das?->nama_das ?? 'DAS belum diatur')

@section('aksi-header')
    <a href="{{ route('juru.index') }}"
       class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-slate-100 px-3.5 py-2.5 text-[13px] font-bold text-slate-700 transition hover:bg-slate-200">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>
@endsection

@section('konten')

    <div class="grid gap-3.5 lg:grid-cols-3">

        {{-- Identitas --}}
        <div class="rounded-2xl bg-sungai-950 p-5">
            <div class="flex items-center gap-3.5">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emas-400 text-xl font-extrabold text-emas-900">
                    {{ strtoupper(substr($juru->nama, 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-lg font-extrabold text-white">{{ $juru->nama }}</p>
                    <x-lencana :warna="$juru->aktif ? 'normal' : 'netral'" class="mt-1">
                        {{ $juru->aktif ? 'Aktif' : 'Nonaktif' }}
                    </x-lencana>
                </div>
            </div>

            <dl class="mt-5 space-y-3 border-t border-white/10 pt-4">
                @php
                    $data = [
                        'Nomor Telepon' => $juru->no_telepon ?: '-',
                        'Nomor SK' => $juru->nomor_sk ?: '-',
                        'Username' => $juru->username,
                        'DAS' => $juru->das?->nama_das ?? '-',
                        'Wilayah Sungai' => $juru->das?->wilayahSungai?->nama_sungai ?? '-',
                        'Kabupaten' => $juru->das?->labelKabupaten() ?? '-',
                    ];
                @endphp
                @foreach ($data as $label => $nilai)
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-[12px] text-sungai-300/60">{{ $label }}</dt>
                        <dd class="truncate text-right text-[13px] font-semibold text-white">{{ $nilai }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- Kepatuhan pelaporan --}}
        <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200/70 lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-sungai-950">Kepatuhan Pelaporan</h2>
                    <p class="mt-0.5 text-[12px] text-slate-400">
                        {{ count($bulanTerlapor) }} dari 12 bulan sudah dilaporkan
                    </p>
                </div>
                <form method="GET">
                    <select name="tahun" onchange="this.form.submit()"
                            class="rounded-xl border-0 bg-slate-100 px-3 py-1.5 text-[13px] font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-sungai-500">
                        @foreach ($daftarTahun as $t)
                            <option value="{{ $t }}" @selected($t === $tahun)>{{ $t }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="mt-4 grid grid-cols-4 gap-2 sm:grid-cols-6">
                @foreach (Bulan::NAMA as $nomor => $nama)
                    @php $sudah = in_array($nomor, $bulanTerlapor, true); @endphp
                    <div class="flex flex-col items-center justify-center rounded-xl py-3 ring-1
                                {{ $sudah ? 'bg-hijau-50 ring-hijau-500/20' : 'bg-slate-50 ring-slate-200/70' }}">
                        <span class="text-[11px] font-bold uppercase tracking-wide {{ $sudah ? 'text-hijau-700' : 'text-slate-400' }}">
                            {{ Bulan::singkat($nomor) }}
                        </span>
                        @if ($sudah)
                            <svg class="mt-1 h-4 w-4 text-hijau-600" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <span class="mt-1 h-4 w-4 text-center text-slate-300">&mdash;</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Riwayat laporan --}}
    <div class="mt-3.5 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
        <div class="px-5 py-4">
            <h2 class="text-sm font-bold text-sungai-950">Riwayat Laporan</h2>
        </div>

        @if ($laporan->isEmpty())
            <x-kosong judul="Belum pernah mengirim laporan" pesan="Laporan dari aplikasi juru akan muncul di sini."/>
        @else
            <div class="overflow-x-auto scrollbar-halus">
                <table class="w-full">
                    <thead>
                        <tr class="border-t border-slate-100 bg-slate-50/70 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Periode</th>
                            <th class="px-5 py-3">DAS</th>
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
                                <td class="whitespace-nowrap px-5 py-3 text-slate-500">
                                    {{ Bulan::singkat($item->bulan) }} {{ $item->tahun }}
                                </td>
                                <td class="px-5 py-3 text-slate-500">{{ $item->das?->nama_das ?? '-' }}</td>
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
