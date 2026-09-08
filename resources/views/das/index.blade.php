@extends('layouts.app')

@section('judul', 'Daerah Aliran Sungai')
@section('subjudul', $das->total() . ' daerah aliran sungai terdaftar')

@section('aksi-header')
    @if (auth('staf')->user()?->isAdmin())
        <a href="{{ route('das.create') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-aksi px-4 py-2.5 text-[13px] font-bold text-aksi-teks shadow-lg shadow-aksi/25 transition hover:bg-aksi-sorot">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
            </svg>
            Tambah DAS
        </a>
    @endif
@endsection

@php
    $kelasInput = 'w-full rounded-xl border-0 bg-permukaan-lembut px-3 py-2 text-[13px] font-medium text-teks ring-1 ring-inset ring-garis transition focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
@endphp

@push('kepala')
    @vite('resources/js/peta.js')
@endpush

@section('konten')

    @if ($belumBerkoordinat->isNotEmpty())
        <div class="mb-3.5 flex flex-wrap items-start gap-3 rounded-2xl bg-peringatan-latar p-4 ring-1 ring-peringatan-teks/25">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-peringatan-teks" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
            </svg>
            <div class="min-w-0 flex-1">
                <p class="text-[13px] font-semibold text-peringatan-teks-pekat">
                    {{ $belumBerkoordinat->count() }} DAS belum punya titik dan tidak muncul di peta
                </p>
                <p class="mt-1 text-[12px] leading-relaxed text-peringatan-teks">
                    Buka DAS-nya lalu tentukan titiknya dengan mengklik peta pada formulir.
                    Titik ini perkiraan saja &mdash; DAS sebenarnya berupa wilayah, bukan titik.
                </p>
            </div>
        </div>
    @endif

    {{-- Peta. Dulu halaman tersendiri, kini menyatu dengan daftarnya. --}}
    @if ($dataPeta->isNotEmpty())
        <div class="mb-3.5 grid gap-3.5 lg:grid-cols-3">
            <div class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70 lg:col-span-2">
                <div id="peta-das" class="relative h-[clamp(18rem,42vh,26rem)] w-full" data-das="{{ $dataPeta->toJson() }}"></div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
                <div id="panel-kosong" class="flex h-full flex-col items-center justify-center px-6 py-12 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-info-latar">
                        <svg class="h-6 w-6 text-info-teks" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.7 16.7 12 22l-5.7-5.3a8 8 0 1111.4 0z"/>
                            <circle cx="12" cy="11" r="2.5"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-teks-sedang">Pilih sebuah DAS</p>
                    <p class="mt-1 max-w-[15rem] text-[13px] leading-relaxed text-teks-samar">
                        Klik penanda di peta untuk melihat juru yang bertugas beserta ringkasan laporannya.
                    </p>
                </div>
                <div id="panel-das" class="hidden"></div>
            </div>
        </div>
    @endif


    @error('hapus')
        <div class="mb-3.5 rounded-2xl bg-bahaya-latar px-4 py-3 text-[13px] font-medium text-bahaya-teks ring-1 ring-bahaya-teks/25">
            {{ $message }}
        </div>
    @enderror

    <form method="GET" class="rounded-2xl bg-permukaan p-4 ring-1 ring-garis/70">
        <label class="mb-1 block text-[12px] font-bold uppercase tracking-wider text-teks-lembut">Pencarian</label>
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" name="cari" value="{{ $filter['cari'] }}" placeholder="Nama atau kode DAS"
                   class="{{ $kelasInput }} max-w-md flex-1">
            <button type="submit" class="rounded-xl bg-cari px-4 py-2 text-[13px] font-bold text-cari-teks transition hover:bg-cari-sorot">
                Cari
            </button>
            @if ($filter['cari'])
                <a href="{{ route('das.index') }}" class="rounded-xl px-3 py-2 text-[13px] font-semibold text-teks-samar transition hover:bg-permukaan-sedang hover:text-teks-sedang">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <div class="mt-3.5 overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
        @if ($das->isEmpty())
            <x-kosong judul="Tidak ada DAS yang cocok" pesan="Coba ubah kata kunci pencarian."/>
        @else
            <div class="overflow-x-auto scrollbar-halus">
                <table class="w-full">
                    <thead>
                        <tr class="bg-permukaan-lembut/70 text-left text-[12px] font-bold uppercase tracking-wider text-teks-lembut">
                            <th class="px-5 py-3">Nama DAS</th>
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Wilayah Sungai</th>
                            <th class="px-5 py-3 text-center">Juru</th>
                            <th class="px-5 py-3 text-center">Laporan</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis-halus text-[13.5px]">
                        @foreach ($das as $item)
                            <tr class="transition hover:bg-permukaan-lembut">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-info-latar">
                                            <svg class="h-4 w-4 text-info-teks" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 15c2.5-2 5.5-2 8 0s5.5 2 8 0M3 9c2.5-2 5.5-2 8 0s5.5 2 8 0"/>
                                            </svg>
                                        </span>
                                        <span class="font-semibold text-teks">{{ $item->nama_das }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 font-medium text-teks-samar">{{ $item->kode_das }}</td>
                                <td class="px-5 py-3 text-teks-samar">{{ $item->wilayahSungai?->nama_sungai ?? '-' }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-permukaan-sedang px-1.5 text-[12px] font-bold tabular-nums text-teks-lembut">
                                        {{ $item->juru_sungai_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-permukaan-sedang px-1.5 text-[12px] font-bold tabular-nums text-teks-lembut">
                                        {{ $item->laporan_op_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <x-lencana :warna="$item->aktif ? 'normal' : 'netral'">
                                        {{ $item->aktif ? 'Aktif' : 'Nonaktif' }}
                                    </x-lencana>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <a href="{{ route('das.show', $item) }}"
                                       class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-info-teks transition hover:bg-info-latar">
                                        Detail
                                    </a>
                                    @if (auth('staf')->user()?->isAdmin())
                                        <a href="{{ route('das.edit', $item) }}"
                                           class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-teks-lembut transition hover:bg-permukaan-sedang">
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('das.destroy', $item) }}" class="inline"
                                              onsubmit="return confirm('Hapus DAS {{ $item->nama_das }}?')">
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

            @if ($das->hasPages())
                <div class="border-t border-garis-halus px-5 py-3">{{ $das->links() }}</div>
            @endif
        @endif
    </div>

@endsection
