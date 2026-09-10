@extends('layouts.app')

@section('judul', 'Telaah Batas Wilayah')
@section('subjudul', $telaah['nama_berkas'])

@section('konten')

    <x-aksi-halaman :kembali="route('batas.index')" label-kembali="Batal, kembali"/>

    @php
        $jenisLabel = $telaah['jenis'] === 'das' ? 'DAS' : 'wilayah sungai';
        $cocokCount = $baris->whereNotNull('cocok')->count();
        $dipangkas = $baris->filter(fn ($b) => $b['titik_asli'] > $b['titik'])->count();
        $ki = 'w-full rounded-lg border-0 bg-permukaan-lembut px-3 py-1.5 text-[13px] text-teks-tegas ring-1 ring-inset ring-garis focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
    @endphp

    {{-- Ringkasan lebih dulu, sebelum tabelnya.

         Yang paling perlu diketahui bukan isi tiap baris melainkan berapa yang
         belum berpasangan — sebab baris tanpa pasangan akan dilewati diam-diam
         saat disimpan, dan itu harus diketahui sebelum tombol simpan ditekan,
         bukan sesudahnya. --}}
    <div class="rounded-2xl bg-permukaan p-5 ring-1 ring-garis/70">
        <p class="text-[13.5px] leading-relaxed text-teks-sedang">
            Berkas berisi <strong class="font-bold text-teks-tegas">{{ $baris->count() }} batas wilayah</strong>.
            @if ($cocokCount === $baris->count())
                Semuanya menemukan pasangan {{ $jenisLabel }} di sistem ini.
            @else
                <strong class="font-bold text-teks-tegas">{{ $cocokCount }}</strong> menemukan pasangan;
                sisanya perlu dipilihkan sendiri, atau dibiarkan kosong untuk dilewati.
            @endif
        </p>

        @if ($dipangkas > 0)
            <p class="mt-2 text-[12.5px] leading-relaxed text-teks-samar">
                {{ $dipangkas }} batas disederhanakan agar peta tidak berat. Bentuknya tetap,
                yang berkurang hanya kerapatan titiknya — perbedaannya tidak terlihat pada peta.
            </p>
        @endif
    </div>

    <form method="GET" action="{{ route('batas.pratinjau', $tanda) }}"
          class="mt-4 rounded-2xl bg-permukaan p-4 ring-1 ring-garis/70">
        <label for="kolom_nama" class="mb-1.5 block text-[12px] font-bold uppercase tracking-wider text-teks-lembut">
            Nama wilayah diambil dari kolom
        </label>
        <div class="flex flex-wrap items-center gap-2">
            <select id="kolom_nama" name="kolom_nama" class="{{ $ki }} max-w-xs" onchange="this.form.submit()">
                @foreach ($kolom as $k)
                    <option value="{{ $k }}" @selected($k === $kolomNama)>{{ $k }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-cari px-4 py-2 text-[13px] font-bold text-cari-teks transition hover:bg-cari-sorot">
                Terapkan
            </button>
        </div>
        <p class="mt-2 text-[12px] leading-relaxed text-teks-samar">
            Kolom yang paling banyak cocok dipilih otomatis. Ganti bila nama wilayahnya
            ternyata tersimpan di kolom lain.
        </p>
    </form>

    <form method="POST" action="{{ route('batas.simpan', $tanda) }}" class="mt-4">
        @csrf

        <div class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
            <div class="max-h-[60vh] overflow-auto scrollbar-halus">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="sticky top-0 z-10 bg-permukaan-lembut text-[12px] font-bold uppercase tracking-wider text-teks-lembut">
                            <th class="border-b border-garis-halus px-5 py-3">Nama di berkas</th>
                            <th class="border-b border-garis-halus px-5 py-3">Titik</th>
                            <th class="border-b border-garis-halus px-5 py-3">Dipasangkan ke {{ $jenisLabel }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($baris as $b)
                            <tr class="border-b border-garis-halus text-[13px] last:border-0">
                                <td class="px-5 py-2.5 font-semibold text-teks">{{ $b['nama'] }}</td>
                                <td class="px-5 py-2.5 tabular-nums text-teks-samar">
                                    {{ number_format($b['titik']) }}
                                    @if ($b['titik_asli'] > $b['titik'])
                                        <span class="text-[11px]">(dari {{ number_format($b['titik_asli']) }})</span>
                                    @endif
                                </td>
                                <td class="px-5 py-2">
                                    <select name="cocok[{{ $b['indeks'] }}]" class="{{ $ki }}"
                                            data-pilihan-cari="Ketik untuk mencari&hellip;">
                                        <option value="">— lewati, jangan simpan —</option>
                                        @foreach ($tujuan as $t)
                                            <option value="{{ $t->id }}" @selected($b['cocok'] === $t->id)>{{ $t->label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-garis-halus bg-permukaan-lembut px-5 py-4">
                <p class="text-[12.5px] text-teks-samar">
                    Batas yang sudah ada pada {{ $jenisLabel }} yang sama akan ditimpa.
                </p>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-aksi px-4 py-2.5 text-[13px] font-bold text-aksi-teks shadow-lg shadow-aksi/25 transition hover:bg-aksi-sorot">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/>
                    </svg>
                    Simpan Batas Wilayah
                </button>
            </div>
        </div>
    </form>

@endsection
