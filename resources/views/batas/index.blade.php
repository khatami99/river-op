@extends('layouts.app')

@section('judul', 'Batas Wilayah')
@section('subjudul', 'Memasukkan batas DAS dan wilayah sungai dari berkas BWS')

@section('konten')

    @php
        $ki = 'w-full rounded-xl border-0 bg-permukaan-lembut px-3.5 py-2.5 text-[13.5px] text-teks-tegas ring-1 ring-inset ring-garis transition file:mr-3 file:rounded-lg file:border-0 file:bg-permukaan-sedang file:px-3 file:py-1.5 file:text-[12.5px] file:font-bold file:text-teks-sedang focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
        $sudahDas = $das->where('punya', true)->count();
        $sudahWs = $wilayah->where('punya', true)->count();
    @endphp

    @error('berkas')
        <div class="mb-4 rounded-2xl bg-bahaya-latar px-4 py-3.5 text-[13px] font-medium leading-relaxed text-bahaya-teks ring-1 ring-bahaya-teks/25">
            {{ $message }}
        </div>
    @enderror

    <div class="grid gap-4 lg:grid-cols-5">

        {{-- Unggah --}}
        <div class="lg:col-span-3">
            <form method="POST" action="{{ route('batas.telaah') }}" enctype="multipart/form-data"
                  class="rounded-2xl bg-permukaan p-5 ring-1 ring-garis/70">
                @csrf

                <h2 class="text-[15px] font-bold text-judul">Unggah Berkas Batas</h2>
                <p class="mt-1 text-[13px] leading-relaxed text-teks-lembut">
                    Berkas dibaca dan ditampilkan lebih dulu. Tidak ada yang tersimpan
                    sebelum pasangannya dibenarkan pada langkah berikutnya.
                </p>

                <div class="mt-5">
                    <label class="mb-1.5 block text-[13px] font-semibold text-teks-sedang">Batas ini untuk</label>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach (['das' => 'Daerah Aliran Sungai', 'wilayah' => 'Wilayah Sungai'] as $nilai => $label)
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-xl bg-permukaan-lembut px-3.5 py-2.5 text-[13.5px] text-teks-sedang ring-1 ring-inset ring-garis transition hover:bg-permukaan hover:ring-info-teks/40">
                                <input type="radio" name="jenis" value="{{ $nilai }}"
                                       @checked(old('jenis', 'das') === $nilai)
                                       class="h-4 w-4 border-garis-kuat text-info-teks focus:ring-sungai-500/30">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    <label for="berkas" class="mb-1.5 block text-[13px] font-semibold text-teks-sedang">Berkas</label>
                    <input id="berkas" name="berkas" type="file" accept=".zip,.geojson,.json" required class="{{ $ki }}">
                    <p class="mt-1.5 text-[12px] text-teks-samar">
                        Shapefile dikompres jadi satu <strong class="font-semibold text-teks-lembut">.zip</strong>,
                        atau berkas <strong class="font-semibold text-teks-lembut">.geojson</strong>. Maksimal 50 MB.
                    </p>
                </div>

                <div class="mt-5 border-t border-garis-halus pt-4">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-aksi px-4 py-2.5 text-[13px] font-bold text-aksi-teks shadow-lg shadow-aksi/25 transition hover:bg-aksi-sorot">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4 4 4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                        </svg>
                        Baca Berkas
                    </button>
                </div>
            </form>
        </div>

        {{-- Panduan.

             Ditulis untuk dibaca orang yang belum pernah menyentuh berkas peta
             dan tidak punya siapa pun untuk ditanyai. Karena itu isinya bukan
             penjelasan istilah, melainkan kalimat yang bisa langsung dikirim ke
             BWS dan langkah yang bisa langsung diikuti. --}}
        <div class="lg:col-span-2">
            <div class="rounded-2xl bg-catatan-latar p-5 ring-1 ring-catatan-teks/20">
                <h2 class="text-[14px] font-bold text-catatan-teks-pekat">Belum punya berkasnya?</h2>

                <p class="mt-2 text-[12.5px] leading-relaxed text-catatan-teks-pekat">
                    Batas wilayah tidak dibuat sendiri — ia diminta dari instansi yang
                    berwenang, lalu diunggah di sini. Yang perlu diminta:
                </p>

                <div class="mt-3 rounded-xl bg-permukaan/60 p-3 text-[12.5px] leading-relaxed text-teks-sedang ring-1 ring-catatan-teks/15">
                    “Mohon data spasial batas Daerah Aliran Sungai dan Wilayah Sungai
                    di Provinsi Kalimantan Selatan dalam format <strong>shapefile</strong>
                    atau <strong>GeoJSON</strong>, dengan sistem koordinat
                    <strong>WGS 84 (EPSG:4326)</strong>.”
                </div>

                <p class="mt-3 text-[12.5px] leading-relaxed text-catatan-teks-pekat">
                    Dikirimkan ke salah satu dari: <strong>BWS Kalimantan III</strong>,
                    <strong>BPDAS Barito</strong>, atau diunduh sendiri dari
                    <strong>Ina-Geoportal</strong> milik Badan Informasi Geospasial.
                </p>

                <div class="mt-4 border-t border-catatan-teks/20 pt-3">
                    <h3 class="text-[13px] font-bold text-catatan-teks-pekat">Kalau berkasnya ditolak</h3>
                    <p class="mt-1.5 text-[12.5px] leading-relaxed text-catatan-teks-pekat">
                        Pesan penolakannya menyebutkan sebabnya. Yang paling sering:
                        koordinatnya belum WGS 84. Perbaikannya sekali jalan dengan
                        <strong>QGIS</strong> (gratis, qgis.org):
                    </p>
                    <ol class="mt-2 list-decimal space-y-1 pl-4 text-[12.5px] leading-relaxed text-catatan-teks-pekat">
                        <li>Buka berkas .shp-nya di QGIS.</li>
                        <li>Klik kanan namanya di daftar layer, pilih <em>Export → Save Features As</em>.</li>
                        <li>Format: <strong>GeoJSON</strong>. CRS: <strong>EPSG:4326 - WGS 84</strong>.</li>
                        <li>Simpan, lalu unggah berkas hasilnya di sini.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Keadaan sekarang --}}
    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        @foreach ([
            ['judul' => 'Daerah Aliran Sungai', 'data' => $das, 'jenis' => 'das', 'sudah' => $sudahDas, 'nama' => 'nama_das', 'kode' => 'kode_das'],
            ['judul' => 'Wilayah Sungai', 'data' => $wilayah, 'jenis' => 'wilayah', 'sudah' => $sudahWs, 'nama' => 'nama_sungai', 'kode' => 'kode_sungai'],
        ] as $bagian)
            <div class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
                <div class="flex items-center justify-between gap-3 border-b border-garis-halus px-5 py-3.5">
                    <h2 class="text-[14px] font-bold text-judul">{{ $bagian['judul'] }}</h2>
                    <span class="text-[12.5px] font-semibold {{ $bagian['sudah'] > 0 ? 'text-aman-teks' : 'text-teks-samar' }}">
                        {{ $bagian['sudah'] }} dari {{ $bagian['data']->count() }} sudah berbatas
                    </span>
                </div>

                @if ($bagian['sudah'] === 0)
                    <p class="px-5 py-6 text-center text-[13px] text-teks-samar">
                        Belum ada batas wilayah yang dimasukkan.
                    </p>
                @else
                    <div class="max-h-72 overflow-auto scrollbar-halus">
                        @foreach ($bagian['data']->where('punya', true) as $baris)
                            <div class="flex items-center justify-between gap-3 border-b border-garis-halus px-5 py-2.5 last:border-0">
                                <div class="min-w-0">
                                    <p class="truncate text-[13px] font-semibold text-teks">{{ $baris->{$bagian['nama']} }}</p>
                                    <p class="text-[12px] text-teks-samar">
                                        {{ $baris->{$bagian['kode']} }}
                                        @if ($baris->poligon_diperbarui_at)
                                            &middot; dimasukkan {{ $baris->poligon_diperbarui_at->format('d/m/Y') }}
                                        @endif
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('batas.hapus', [$bagian['jenis'], $baris->id]) }}"
                                      onsubmit="return confirm('Hapus batas wilayah ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="shrink-0 rounded-lg px-2.5 py-1.5 text-[12px] font-bold text-bahaya-teks transition hover:bg-bahaya-latar">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

@endsection
