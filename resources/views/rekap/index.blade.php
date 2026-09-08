@extends('layouts.app')

@use('App\Support\Bulan')

@section('judul', 'Rekapitulasi')
@section('subjudul', 'Rekap pelaporan ' . Bulan::nama($bulan) . ' ' . $tahun)

@section('aksi-header')
    <a href="{{ route('rekap.export', ['bulan' => $bulan, 'tahun' => $tahun]) }}" data-unduh
       class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-sungai-950 px-4 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-sungai-950/15 transition hover:bg-sungai-900">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
        Export Rekap
    </a>
@endsection

@section('konten')

    <x-pemilih-periode :bulan="$bulan" :tahun="$tahun" :daftar-tahun="$daftarTahun"/>

    @php $persen = $total['juru'] > 0 ? (int) round($total['sudah'] / $total['juru'] * 100) : 0; @endphp

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 gap-3.5 xl:grid-cols-4">
        <div class="relative col-span-2 overflow-hidden rounded-2xl bg-sungai-950 p-5">
            <svg class="pointer-events-none absolute -bottom-8 -right-10 w-64 opacity-20" viewBox="0 0 400 120" fill="none">
                <path d="M0 40c40-24 80-24 120 0s80 24 120 0 80-24 120 0 80 24 120 0" stroke="#5db8f8" stroke-width="9" stroke-linecap="round"/>
                <path d="M0 76c40-24 80-24 120 0s80 24 120 0 80-24 120 0 80 24 120 0" stroke="#127cc4" stroke-width="9" stroke-linecap="round"/>
            </svg>
            <div class="relative">
                <p class="text-[11px] font-bold uppercase tracking-widest text-emas-400">Kepatuhan Pelaporan</p>
                <p class="mt-1 text-lg font-bold text-white">{{ Bulan::nama($bulan) }} {{ $tahun }}</p>
                <div class="mt-4 flex items-end gap-3">
                    <span class="text-[40px] font-extrabold leading-none tabular-nums text-white">{{ $persen }}<span class="text-lg">%</span></span>
                    <span class="pb-1 text-[13px] text-sungai-200/70">{{ $total['sudah'] }} dari {{ $total['juru'] }} juru</span>
                </div>
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-white/10">
                    <div class="h-full rounded-full bg-emas-400" style="width: {{ $persen }}%"></div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200/70">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Belum Melapor</p>
            <p class="mt-2 text-[40px] font-extrabold leading-none tabular-nums {{ $total['belum'] > 0 ? 'text-emas-500' : 'text-hijau-600' }}">
                {{ $total['belum'] }}
            </p>
            <p class="mt-1.5 text-[12px] font-medium text-slate-400">juru sungai</p>
        </div>

        <div class="rounded-2xl p-5 ring-1 {{ $total['bermasalah'] > 0 ? 'bg-bata-50 ring-bata-500/20' : 'bg-white ring-slate-200/70' }}">
            <p class="text-[11px] font-bold uppercase tracking-wider {{ $total['bermasalah'] > 0 ? 'text-bata-700/70' : 'text-slate-400' }}">
                Temuan Bermasalah
            </p>
            <p class="mt-2 text-[40px] font-extrabold leading-none tabular-nums {{ $total['bermasalah'] > 0 ? 'text-bata-600' : 'text-hijau-600' }}">
                {{ $total['bermasalah'] }}
            </p>
            <p class="mt-1.5 text-[12px] font-medium {{ $total['bermasalah'] > 0 ? 'text-bata-700/60' : 'text-slate-400' }}">
                dari {{ $total['bangunan'] }} bangunan diperiksa
            </p>
        </div>
    </div>

    {{-- Tabel rekap --}}
    <div class="mt-3.5 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
        <div class="px-5 py-4">
            <h2 class="text-sm font-bold text-sungai-950">Rekap per DAS</h2>
        </div>

        @if ($rekap->isEmpty())
            <x-kosong judul="Belum ada DAS terdaftar" pesan="Tambahkan DAS terlebih dahulu."/>
        @else
            <div class="overflow-x-auto scrollbar-halus">
                <table class="w-full">
                    <thead>
                        <tr class="border-t border-slate-100 bg-slate-50/70 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-3">DAS</th>
                            <th class="px-5 py-3 text-center">Juru</th>
                            <th class="px-5 py-3 text-center">Sudah</th>
                            <th class="px-5 py-3 text-center">Belum</th>
                            <th class="px-5 py-3 text-center">Laporan</th>
                            <th class="px-5 py-3 text-center">Bangunan</th>
                            <th class="px-5 py-3 text-center">Bermasalah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[13.5px]">
                        @foreach ($rekap as $baris)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <a href="{{ route('das.show', $baris->id) }}" class="font-semibold text-slate-800 hover:text-sungai-600">
                                        {{ $baris->nama_das }}
                                    </a>
                                    <span class="ml-1 text-[12px] text-slate-400">{{ $baris->kode_das }}</span>
                                </td>
                                <td class="px-5 py-3 text-center tabular-nums text-slate-600">{{ $baris->jumlah_juru }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="font-bold tabular-nums text-hijau-600">{{ $baris->sudah_lapor }}</span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="font-bold tabular-nums {{ $baris->belum_lapor > 0 ? 'text-emas-600' : 'text-slate-300' }}">
                                        {{ $baris->belum_lapor }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center tabular-nums text-slate-600">{{ $baris->jumlah_laporan }}</td>
                                <td class="px-5 py-3 text-center tabular-nums text-slate-600">{{ $baris->jumlah_bangunan }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="font-bold tabular-nums {{ $baris->temuan_bermasalah > 0 ? 'text-bata-600' : 'text-slate-300' }}">
                                        {{ $baris->temuan_bermasalah }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-200 bg-slate-50 text-[13.5px] font-extrabold text-sungai-950">
                            <td class="px-5 py-3.5">TOTAL</td>
                            <td class="px-5 py-3.5 text-center tabular-nums">{{ $total['juru'] }}</td>
                            <td class="px-5 py-3.5 text-center tabular-nums text-hijau-700">{{ $total['sudah'] }}</td>
                            <td class="px-5 py-3.5 text-center tabular-nums {{ $total['belum'] > 0 ? 'text-emas-700' : '' }}">{{ $total['belum'] }}</td>
                            <td class="px-5 py-3.5 text-center tabular-nums">{{ $total['laporan'] }}</td>
                            <td class="px-5 py-3.5 text-center tabular-nums">{{ $total['bangunan'] }}</td>
                            <td class="px-5 py-3.5 text-center tabular-nums {{ $total['bermasalah'] > 0 ? 'text-bata-700' : '' }}">{{ $total['bermasalah'] }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    {{-- Daftar belum melapor --}}
    <div class="mt-3.5 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
        <div class="flex items-center justify-between px-5 py-4">
            <h2 class="text-sm font-bold text-sungai-950">Daftar Juru Belum Melapor</h2>
            <x-lencana :warna="$juruBelumLapor->isEmpty() ? 'normal' : 'emas'">
                {{ $juruBelumLapor->count() }} juru
            </x-lencana>
        </div>

        @if ($juruBelumLapor->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-hijau-50">
                    <svg class="h-6 w-6 text-hijau-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-hijau-700">Seluruh juru sudah melapor</p>
                <p class="mt-1 text-[13px] text-slate-400">Periode {{ Bulan::nama($bulan) }} {{ $tahun }}</p>
            </div>
        @else
            <div class="overflow-x-auto scrollbar-halus">
                <table class="w-full">
                    <thead>
                        <tr class="border-t border-slate-100 bg-slate-50/70 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Telepon</th>
                            <th class="px-5 py-3">DAS</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[13.5px]">
                        @foreach ($juruBelumLapor as $orang)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emas-100 text-[12px] font-extrabold text-emas-800">
                                            {{ strtoupper(substr($orang->nama, 0, 1)) }}
                                        </span>
                                        <span class="font-semibold text-slate-800">{{ $orang->nama }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 tabular-nums text-slate-500">{{ $orang->no_telepon ?: '-' }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $orang->das?->nama_das ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <a href="{{ route('juru.show', $orang) }}"
                                       class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-sungai-600 transition hover:bg-sungai-50">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection
