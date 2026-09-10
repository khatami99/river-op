@props([
    'nama',
    'pilihan',
    'terpilih' => null,
    'kosong' => 'Pilih',
    'matikan' => false,
    'cari' => null,
])

{{-- Panel pilih banyak: satu kotak yang membuka, isinya kotak centang.

     Dipakai untuk kabupaten dan juru pada formulir DAS. Sebelumnya keduanya
     berbeda rupa di satu halaman yang sama — kabupaten sebagai panel centang,
     juru sebagai chip — dan tidak ada alasan sahnya. Aturannya sekarang:
     memilih dari daftar yang sudah ada memakai panel ini, sedangkan mengetik
     untuk membuat sesuatu yang belum ada tetap memakai chip. Yang terakhir
     cuma ada satu, yaitu kabupaten pada wilayah sungai.

     Kotak centang sungguhan, bukan tiruan: tanpa JavaScript panel ini masih
     bisa dibuka dan dicentang, dan nilainya tetap terkirim. Yang ditambahkan
     JavaScript hanya ringkasan pada kepalanya dan kotak pencariannya.

     Panelnya TIDAK menutup saat sesuatu dicentang — itu memang sifat
     <details>, dan di sini itulah yang benar: orang jarang memilih tepat satu
     lalu selesai. --}}
@php
    $terpilih = collect($terpilih ?? [])->map(fn ($v) => (string) $v);
    $pilihan = collect($pilihan);

    // Kotak pencarian baru berguna kalau daftarnya cukup panjang untuk tidak
    // habis dibaca sekali lihat. Di bawah itu ia cuma menambah satu benda
    // untuk diabaikan.
    $pakaiCari = $cari ?? $pilihan->count() > 6;

    $labelTerpilih = $pilihan
        ->filter(fn ($p) => $terpilih->contains((string) $p['nilai']))
        ->pluck('label');
@endphp

<details class="relative" data-panel-pilihan data-panel-pilih>
    <summary class="flex cursor-pointer select-none items-center justify-between gap-2 rounded-xl bg-permukaan-lembut px-3.5 py-2.5 text-[13.5px] ring-1 ring-inset ring-garis transition hover:bg-permukaan
                    list-none [&::-webkit-details-marker]:hidden">
        <span data-ringkasan class="truncate {{ $labelTerpilih->isEmpty() ? 'text-teks-samar' : 'text-teks-tegas' }}"
              data-kosong="{{ $kosong }}">
            {{ $labelTerpilih->isEmpty() ? $kosong : $labelTerpilih->join(', ') }}
        </span>
        <svg class="h-4 w-4 shrink-0 text-teks-samar" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
        </svg>
    </summary>

    <div class="absolute left-0 right-0 z-30 mt-2 rounded-2xl bg-permukaan p-1.5 shadow-lg shadow-sungai-950/10 ring-1 ring-garis">
        @if ($pakaiCari)
            <div class="p-1.5 pb-2">
                <input type="text" data-cari-panel autocomplete="off" placeholder="Ketik untuk menyaring&hellip;"
                       aria-label="Saring daftar"
                       class="w-full rounded-lg border-0 bg-permukaan-lembut px-3 py-2 text-[13px] text-teks-tegas ring-1 ring-inset ring-garis placeholder:text-teks-samar focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none">
            </div>
        @endif

        <div data-daftar class="max-h-60 overflow-auto scrollbar-halus">
            @foreach ($pilihan as $p)
                <label data-baris="{{ $p['label'] }}"
                       class="flex cursor-pointer items-center gap-2.5 rounded-xl px-3 py-2.5 text-[13.5px] text-teks-sedang transition hover:bg-permukaan-lembut">
                    <input type="checkbox" name="{{ $nama }}" value="{{ $p['nilai'] }}"
                           @checked($terpilih->contains((string) $p['nilai']))
                           @disabled($matikan)
                           class="h-4 w-4 shrink-0 rounded border-garis-kuat text-info-teks focus:ring-sungai-500/30">
                    <span class="truncate">{{ $p['label'] }}</span>

                    {{-- Keterangan seperti "kini di DAS Kusan": akibat memilihnya
                         harus terbaca sebelum dicentang, bukan sesudah tersimpan. --}}
                    @if (! empty($p['catatan']))
                        <span class="ml-auto shrink-0 pl-3 text-[12px] text-teks-samar">{{ $p['catatan'] }}</span>
                    @endif
                </label>
            @endforeach
        </div>

        <p data-kosong-cari hidden class="px-3 py-2.5 text-[13px] text-teks-samar">Tidak ada yang cocok.</p>
    </div>
</details>
