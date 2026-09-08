@extends('layouts.app')

@php
    $sudah = $daftarDas->filter(fn ($d) => $d->kabupaten->isNotEmpty())->count();
@endphp

@section('judul', 'Kabupaten per DAS')
@section('subjudul', $sudah . ' dari ' . $daftarDas->count() . ' DAS sudah punya kabupaten')

@section('aksi-header')
    <a href="{{ route('das.index') }}"
       class="inline-flex items-center gap-1.5 rounded-lg px-2 py-2 text-[13px] font-semibold text-teks-samar transition hover:bg-permukaan-sedang hover:text-teks-sedang">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali
    </a>
@endsection

@section('konten')

    {{-- Catatan ini menjelaskan akibat, bukan sekadar memberi tahu cara
         pakai: kolom kabupaten ikut tercetak pada blangko yang diserahkan ke
         dinas, jadi yang dikosongkan di sini akan muncul sebagai kolom
         melompong di dokumen resmi. --}}
    <div class="mb-3.5 flex items-start gap-3 rounded-2xl bg-catatan-latar p-4 ring-1 ring-catatan-teks/20">
        <svg class="mt-0.5 h-4 w-4 shrink-0 text-catatan-teks" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-[13px] leading-relaxed text-catatan-teks-pekat">
            Kabupaten ikut tercetak pada blangko OP yang diserahkan ke dinas.
            <span class="font-semibold">Satu DAS boleh dicentang lebih dari satu kabupaten</span>
            bila wilayahnya membentang di keduanya.
        </p>
    </div>

    <form method="POST" action="{{ route('das.kabupaten.simpan') }}"
          class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
        @csrf

        <div class="max-h-[64vh] overflow-auto scrollbar-halus">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-[12px] font-bold uppercase tracking-wider text-teks-lembut">
                        @php $kepala = 'sticky top-0 z-10 border-b border-garis-halus bg-permukaan-lembut px-5 py-3'; @endphp
                        <th class="{{ $kepala }}">DAS</th>
                        @foreach ($daftarKabupaten as $kab)
                            <th class="{{ $kepala }} text-center">{{ $kab->nama }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis-halus text-[13.5px]">
                    @foreach ($daftarDas as $das)
                        @php $terpilih = $das->kabupaten->pluck('id')->all(); @endphp
                        <tr class="transition hover:bg-permukaan-lembut">
                            <td class="px-5 py-2.5">
                                <span class="font-semibold text-teks">{{ $das->nama_das }}</span>
                                <span class="ml-1 text-[12px] text-teks-samar">{{ $das->kode_das }}</span>
                                @if (empty($terpilih))
                                    <span class="ml-1.5 inline-block rounded-md bg-permukaan-sedang px-1.5 py-0.5 text-[11px] font-semibold text-teks-lembut">
                                        belum diisi
                                    </span>
                                @endif
                            </td>
                            @foreach ($daftarKabupaten as $kab)
                                <td class="px-5 py-2.5 text-center">
                                    {{-- Seluruh sel dijadikan sasaran klik: mengarahkan
                                         penunjuk ke kotak 16px sebanyak 62 baris itu
                                         melelahkan tanpa alasan. --}}
                                    <label class="flex cursor-pointer items-center justify-center py-1">
                                        <input type="checkbox"
                                               name="kabupaten[{{ $das->id }}][]"
                                               value="{{ $kab->id }}"
                                               @checked(in_array($kab->id, $terpilih, true))
                                               class="h-4 w-4 rounded border-garis-kuat text-info-teks focus:ring-sungai-500/30">
                                    </label>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Tombol simpan menempel di dasar: dengan 62 baris, tombol yang
             hanyut ke bawah berarti menggulung seluruh daftar hanya untuk
             menyimpan. --}}
        <div class="flex items-center justify-between gap-3 border-t border-garis bg-permukaan-lembut px-5 py-3.5">
            <p class="text-[13px] text-teks-samar">
                Perubahan baru tersimpan setelah tombol ini ditekan.
            </p>
            <button type="submit"
                    class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-aksi px-4 py-2.5 text-[13px] font-bold text-aksi-teks shadow-lg shadow-aksi/25 transition hover:bg-aksi-sorot">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan
            </button>
        </div>
    </form>

@endsection
