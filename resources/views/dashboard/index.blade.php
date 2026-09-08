@extends('layouts.app')

@use('App\Support\Bulan')

@section('judul', 'Dashboard')
@section('subjudul', 'Ringkasan pelaporan ' . Bulan::nama($bulan) . ' ' . $tahun)

@section('konten')

    <x-pemilih-periode :bulan="$bulan" :tahun="$tahun" :daftar-tahun="$daftarTahun"/>

    @php
        $keliling = 2 * M_PI * 42;
        $sisa = $keliling * (1 - $persentase / 100);
    @endphp

    {{-- Bento ringkasan --}}
    <div class="grid grid-cols-2 gap-3.5 xl:grid-cols-4">

        {{-- Kartu utama: progres pelaporan --}}
        <div class="relative col-span-2 overflow-hidden rounded-2xl bg-sungai-950 p-6 xl:row-span-2">
            <svg class="pointer-events-none absolute -bottom-8 -right-10 w-72 opacity-20" viewBox="0 0 400 120" fill="none">
                <path d="M0 40c40-24 80-24 120 0s80 24 120 0 80-24 120 0 80 24 120 0" stroke="#5db8f8" stroke-width="9" stroke-linecap="round"/>
                <path d="M0 76c40-24 80-24 120 0s80 24 120 0 80-24 120 0 80 24 120 0" stroke="#127cc4" stroke-width="9" stroke-linecap="round"/>
            </svg>

            <div class="relative flex h-full flex-col justify-between gap-6">
                <div>
                    <p class="text-[12px] font-bold uppercase tracking-wider text-emas-400">Progres Pelaporan</p>
                    <p class="mt-1 text-lg font-bold text-white">{{ Bulan::nama($bulan) }} {{ $tahun }}</p>
                </div>

                <div class="flex items-center gap-6">
                    <div class="relative h-[104px] w-[104px] shrink-0">
                        <svg class="h-full w-full -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="42" fill="none" stroke="currentColor" class="text-white/10" stroke-width="11"/>
                            <circle cx="50" cy="50" r="42" fill="none" stroke="currentColor" class="text-emas-400"
                                    stroke-width="11" stroke-linecap="round"
                                    stroke-dasharray="{{ $keliling }}" stroke-dashoffset="{{ $sisa }}"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-extrabold tabular-nums text-white">{{ $persentase }}<span class="text-sm">%</span></span>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <p class="text-[34px] font-extrabold leading-none tabular-nums text-white">
                            {{ $sudahLapor }}<span class="text-lg font-bold text-sungai-300/80">/{{ $juruAktif }}</span>
                        </p>
                        <p class="mt-2 text-[13px] leading-relaxed text-sungai-200/70">
                            juru sungai sudah<br>mengirim laporan
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Belum melapor: satu-satunya kartu beraksen penuh, karena ini yang perlu ditindaklanjuti --}}
        <div class="relative overflow-hidden rounded-2xl bg-alarm-latar p-5">
            <p class="text-[12px] font-bold uppercase tracking-wider text-alarm-teks">Belum Melapor</p>
            <p class="mt-2 text-[40px] font-extrabold leading-none tabular-nums text-alarm-tegas">{{ $belumLapor }}</p>
            <p class="mt-1.5 text-[12px] font-semibold text-alarm-teks">perlu ditindaklanjuti</p>
            <svg class="absolute -bottom-4 -right-3 h-20 w-20 text-alarm-teks/15" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
            </svg>
        </div>

        {{-- Sudah melapor --}}
        <div class="rounded-2xl bg-permukaan p-5 ring-1 ring-garis/70">
            <div class="flex items-center justify-between">
                <p class="text-[12px] font-bold uppercase tracking-wider text-teks-lembut">Sudah Melapor</p>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-aman-latar">
                    <svg class="h-4 w-4 text-aman-teks" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-[40px] font-extrabold leading-none tabular-nums text-aman-teks">{{ $sudahLapor }}</p>
            <p class="mt-1.5 text-[12px] font-medium text-teks-samar">periode ini</p>
        </div>

        {{-- Juru aktif --}}
        <div class="rounded-2xl bg-permukaan p-5 ring-1 ring-garis/70">
            <div class="flex items-center justify-between">
                <p class="text-[12px] font-bold uppercase tracking-wider text-teks-lembut">Juru Aktif</p>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-info-latar">
                    <svg class="h-4 w-4 text-info-teks" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.4-1.9M17 20H7m10 0v-2c0-.7-.1-1.3-.4-1.9M7 20H2v-2a3 3 0 015.4-1.9M7 20v-2c0-.7.1-1.3.4-1.9m0 0a5 5 0 019.2 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-[40px] font-extrabold leading-none tabular-nums text-judul">{{ $juruAktif }}</p>
            <p class="mt-1.5 text-[12px] font-medium text-teks-samar">terdaftar di sistem</p>
        </div>

        {{-- Laporan bermasalah --}}
        <a href="{{ route('laporan.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
           class="rounded-2xl p-5 ring-1 transition hover:ring-2
                  {{ $laporanBermasalah > 0 ? 'bg-bahaya-latar ring-bahaya-teks/25 hover:ring-bata-500/40' : 'bg-permukaan ring-garis/70 hover:ring-garis-kuat' }}">
            <div class="flex items-center justify-between">
                <p class="text-[12px] font-bold uppercase tracking-wider {{ $laporanBermasalah > 0 ? 'text-bahaya-teks' : 'text-teks-lembut' }}">
                    Laporan Bermasalah
                </p>
                <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $laporanBermasalah > 0 ? 'bg-bata-100' : 'bg-aman-latar' }}">
                    @if ($laporanBermasalah > 0)
                        <svg class="h-4 w-4 text-bahaya-teks" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
                        </svg>
                    @else
                        <svg class="h-4 w-4 text-aman-teks" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    @endif
                </span>
            </div>
            <p class="mt-2 text-[40px] font-extrabold leading-none tabular-nums {{ $laporanBermasalah > 0 ? 'text-bahaya-teks' : 'text-aman-teks' }}">
                {{ $laporanBermasalah }}
            </p>
            <p class="mt-1.5 text-[12px] font-medium {{ $laporanBermasalah > 0 ? 'text-bahaya-teks' : 'text-teks-samar' }}">
                dari {{ $totalLaporanPeriode }} laporan periode ini
            </p>
        </a>
    </div>

    {{-- Dua panel bawah --}}
    <div class="mt-3.5 grid gap-3.5 lg:grid-cols-5">

        {{-- Laporan terbaru --}}
        <div class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70 lg:col-span-3">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="text-sm font-bold text-judul">Laporan Terbaru</h2>
                <a href="{{ route('laporan.index') }}"
                   class="inline-flex items-center gap-1 text-[13px] font-semibold text-info-teks transition hover:text-info-teks">
                    Lihat semua
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            @if ($laporanTerbaru->isEmpty())
                <x-kosong judul="Belum ada laporan masuk" pesan="Laporan dari aplikasi juru akan muncul di sini."/>
            @else
                <ul class="divide-y divide-garis-halus border-t border-garis-halus">
                    @foreach ($laporanTerbaru as $item)
                        <li>
                            <a href="{{ route('laporan.show', $item) }}"
                               class="flex items-center gap-3.5 px-5 py-3 transition hover:bg-permukaan-lembut">
                                <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-xl bg-info-latar leading-none">
                                    <span class="text-[13px] font-extrabold tabular-nums text-info-teks">
                                        {{ optional($item->tanggal_inspeksi)->format('d') }}
                                    </span>
                                    <span class="mt-0.5 text-[9px] font-bold uppercase tracking-wide text-info-teks">
                                        {{ Bulan::singkat($item->bulan) }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[13.5px] font-semibold text-teks">{{ $item->juruSungai?->nama ?? '-' }}</p>
                                    <p class="truncate text-[12px] text-teks-samar">{{ $item->das?->nama_das ?? '-' }}</p>
                                </div>
                                <svg class="h-4 w-4 shrink-0 text-teks-pudar" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Belum melapor --}}
        <div class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70 lg:col-span-2">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="text-sm font-bold text-judul">Belum Melapor</h2>
                <x-lencana warna="emas">{{ $juruBelumLapor->count() }} juru</x-lencana>
            </div>

            @if ($juruBelumLapor->isEmpty())
                <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-aman-latar">
                        <svg class="h-6 w-6 text-aman-teks" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-aman-teks">Semua juru sudah melapor</p>
                    <p class="mt-1 text-[13px] text-teks-samar">Periode {{ Bulan::nama($bulan) }} {{ $tahun }}</p>
                </div>
            @else
                <ul class="max-h-[340px] divide-y divide-garis-halus overflow-y-auto border-t border-garis-halus scrollbar-halus">
                    @foreach ($juruBelumLapor as $orang)
                        <li>
                            <a href="{{ route('juru.show', $orang) }}"
                               class="flex items-center gap-3 px-5 py-3 transition hover:bg-permukaan-lembut">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-peringatan-latar text-[12px] font-extrabold text-peringatan-teks">
                                    {{ strtoupper(substr($orang->nama, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[13.5px] font-semibold text-teks">{{ $orang->nama }}</p>
                                    <p class="truncate text-[12px] text-teks-samar">{{ $orang->das?->nama_das ?? '-' }}</p>
                                </div>
                                <svg class="h-4 w-4 shrink-0 text-teks-pudar" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

@endsection
