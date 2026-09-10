@props(['kembali' => null, 'labelKembali' => 'Kembali'])

{{-- Baris tindakan milik halaman: Tambah, Export, Blangko, Kembali.

     Dulu semuanya menempel di kop. Itu keliru, dan keliru dengan alasan yang
     sudah dipakai sendiri waktu memindahkan pemilih bulan keluar dari sana:
     kop bukan tempat kendali. Sejak akun dan sakelar tema pindah ke pojok
     kanan atas, kop menjawab satu pertanyaan saja — "aku sedang masuk sebagai
     siapa, dan sedang di halaman apa". Tombol Export duduk di sebelah nama
     pengguna mencampur identitas dengan tindakan, dua hal yang tidak ada
     hubungannya.

     Tempatnya sekarang di badan halaman, di atas isinya. Ia ikut menggulung
     bersama halaman, sebagaimana mestinya sesuatu yang berlaku hanya untuk
     halaman itu.

     Semuanya rata kiri, termasuk tombol tindakan. Rata kanan itu kebiasaan
     yang dipinjam dari kop kartu, dan di sana ia masuk akal karena ada judul
     di seberangnya yang menahan. Baris ini tidak punya seberang: isinya cuma
     tombol, sehingga rata kanan meninggalkannya terdampar di ujung layar,
     seribu piksel lebih dari tempat mata sedang membaca. Kolom isi mulai di
     kiri, judul di kiri, tabel di kiri — tombolnya ikut.

     Kembali dan tindakan tetap dipisah segaris tipis. Satu membawa keluar
     halaman, yang lain mengerjakan sesuatu di dalamnya, dan berdempetan tanpa
     jeda membuat "pergi" terbaca setara dengan "kerjakan". --}}
@php $adaTindakan = trim($slot) !== ''; @endphp

{{-- Tidak tergambar sama sekali bila kosong. Di halaman daftar, tombol
     Tambah dibungkus penjagaan peran, sehingga bagi akun peninjau slotnya
     hanya berisi spasi — dan wadah kosong pun tetap menyisakan jarak bawahnya
     sebagai celah hampa yang tak jelas asalnya. --}}
@if ($kembali || $adaTindakan)
<div {{ $attributes->merge(['class' => 'mb-4 flex flex-wrap items-center gap-2.5']) }}>
    @if ($kembali)
        <a href="{{ $kembali }}"
           class="inline-flex shrink-0 items-center gap-1.5 rounded-xl px-3 py-2 text-[13px] font-semibold text-teks-samar transition hover:bg-permukaan-sedang hover:text-teks-sedang">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ $labelKembali }}
        </a>

        @if ($adaTindakan)
            <span class="hidden h-7 w-px shrink-0 bg-garis sm:block"></span>
        @endif
    @endif

    @if ($adaTindakan)
        <div class="flex flex-wrap items-center gap-2.5">
            {{ $slot }}
        </div>
    @endif
</div>
@endif
