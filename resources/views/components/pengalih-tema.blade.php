{{-- Sakelar tema, satu tombol, di kop halaman sebelah menu akun.

     Dulu tiga tombol berjajar: Terang, Ikut sistem, Gelap. Tiga tombol untuk
     satu setelan kenyamanan terlalu ramai untuk sesuatu yang dipakai sekali
     lalu dilupakan, dan tempatnya pun salah — menempel di bawah rel navigasi,
     jauh dari kendali pribadi lain yang semuanya berkumpul di kanan atas.

     Yang hilang dengan memerasnya jadi satu: "Ikut sistem" tidak bisa lagi
     dipilih kembali sesudah pengguna menentukan sendiri. Yang tidak hilang:
     "Ikut sistem" tetap keadaan bawaan, jadi selama pegawai tidak pernah
     menyentuh tombol ini, aplikasi tetap mengikuti setelan HP atau laptopnya
     seperti sebelumnya.

     Ikonnya menunjukkan TUJUAN, bukan keadaan sekarang: bulan berarti "pencet
     untuk menggelapkan". Mana yang tampil ditentukan CSS, bukan JavaScript,
     agar tidak berkedip salah sebelum skrip dimuat.

     Tanpa JavaScript tema tetap mengikuti setelan perangkat; yang hilang
     hanya kemampuan menimpanya. --}}
<button type="button" data-sakelar-tema
        {{ $attributes->merge(['class' => 'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-teks-samar ring-1 ring-garis/70 transition hover:bg-permukaan-lembut hover:text-teks']) }}
        title="Ganti tema tampilan" aria-label="Ganti tema tampilan">
    <svg class="ikon-bulan h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M20.5 14.6A8.5 8.5 0 0 1 9.4 3.5a8.5 8.5 0 1 0 11.1 11.1z"/>
    </svg>
    <svg class="ikon-matahari h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 3v1.5m0 15V21m9-9h-1.5m-15 0H3m15.4-6.4-1 1m-10.8 10.8-1 1m12.8 0-1-1M6.6 6.6l-1-1M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"/>
    </svg>
</button>
