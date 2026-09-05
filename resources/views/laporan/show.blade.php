@extends('layouts.app')

@use('App\Support\Bulan')

@section('judul', 'Detail Laporan OP')
@section('subjudul', $laporan->juruSungai?->nama . ' · ' . optional($laporan->tanggal_inspeksi)->format('d F Y'))

@section('aksi-header')
    <div class="flex shrink-0 gap-2">
        <button onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3.5 py-2.5 text-[13px] font-bold text-slate-700 transition hover:bg-slate-200">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zM7 9V5a2 2 0 012-2h6a2 2 0 012 2v4"/>
            </svg>
            Cetak
        </button>
        <a href="{{ route('laporan.index') }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3.5 py-2.5 text-[13px] font-bold text-slate-700 transition hover:bg-slate-200">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
    </div>
@endsection

@section('konten')

    @php
        $semuaTemuan = $laporan->itemInspeksi->flatMap->temuanKondisi;
        $jumlahBermasalah = $semuaTemuan->filter(fn ($t) => $t->adaMasalah())->count();
        $fotoLokasi = array_values(array_filter([$laporan->foto_lokasi_1, $laporan->foto_lokasi_2]));
    @endphp

    {{-- Ringkasan atas --}}
    <div class="grid gap-3.5 lg:grid-cols-3">

        <div class="rounded-2xl bg-sungai-950 p-5 lg:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-emas-400">Identitas Laporan</p>
                    <p class="mt-1.5 text-xl font-extrabold text-white">{{ $laporan->juruSungai?->nama ?? '-' }}</p>
                    <p class="text-[13px] text-sungai-200/70">
                        {{ $laporan->wilayahSungai?->nama_sungai ?? '-' }}
                        @if ($laporan->wilayahSungai?->kabupaten?->nama)
                            &middot; {{ $laporan->wilayahSungai->kabupaten->nama }}
                        @endif
                    </p>
                </div>
                <div class="rounded-xl bg-white/5 px-3.5 py-2 ring-1 ring-white/10">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-sungai-300/60">Periode</p>
                    <p class="text-[13px] font-bold text-white">{{ Bulan::nama($laporan->bulan) }} {{ $laporan->tahun }}</p>
                </div>
            </div>

            <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3.5 border-t border-white/10 pt-4 sm:grid-cols-4">
                @php
                    $ringkas = [
                        'NIP' => $laporan->juruSungai?->nip ?: '-',
                        'Tanggal Inspeksi' => optional($laporan->tanggal_inspeksi)->format('d M Y') ?? '-',
                        'Cuaca' => ucfirst($laporan->cuaca ?? '-'),
                        'Debit Air' => ucfirst($laporan->debit_air ?? '-'),
                    ];
                @endphp
                @foreach ($ringkas as $label => $nilai)
                    <div>
                        <dt class="text-[10px] font-bold uppercase tracking-widest text-sungai-300/50">{{ $label }}</dt>
                        <dd class="mt-1 text-[13.5px] font-semibold text-white">{{ $nilai }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- Angka kunci --}}
        <div class="grid grid-cols-2 gap-3.5 lg:grid-cols-1">
            <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/70">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Bangunan Diperiksa</p>
                <p class="mt-1.5 text-3xl font-extrabold tabular-nums text-sungai-950">{{ $laporan->itemInspeksi->count() }}</p>
            </div>
            <div class="rounded-2xl p-4 ring-1 {{ $jumlahBermasalah > 0 ? 'bg-bata-50 ring-bata-500/20' : 'bg-hijau-50 ring-hijau-500/20' }}">
                <p class="text-[11px] font-bold uppercase tracking-wider {{ $jumlahBermasalah > 0 ? 'text-bata-700/70' : 'text-hijau-700/70' }}">
                    Temuan Bermasalah
                </p>
                <p class="mt-1.5 text-3xl font-extrabold tabular-nums {{ $jumlahBermasalah > 0 ? 'text-bata-600' : 'text-hijau-600' }}">
                    {{ $jumlahBermasalah }}<span class="text-base font-bold opacity-40">/{{ $semuaTemuan->count() }}</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Foto lokasi --}}
    @if (count($fotoLokasi))
        <div class="mt-3.5 rounded-2xl bg-white p-5 ring-1 ring-slate-200/70">
            <p class="mb-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Foto Lokasi</p>
            <div class="flex flex-wrap gap-3">
                @foreach ($fotoLokasi as $foto)
                    <img src="{{ $foto }}" alt="Foto lokasi" loading="lazy"
                         class="h-32 w-44 cursor-zoom-in rounded-xl object-cover ring-1 ring-slate-200 transition hover:opacity-90"
                         onclick="bukaFoto(this.src)">
                @endforeach
            </div>
        </div>
    @endif

    {{-- Daftar bangunan --}}
    <div class="mt-3.5 space-y-3.5">
        @forelse ($laporan->itemInspeksi as $index => $item)
            @php $fotoBangunan = array_values(array_filter([$item->foto_1, $item->foto_2, $item->foto_3])); @endphp

            <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sungai-950 text-[13px] font-extrabold text-emas-400">
                            {{ $index + 1 }}
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-[15px] font-bold text-sungai-950">
                                {{ $item->jenisBangunan?->nama ?? 'Jenis tidak diketahui' }}
                            </h3>
                            <p class="mt-0.5 flex flex-wrap items-center gap-x-1.5 text-[12px] text-slate-400">
                                @if ($item->jenisBangunan?->kode)
                                    <span class="font-bold text-emas-600">{{ $item->jenisBangunan->kode }}</span>
                                @endif
                                @if ($item->nama_bangunan)
                                    <span>&middot;</span><span>{{ $item->nama_bangunan }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <x-lencana warna="netral">{{ $item->temuanKondisi->count() }} bagian diperiksa</x-lencana>
                </div>

                @if (count($fotoBangunan))
                    <div class="flex flex-wrap gap-2.5 border-t border-slate-100 px-5 py-4">
                        @foreach ($fotoBangunan as $foto)
                            <img src="{{ $foto }}" alt="Foto bangunan" loading="lazy"
                                 class="h-28 w-40 cursor-zoom-in rounded-xl object-cover ring-1 ring-slate-200 transition hover:opacity-90"
                                 onclick="bukaFoto(this.src)">
                        @endforeach
                    </div>
                @endif

                @if ($item->temuanKondisi->isEmpty())
                    <p class="border-t border-slate-100 px-5 py-8 text-center text-[13px] text-slate-400">
                        Tidak ada temuan tercatat pada bangunan ini.
                    </p>
                @else
                    <div class="divide-y divide-slate-100 border-t border-slate-100">
                        @foreach ($item->temuanKondisi as $temuan)
                            @php
                                $bermasalah = $temuan->adaMasalah();
                                $fotoTemuan = $temuan->semuaFoto();
                            @endphp
                            <div class="relative px-5 py-4 pl-6">
                                {{-- Penanda tepi: hijau bila normal, merah bila ada masalah --}}
                                <span class="absolute left-0 top-4 bottom-4 w-1 rounded-r-full {{ $bermasalah ? 'bg-bata-500' : 'bg-hijau-500' }}"></span>

                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <p class="text-[14px] font-bold text-slate-800">
                                        {{ $temuan->bagianBangunan?->nama ?? 'Bagian tidak diketahui' }}
                                    </p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($temuan->kondisiList() as $kondisi)
                                            <x-lencana :warna="strcasecmp($kondisi, 'Normal') === 0 ? 'normal' : 'masalah'">
                                                {{ $kondisi }}
                                            </x-lencana>
                                        @endforeach
                                    </div>
                                </div>

                                @if ($temuan->tindakan_diperlukan || $temuan->keterangan)
                                    <div class="mt-2.5 space-y-1 text-[13px] leading-relaxed">
                                        @if ($temuan->tindakan_diperlukan)
                                            <p class="text-slate-600">
                                                <span class="font-bold text-slate-700">Tindakan:</span>
                                                {{ $temuan->tindakan_diperlukan }}
                                            </p>
                                        @endif
                                        @if ($temuan->keterangan)
                                            <p class="text-slate-600">
                                                <span class="font-bold text-slate-700">Keterangan:</span>
                                                {{ $temuan->keterangan }}
                                            </p>
                                        @endif
                                    </div>
                                @endif

                                @if (count($fotoTemuan))
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($fotoTemuan as $foto)
                                            <img src="{{ $foto }}" alt="Foto temuan" loading="lazy"
                                                 class="h-24 w-32 cursor-zoom-in rounded-lg object-cover ring-1 ring-slate-200 transition hover:opacity-90"
                                                 onclick="bukaFoto(this.src)">
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl bg-white ring-1 ring-slate-200/70">
                <x-kosong judul="Laporan ini belum memiliki data bangunan"/>
            </div>
        @endforelse
    </div>

    {{-- Catatan --}}
    @if ($laporan->catatan_umum || $laporan->kendala)
        <div class="mt-3.5 grid gap-3.5 sm:grid-cols-2">
            @if ($laporan->catatan_umum)
                <div class="rounded-2xl bg-white p-5 ring-1 ring-slate-200/70">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Catatan Umum</p>
                    <p class="mt-2 whitespace-pre-line text-[13.5px] leading-relaxed text-slate-600">{{ $laporan->catatan_umum }}</p>
                </div>
            @endif
            @if ($laporan->kendala)
                <div class="rounded-2xl bg-emas-50 p-5 ring-1 ring-emas-500/20">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-emas-700/70">Kendala di Lapangan</p>
                    <p class="mt-2 whitespace-pre-line text-[13.5px] leading-relaxed text-emas-900/80">{{ $laporan->kendala }}</p>
                </div>
            @endif
        </div>
    @endif

    <p class="mt-4 text-center text-[12px] text-slate-400">
        Disinkronkan dari aplikasi juru pada {{ optional($laporan->synced_at)->format('d/m/Y H:i') ?? '-' }}
    </p>

    {{-- Pratinjau foto --}}
    <div id="lightbox" hidden class="fixed inset-0 z-50 items-center justify-center bg-sungai-950/90 p-6" onclick="tutupFoto()">
        <img id="lightbox-gambar" src="" alt="Pratinjau foto" class="max-h-full max-w-full rounded-2xl object-contain shadow-2xl">
    </div>

    <script>
        const kotakFoto = document.getElementById('lightbox');
        const gambarFoto = document.getElementById('lightbox-gambar');

        function bukaFoto(sumber) {
            gambarFoto.src = sumber;
            kotakFoto.hidden = false;
            kotakFoto.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function tutupFoto() {
            kotakFoto.hidden = true;
            kotakFoto.style.display = 'none';
            gambarFoto.src = '';
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !kotakFoto.hidden) tutupFoto();
        });
    </script>

@endsection
