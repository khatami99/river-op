@extends('layouts.app')

@section('judul', 'Manajemen Pengguna')
@section('subjudul', $staf->total() . ' akun staf dinas')

@section('aksi-header')
    <a href="{{ route('staf.create') }}"
       class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-aksi px-4 py-2.5 text-[13px] font-bold text-aksi-teks shadow-lg shadow-aksi/25 transition hover:bg-aksi-sorot">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
        </svg>
        Tambah Akun
    </a>
@endsection

@php
    $ki = 'w-full rounded-xl border-0 bg-permukaan-lembut px-3 py-2 text-[13px] font-medium text-teks ring-1 ring-inset ring-garis transition focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
@endphp

@section('konten')

    @error('hapus')
        <div class="mb-3.5 rounded-2xl bg-bahaya-latar px-4 py-3 text-[13px] font-medium text-bahaya-teks ring-1 ring-bahaya-teks/25">
            {{ $message }}
        </div>
    @enderror

    <form method="GET" class="rounded-2xl bg-permukaan p-4 ring-1 ring-garis/70">
        <label class="mb-1 block text-[12px] font-bold uppercase tracking-wider text-teks-lembut">Pencarian</label>
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" name="cari" value="{{ $filter['cari'] }}" placeholder="Nama atau username"
                   class="{{ $ki }} max-w-md flex-1">
            <button type="submit" class="rounded-xl bg-cari px-4 py-2 text-[13px] font-bold text-cari-teks transition hover:bg-cari-sorot">Cari</button>
            @if ($filter['cari'])
                <a href="{{ route('staf.index') }}" class="rounded-xl px-3 py-2 text-[13px] font-semibold text-teks-samar transition hover:bg-permukaan-sedang hover:text-teks-sedang">Reset</a>
            @endif
        </div>
    </form>

    <div class="mt-3.5 overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
        @if ($staf->isEmpty())
            <x-kosong judul="Tidak ada akun yang cocok" pesan="Coba ubah kata kunci pencarian."/>
        @else
            <div class="overflow-x-auto scrollbar-halus">
                <table class="w-full">
                    <thead>
                        <tr class="bg-permukaan-lembut/70 text-left text-[12px] font-bold uppercase tracking-wider text-teks-lembut">
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Username</th>
                            <th class="px-5 py-3">Peran</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis-halus text-[13.5px]">
                        @foreach ($staf as $orang)
                            @php $diriSendiri = $orang->id === auth('staf')->id(); @endphp
                            <tr class="transition hover:bg-permukaan-lembut">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-peringatan-latar text-[12px] font-extrabold text-peringatan-teks">
                                            {{ strtoupper(substr($orang->nama, 0, 1)) }}
                                        </span>
                                        <span class="font-semibold text-teks">{{ $orang->nama }}</span>
                                        @if ($diriSendiri)
                                            <span class="text-[11px] font-semibold text-teks-samar">(Anda)</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-teks-samar">{{ $orang->username }}</td>
                                <td class="px-5 py-3">
                                    <x-lencana :warna="$orang->isAdmin() ? 'emas' : 'netral'">
                                        {{ $orang->isAdmin() ? 'Admin' : 'Viewer' }}
                                    </x-lencana>
                                </td>
                                <td class="px-5 py-3">
                                    <x-lencana :warna="$orang->aktif ? 'normal' : 'netral'">
                                        {{ $orang->aktif ? 'Aktif' : 'Nonaktif' }}
                                    </x-lencana>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <a href="{{ route('staf.edit', $orang) }}"
                                       class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-info-teks transition hover:bg-info-latar">
                                        Ubah
                                    </a>
                                    @unless ($diriSendiri)
                                        <form method="POST" action="{{ route('staf.destroy', $orang) }}" class="inline"
                                              onsubmit="return confirm('Hapus akun {{ $orang->nama }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-bahaya-teks transition hover:bg-bahaya-latar">
                                                Hapus
                                            </button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($staf->hasPages())
                <div class="border-t border-garis-halus px-5 py-3">{{ $staf->links() }}</div>
            @endif
        @endif
    </div>

@endsection
