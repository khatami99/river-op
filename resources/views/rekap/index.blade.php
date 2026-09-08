@extends('layouts.app')

@use('App\Support\Bulan')

@section('judul', 'Rekapitulasi')
@section('subjudul', 'Rekap pelaporan ' . Bulan::nama($bulan) . ' ' . $tahun)

@section('aksi-header')
    <a href="{{ route('rekap.export', ['bulan' => $bulan, 'tahun' => $tahun]) }}" data-unduh
       class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-aksi px-4 py-2.5 text-[13px] font-bold text-aksi-teks shadow-lg shadow-aksi/25 transition hover:bg-aksi-sorot">
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
                <p class="text-[12px] font-bold uppercase tracking-wider text-emas-400">Kepatuhan Pelaporan</p>
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

        <div class="rounded-2xl bg-permukaan p-5 ring-1 ring-garis/70">
            <p class="text-[12px] font-bold uppercase tracking-wider text-teks-lembut">Belum Melapor</p>
            <p class="mt-2 text-[40px] font-extrabold leading-none tabular-nums {{ $total['belum'] > 0 ? 'text-emas-500' : 'text-aman-teks' }}">
                {{ $total['belum'] }}
            </p>
            <p class="mt-1.5 text-[12px] font-medium text-teks-samar">juru sungai</p>
        </div>

        <div class="rounded-2xl p-5 ring-1 {{ $total['bermasalah'] > 0 ? 'bg-bahaya-latar ring-bahaya-teks/25' : 'bg-permukaan ring-garis/70' }}">
            <p class="text-[12px] font-bold uppercase tracking-wider {{ $total['bermasalah'] > 0 ? 'text-bahaya-teks' : 'text-teks-lembut' }}">
                Temuan Bermasalah
            </p>
            <p class="mt-2 text-[40px] font-extrabold leading-none tabular-nums {{ $total['bermasalah'] > 0 ? 'text-bahaya-teks' : 'text-aman-teks' }}">
                {{ $total['bermasalah'] }}
            </p>
            <p class="mt-1.5 text-[12px] font-medium {{ $total['bermasalah'] > 0 ? 'text-bahaya-teks' : 'text-teks-samar' }}">
                dari {{ $total['bangunan'] }} bangunan diperiksa
            </p>
        </div>
    </div>

    {{-- Tabel rekap --}}
    <div class="mt-3.5 overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
        <div class="px-5 py-4">
            <h2 class="text-sm font-bold text-judul">Rekap per DAS</h2>
        </div>

        @if ($rekap->isEmpty())
            <x-kosong judul="Belum ada DAS terdaftar" pesan="Tambahkan DAS terlebih dahulu."/>
        @else
            <div class="max-h-[68vh] overflow-auto scrollbar-halus">
                <table class="w-full">
                    {{-- Kepala tabel melekat.

                         Enam kolom di sini semuanya angka. Begitu kepalanya
                         hanyut, deretan "0 0 0 0 0 0" tidak berarti apa-apa —
                         pembaca tidak lagi tahu mana Sudah, mana Belum, mana
                         Bermasalah. Dengan 62 DAS, itu terjadi setelah
                         beberapa detik menggulung.

                         Latarnya wajib pekat, bukan bg-.../70 seperti dulu:
                         baris yang lewat di bawahnya akan menembus warna
                         yang tembus pandang. --}}
                    <thead>
                        <tr class="text-left text-[12px] font-bold uppercase tracking-wider text-teks-lembut">
                            @php
                                $kepala = 'sticky top-0 z-10 border-b border-garis-halus bg-permukaan-lembut px-5 py-3';
                            @endphp
                            <th class="{{ $kepala }}">DAS</th>
                            <th class="{{ $kepala }} text-center">Juru</th>
                            <th class="{{ $kepala }} text-center">Sudah</th>
                            <th class="{{ $kepala }} text-center">Belum</th>
                            <th class="{{ $kepala }} text-center">Laporan</th>
                            <th class="{{ $kepala }} text-center">Bangunan</th>
                            <th class="{{ $kepala }} text-center">Bermasalah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis-halus text-[13.5px]">
                        {{-- DAS yang belum punya juru ditandai, bukan disembunyikan.

                             Sembilan dari sepuluh baris di sini nol permanen: bukan
                             karena jurunya lalai, melainkan karena belum ada juru yang
                             ditugaskan. Sebelumnya keduanya tampil sebagai angka 0 yang
                             sama persis — padahal yang pertama perlu ditegur dan yang
                             kedua perlu diisi orang.

                             Kolom laporannya diberi tanda pisah, bukan angka nol. Nol
                             berarti "diukur, hasilnya kosong"; tanda pisah berarti
                             "tidak berlaku". Ini tidak menyentuh baris TOTAL, sebab nol
                             memang tidak menambah apa pun pada penjumlahan. --}}
                        @foreach ($rekap as $baris)
                            @php $tanpaJuru = (int) $baris->jumlah_juru === 0; @endphp
                            <tr class="transition hover:bg-permukaan-lembut">
                                <td class="px-5 py-3">
                                    <a href="{{ route('das.show', $baris->id) }}" class="font-semibold text-teks hover:text-info-teks">
                                        {{ $baris->nama_das }}
                                    </a>
                                    <span class="ml-1 text-[12px] text-teks-samar">{{ $baris->kode_das }}</span>
                                    @if ($tanpaJuru)
                                        <span class="ml-1.5 inline-block rounded-md bg-permukaan-sedang px-1.5 py-0.5 text-[11px] font-semibold text-teks-samar">
                                            belum ada juru
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center tabular-nums {{ $tanpaJuru ? 'text-teks-pudar' : 'text-teks-lembut' }}">{{ $baris->jumlah_juru }}</td>
                                <td class="px-5 py-3 text-center">
                                    @if ($tanpaJuru)
                                        <span class="text-teks-pudar">&ndash;</span>
                                    @else
                                        <span class="font-bold tabular-nums text-aman-teks">{{ $baris->sudah_lapor }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if ($tanpaJuru)
                                        <span class="text-teks-pudar">&ndash;</span>
                                    @else
                                        <span class="font-bold tabular-nums {{ $baris->belum_lapor > 0 ? 'text-peringatan-teks' : 'text-teks-pudar' }}">
                                            {{ $baris->belum_lapor }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center tabular-nums {{ $tanpaJuru ? 'text-teks-pudar' : 'text-teks-lembut' }}">
                                    {{ $tanpaJuru ? '–' : $baris->jumlah_laporan }}
                                </td>
                                <td class="px-5 py-3 text-center tabular-nums {{ $tanpaJuru ? 'text-teks-pudar' : 'text-teks-lembut' }}">
                                    {{ $tanpaJuru ? '–' : $baris->jumlah_bangunan }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if ($tanpaJuru)
                                        <span class="text-teks-pudar">&ndash;</span>
                                    @else
                                        <span class="font-bold tabular-nums {{ $baris->temuan_bermasalah > 0 ? 'text-bahaya-teks' : 'text-teks-pudar' }}">
                                            {{ $baris->temuan_bermasalah }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php
                            $kaki = 'sticky bottom-0 z-10 border-t-2 border-garis bg-permukaan-lembut px-5 py-3.5';
                        @endphp
                        <tr class="text-[13.5px] font-extrabold text-judul">
                            <td class="{{ $kaki }}">TOTAL</td>
                            <td class="{{ $kaki }} text-center tabular-nums">{{ $total['juru'] }}</td>
                            <td class="{{ $kaki }} text-center tabular-nums text-aman-teks">{{ $total['sudah'] }}</td>
                            <td class="{{ $kaki }} text-center tabular-nums {{ $total['belum'] > 0 ? 'text-peringatan-teks' : '' }}">{{ $total['belum'] }}</td>
                            <td class="{{ $kaki }} text-center tabular-nums">{{ $total['laporan'] }}</td>
                            <td class="{{ $kaki }} text-center tabular-nums">{{ $total['bangunan'] }}</td>
                            <td class="{{ $kaki }} text-center tabular-nums {{ $total['bermasalah'] > 0 ? 'text-bahaya-teks' : '' }}">{{ $total['bermasalah'] }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    {{-- Daftar belum melapor --}}
    <div class="mt-3.5 overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
        <div class="flex items-center justify-between px-5 py-4">
            <h2 class="text-sm font-bold text-judul">Daftar Juru Belum Melapor</h2>
            <x-lencana :warna="$juruBelumLapor->isEmpty() ? 'normal' : 'emas'">
                {{ $juruBelumLapor->count() }} juru
            </x-lencana>
        </div>

        @if ($juruBelumLapor->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-aman-latar">
                    <svg class="h-6 w-6 text-aman-teks" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-aman-teks">Seluruh juru sudah melapor</p>
                <p class="mt-1 text-[13px] text-teks-samar">Periode {{ Bulan::nama($bulan) }} {{ $tahun }}</p>
            </div>
        @else
            <div class="overflow-x-auto scrollbar-halus">
                <table class="w-full">
                    <thead>
                        <tr class="border-t border-garis-halus bg-permukaan-lembut/70 text-left text-[12px] font-bold uppercase tracking-wider text-teks-lembut">
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Telepon</th>
                            <th class="px-5 py-3">DAS</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis-halus text-[13.5px]">
                        @foreach ($juruBelumLapor as $orang)
                            <tr class="transition hover:bg-permukaan-lembut">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-peringatan-latar text-[12px] font-extrabold text-peringatan-teks">
                                            {{ strtoupper(substr($orang->nama, 0, 1)) }}
                                        </span>
                                        <span class="font-semibold text-teks">{{ $orang->nama }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 tabular-nums text-teks-samar">{{ $orang->no_telepon ?: '-' }}</td>
                                <td class="px-5 py-3 text-teks-samar">{{ $orang->das?->nama_das ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <a href="{{ route('juru.show', $orang) }}"
                                       class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-info-teks transition hover:bg-info-latar">
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
