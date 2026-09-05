@extends('layouts.app')

@section('judul', 'Juru Sungai')
@section('subjudul', $juru->total() . ' juru terdaftar')

@section('aksi-header')
    @if (auth('staf')->user()?->isAdmin())
        <a href="{{ route('juru.create') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-sungai-950 px-4 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-sungai-950/15 transition hover:bg-sungai-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
            </svg>
            Tambah Juru
        </a>
    @endif
@endsection

@php
    $kelasInput = 'w-full rounded-xl border-0 bg-slate-50 px-3 py-2 text-[13px] font-medium text-slate-800 ring-1 ring-inset ring-slate-200 transition focus:bg-white focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
    $adaFilter = collect($filter)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
@endphp

@section('konten')

    @error('hapus')
        <div class="mb-3.5 rounded-2xl bg-bata-50 px-4 py-3 text-[13px] font-medium text-bata-700 ring-1 ring-bata-500/20">
            {{ $message }}
        </div>
    @enderror

    <form method="GET" class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/70">
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Pencarian</label>
                <input type="text" name="cari" value="{{ $filter['cari'] }}" placeholder="Nama, NIP, atau username" class="{{ $kelasInput }}">
            </div>
            <div>
                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-400">DAS</label>
                <select name="das_id" class="{{ $kelasInput }}">
                    <option value="">Semua DAS</option>
                    @foreach ($daftarDas as $w)
                        <option value="{{ $w->id }}" @selected((string) $filter['das_id'] === (string) $w->id)>{{ $w->nama_das }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3.5 flex items-center gap-2">
            <button type="submit" class="rounded-xl bg-sungai-600 px-4 py-2 text-[13px] font-bold text-white transition hover:bg-sungai-700">
                Terapkan Filter
            </button>
            @if ($adaFilter)
                <a href="{{ route('juru.index') }}" class="rounded-xl px-3 py-2 text-[13px] font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <div class="mt-3.5 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
        @if ($juru->isEmpty())
            <x-kosong judul="Tidak ada juru sungai yang cocok" pesan="Coba ubah kata kunci pencarian."/>
        @else
            <div class="overflow-x-auto scrollbar-halus">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/70 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">NIP</th>
                            <th class="px-5 py-3">DAS</th>
                            <th class="px-5 py-3 text-center">Laporan</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[13.5px]">
                        @foreach ($juru as $orang)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sungai-50 text-[12px] font-extrabold text-sungai-700">
                                            {{ strtoupper(substr($orang->nama, 0, 1)) }}
                                        </span>
                                        <span class="font-semibold text-slate-800">{{ $orang->nama }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 tabular-nums text-slate-500">{{ $orang->nip ?: '-' }}</td>
                                <td class="px-5 py-3 text-slate-500">{{ $orang->das?->nama_das ?? '-' }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-slate-100 px-1.5 text-[12px] font-bold tabular-nums text-slate-600">
                                        {{ $orang->laporan_op_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <x-lencana :warna="$orang->aktif ? 'normal' : 'netral'">
                                        {{ $orang->aktif ? 'Aktif' : 'Nonaktif' }}
                                    </x-lencana>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <a href="{{ route('juru.show', $orang) }}"
                                       class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-sungai-600 transition hover:bg-sungai-50">
                                        Detail
                                    </a>
                                    @if (auth('staf')->user()?->isAdmin())
                                        <a href="{{ route('juru.edit', $orang) }}"
                                           class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-slate-600 transition hover:bg-slate-100">
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('juru.destroy', $orang) }}" class="inline"
                                              onsubmit="return confirm('Hapus juru {{ $orang->nama }}?')">
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

            @if ($juru->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $juru->links() }}</div>
            @endif
        @endif
    </div>

@endsection
