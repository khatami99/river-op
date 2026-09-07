@extends('layouts.app')

@section('judul', $wilayah->nama_sungai)
@section('subjudul', $wilayah->kode_sungai)

@section('aksi-header')
    <a href="{{ route('wilayah.index') }}"
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
                <p class="text-[11px] font-bold uppercase tracking-widest text-emas-400">Wilayah Sungai</p>
                <p class="mt-1.5 text-lg font-extrabold text-white">{{ $wilayah->nama_sungai }}</p>

                <dl class="mt-4 space-y-3 border-t border-white/10 pt-4">
                    @php
                        $info = [
                            'Kode' => $wilayah->kode_sungai,
                            'Jumlah DAS' => $wilayah->das_count,
                            'Jumlah Juru' => $wilayah->juru_sungai_count,
                            'Total Laporan' => $wilayah->laporan_op_count,
                        ];
                    @endphp
                    @foreach ($info as $label => $nilai)
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-[12px] text-sungai-300/60">{{ $label }}</dt>
                            <dd class="text-right text-[13px] font-semibold text-white">{{ $nilai }}</dd>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-[12px] text-sungai-300/60">Status</dt>
                        <dd>
                            <x-lencana :warna="$wilayah->aktif ? 'normal' : 'netral'">
                                {{ $wilayah->aktif ? 'Aktif' : 'Nonaktif' }}
                            </x-lencana>
                        </dd>
                    </div>
                </dl>

                @if ($wilayah->deskripsi)
                    <p class="mt-4 border-t border-white/10 pt-4 text-[13px] leading-relaxed text-sungai-200/70">
                        {{ $wilayah->deskripsi }}
                    </p>
                @endif
            </div>
        </div>

        {{-- DAS di bawah wilayah sungai ini --}}
        <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70 lg:col-span-2">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="text-sm font-bold text-sungai-950">Daerah Aliran Sungai</h2>
                <x-lencana warna="sungai">{{ $wilayah->das->count() }} DAS</x-lencana>
            </div>

            @if ($wilayah->das->isEmpty())
                <x-kosong judul="Belum ada DAS" pesan="Wilayah sungai ini belum memiliki DAS."/>
            @else
                <ul class="divide-y divide-slate-100 border-t border-slate-100">
                    @foreach ($wilayah->das as $item)
                        <li>
                            <a href="{{ route('das.show', $item) }}" class="flex items-center gap-3 px-5 py-3 transition hover:bg-slate-50">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sungai-50">
                                    <svg class="h-4 w-4 text-sungai-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 15c2.5-2 5.5-2 8 0s5.5 2 8 0M3 9c2.5-2 5.5-2 8 0s5.5 2 8 0"/>
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[13.5px] font-semibold text-slate-800">{{ $item->nama_das }}</p>
                                    <p class="truncate text-[12px] text-slate-400">{{ $item->kode_das }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-4 text-[12px] text-slate-500">
                                    <span><span class="font-bold tabular-nums text-slate-700">{{ $item->juru_sungai_count }}</span> juru</span>
                                    <span><span class="font-bold tabular-nums text-slate-700">{{ $item->laporan_op_count }}</span> laporan</span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

@endsection
