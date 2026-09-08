@extends('layouts.app')

@section('judul', 'Juru Sungai')
@section('subjudul', $juru->total() . ' juru terdaftar')

@section('aksi-header')
    @if (auth('staf')->user()?->isAdmin())
        <a href="{{ route('juru.create') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-aksi px-4 py-2.5 text-[13px] font-bold text-aksi-teks shadow-lg shadow-aksi/25 transition hover:bg-aksi-sorot">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
            </svg>
            Tambah Juru
        </a>
    @endif
@endsection

@php
    $kelasInput = 'w-full rounded-xl border-0 bg-permukaan-lembut px-3 py-2 text-[13px] font-medium text-teks ring-1 ring-inset ring-garis transition focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
    $adaFilter = collect($filter)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
@endphp

@section('konten')

    @error('hapus')
        <div class="mb-3.5 rounded-2xl bg-bahaya-latar px-4 py-3 text-[13px] font-medium text-bahaya-teks ring-1 ring-bahaya-teks/25">
            {{ $message }}
        </div>
    @enderror

    <form method="GET" class="rounded-2xl bg-permukaan p-4 ring-1 ring-garis/70">
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-[12px] font-bold uppercase tracking-wider text-teks-lembut">Pencarian</label>
                <input type="text" name="cari" value="{{ $filter['cari'] }}" placeholder="Nama, telepon, atau username" class="{{ $kelasInput }}">
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
        </div>
        <div class="mt-3.5 flex items-center gap-2">
            <button type="submit" class="rounded-xl bg-cari px-4 py-2 text-[13px] font-bold text-cari-teks transition hover:bg-cari-sorot">
                Terapkan Filter
            </button>
            @if ($adaFilter)
                <a href="{{ route('juru.index') }}" class="rounded-xl px-3 py-2 text-[13px] font-semibold text-teks-samar transition hover:bg-permukaan-sedang hover:text-teks-sedang">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <div class="mt-3.5 overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
        @if ($juru->isEmpty())
            <x-kosong judul="Tidak ada juru sungai yang cocok" pesan="Coba ubah kata kunci pencarian."/>
        @else
            <div class="overflow-x-auto scrollbar-halus">
                <table class="w-full">
                    <thead>
                        <tr class="bg-permukaan-lembut/70 text-left text-[12px] font-bold uppercase tracking-wider text-teks-lembut">
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">DAS</th>
                            <th class="px-5 py-3 text-center">Laporan</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis-halus text-[13.5px]">
                        @foreach ($juru as $orang)
                            <tr class="transition hover:bg-permukaan-lembut">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-info-latar text-[12px] font-extrabold text-info-teks">
                                            {{ strtoupper(substr($orang->nama, 0, 1)) }}
                                        </span>
                                        <span class="font-semibold text-teks">{{ $orang->nama }}</span>
                                    </div>
                                </td>
                                                                <td class="px-5 py-3 text-teks-samar">{{ $orang->das?->nama_das ?? '-' }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-permukaan-sedang px-1.5 text-[12px] font-bold tabular-nums text-teks-lembut">
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
                                       class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-info-teks transition hover:bg-info-latar">
                                        Detail
                                    </a>
                                    @if (auth('staf')->user()?->isAdmin())
                                        <a href="{{ route('juru.edit', $orang) }}"
                                           class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-teks-lembut transition hover:bg-permukaan-sedang">
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('juru.destroy', $orang) }}" class="inline"
                                              onsubmit="return confirm('Hapus juru {{ $orang->nama }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-bahaya-teks transition hover:bg-bahaya-latar">
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
                <div class="border-t border-garis-halus px-5 py-3">{{ $juru->links() }}</div>
            @endif
        @endif
    </div>

@endsection
