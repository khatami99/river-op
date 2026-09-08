@extends('layouts.app')

@use('App\Support\Bulan')

@section('judul', 'Detail Laporan OP')
@section('subjudul', $laporan->juruSungai?->nama . ' · ' . optional($laporan->tanggal_inspeksi)->format('d F Y'))

@section('aksi-header')
    <div class="flex shrink-0 gap-2">
        <a href="{{ route('laporan.blangko', $laporan) }}" data-unduh
           class="inline-flex items-center gap-2 rounded-xl bg-sungai-950 px-3.5 py-2.5 text-[13px] font-bold text-white transition hover:bg-sungai-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            Blangko Excel
        </a>
        <a href="{{ route('laporan.blangko.pdf', $laporan) }}" data-unduh
           class="inline-flex items-center gap-2 rounded-xl bg-bata-600 px-3.5 py-2.5 text-[13px] font-bold text-white transition hover:bg-bata-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M7 3h7l5 5v11a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
            </svg>
            Blangko PDF
        </a>
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

@push('kepala')
    @vite('resources/js/peta.js')
@endpush

@section('konten')

    @php
        $semuaTemuan = $laporan->itemInspeksi->flatMap->temuanKondisi;
        $jumlahBermasalah = $semuaTemuan->filter(fn ($t) => $t->adaMasalah())->count();
        $fotoLokasi = array_values(array_filter([$laporan->foto_lokasi_1, $laporan->foto_lokasi_2]));

        // Titik bangunan untuk peta. Laporan lama dari sebelum aplikasi juru
        // menangkap GPS tidak punya koordinat, jadi petanya tidak ditampilkan.
        $titikBangunan = collect();
        foreach ($laporan->itemInspeksi as $urutan => $bangunan) {
            if ($bangunan->koordinat_lat !== null && $bangunan->koordinat_lng !== null) {
                $titikBangunan->push([
                    'nomor' => $urutan + 1,
                    'nama' => $bangunan->jenisBangunan?->nama ?? 'Bangunan',
                    'label' => $bangunan->nama_bangunan,
                    'lat' => (float) $bangunan->koordinat_lat,
                    'lng' => (float) $bangunan->koordinat_lng,
                    'bermasalah' => $bangunan->temuanKondisi->contains(fn ($t) => $t->adaMasalah()),
                ]);
            }
        }
    @endphp

    {{-- Ringkasan atas --}}
    <div class="grid gap-3.5 lg:grid-cols-3">

        <div class="rounded-2xl bg-sungai-950 p-5 lg:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-[12px] font-bold uppercase tracking-wider text-emas-400">Identitas Laporan</p>
                    <p class="mt-1.5 text-xl font-extrabold text-white">{{ $laporan->juruSungai?->nama ?? '-' }}</p>
                    <p class="text-[13px] text-sungai-200/70">
                        {{ $laporan->das?->nama_das ?? '-' }}
                        @if ($laporan->das && $laporan->das->kabupaten->isNotEmpty())
                            &middot; {{ $laporan->das->labelKabupaten() }}
                        @endif
                    </p>
                </div>
                <div class="rounded-xl bg-white/5 px-3.5 py-2 ring-1 ring-white/10">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-sungai-300/80">Periode</p>
                    <p class="text-[13px] font-bold text-white">{{ Bulan::nama($laporan->bulan) }} {{ $laporan->tahun }}</p>
                </div>
            </div>

            <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3.5 border-t border-white/10 pt-4 sm:grid-cols-4">
                @php
                    $ringkas = [
                        'Telepon Juru' => $laporan->juruSungai?->no_telepon ?: '-',
                        'Tanggal Inspeksi' => optional($laporan->tanggal_inspeksi)->format('d M Y') ?? '-',
                        'Cuaca' => ucfirst($laporan->cuaca ?? '-'),
                        'Debit Air' => ucfirst($laporan->debit_air ?? '-'),
                    ];
                @endphp
                @foreach ($ringkas as $label => $nilai)
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-sungai-300/80">{{ $label }}</dt>
                        <dd class="mt-1 text-[13.5px] font-semibold text-white">{{ $nilai }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- Angka kunci --}}
        <div class="grid grid-cols-2 gap-3.5 lg:grid-cols-1">
            <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-200/70">
                <p class="text-[12px] font-bold uppercase tracking-wider text-slate-600">Bangunan Diperiksa</p>
                <p class="mt-1.5 text-3xl font-extrabold tabular-nums text-sungai-950">{{ $laporan->itemInspeksi->count() }}</p>
            </div>
            <div class="rounded-2xl p-4 ring-1 {{ $jumlahBermasalah > 0 ? 'bg-bata-50 ring-bata-500/20' : 'bg-hijau-50 ring-hijau-500/20' }}">
                <p class="text-[12px] font-bold uppercase tracking-wider {{ $jumlahBermasalah > 0 ? 'text-bata-700' : 'text-hijau-700' }}">
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
            <p class="mb-3 text-[12px] font-bold uppercase tracking-wider text-slate-600">Foto Lokasi</p>
            <div class="flex flex-wrap gap-3">
                @foreach ($fotoLokasi as $foto)
                    <img src="{{ $foto }}" alt="Foto lokasi" loading="lazy"
                         class="h-32 w-44 cursor-zoom-in rounded-xl object-cover ring-1 ring-slate-200 transition hover:opacity-90"
                         onclick="bukaFoto(this.src)">
                @endforeach
            </div>
        </div>
    @endif

    {{-- Peta lokasi bangunan --}}
    @if ($titikBangunan->isNotEmpty())
        <div class="mt-3.5 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
            <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-4">
                <h2 class="text-sm font-bold text-sungai-950">Lokasi Bangunan</h2>
                <span class="text-[12px] text-slate-500">
                    {{ $titikBangunan->count() }} dari {{ $laporan->itemInspeksi->count() }} bangunan bertitik
                </span>
            </div>
            <div id="peta-laporan" class="relative h-[clamp(16rem,38vh,24rem)] w-full border-t border-slate-100"
                 data-titik="{{ $titikBangunan->toJson() }}"></div>
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
                            <p class="mt-0.5 flex flex-wrap items-center gap-x-1.5 text-[12px] text-slate-500">
                                @if ($item->jenisBangunan?->kode)
                                    <span class="font-bold text-emas-600">{{ $item->jenisBangunan->kode }}</span>
                                @endif
                                @if ($item->nama_bangunan)
                                    <span>&middot;</span><span>{{ $item->nama_bangunan }}</span>
                                @endif
                            </p>
                            {{-- Baris lokasi sesuai blangko OP: nama sungai dan ruas patok kilometer. --}}
                            @if ($item->nama_sungai || $item->lokasi_km_dari !== null)
                                <p class="mt-1 text-[12px] text-slate-500">
                                    @if ($item->nama_sungai)
                                        Sungai {{ preg_replace('/^sungai\s+/i', '', trim($item->nama_sungai)) }}
                                    @endif
                                    @if ($item->lokasi_km_dari !== null)
                                        <span class="tabular-nums">
                                            Km {{ rtrim(rtrim(number_format($item->lokasi_km_dari, 3, ',', '.'), '0'), ',') }}
                                            @if ($item->lokasi_km_sampai !== null)
                                                s/d Km {{ rtrim(rtrim(number_format($item->lokasi_km_sampai, 3, ',', '.'), '0'), ',') }}
                                            @endif
                                        </span>
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($item->koordinat_lat && $item->koordinat_lng)
                            <a href="https://www.google.com/maps?q={{ $item->koordinat_lat }},{{ $item->koordinat_lng }}"
                               target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 rounded-full bg-sungai-50 px-2.5 py-0.5 text-xs font-semibold text-sungai-700 ring-1 ring-inset ring-sungai-500/20 transition hover:bg-sungai-100">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.7 16.7 12 22l-5.7-5.3a8 8 0 1111.4 0z"/>
                                    <circle cx="12" cy="11" r="2.5"/>
                                </svg>
                                {{ number_format($item->koordinat_lat, 5) }}, {{ number_format($item->koordinat_lng, 5) }}
                            </a>
                        @endif
                        <x-lencana warna="netral">{{ $item->temuanKondisi->count() }} bagian diperiksa</x-lencana>
                    </div>
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
                    <p class="border-t border-slate-100 px-5 py-8 text-center text-[13px] text-slate-500">
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

                                @if ($temuan->koordinat_lat && $temuan->koordinat_lng)
                                    <a href="https://www.google.com/maps?q={{ $temuan->koordinat_lat }},{{ $temuan->koordinat_lng }}"
                                       target="_blank" rel="noopener noreferrer"
                                       class="mt-2 inline-flex items-center gap-1.5 text-[12px] font-semibold text-sungai-600 transition hover:text-sungai-800">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.7 16.7 12 22l-5.7-5.3a8 8 0 1111.4 0z"/>
                                            <circle cx="12" cy="11" r="2.5"/>
                                        </svg>
                                        {{ number_format($temuan->koordinat_lat, 5) }}, {{ number_format($temuan->koordinat_lng, 5) }}
                                    </a>
                                @endif

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
                    <p class="text-[12px] font-bold uppercase tracking-wider text-slate-600">Catatan Umum</p>
                    <p class="mt-2 whitespace-pre-line text-[13.5px] leading-relaxed text-slate-600">{{ $laporan->catatan_umum }}</p>
                </div>
            @endif
            @if ($laporan->kendala)
                <div class="rounded-2xl bg-emas-50 p-5 ring-1 ring-emas-500/20">
                    <p class="text-[12px] font-bold uppercase tracking-wider text-emas-800">Kendala di Lapangan</p>
                    <p class="mt-2 whitespace-pre-line text-[13.5px] leading-relaxed text-emas-900">{{ $laporan->kendala }}</p>
                </div>
            @endif
        </div>
    @endif

    <p class="mt-4 text-center text-[12px] text-slate-500">
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
