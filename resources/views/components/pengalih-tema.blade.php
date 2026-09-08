@props(['gaya' => 'rel'])

{{-- Pengalih tema.

     Tiga pilihan, bukan dua. Sakelar dua posisi memaksa pengguna memutuskan
     sekali untuk selamanya; dengan "Ikut sistem" ia bisa menyerahkannya lagi
     kepada perangkat — dan itulah keadaan bawaannya, sehingga aplikasi
     mengikuti kebiasaan yang sudah dipasang orang di HP atau laptopnya.

     Dua gaya, sebab tempatnya dua: menempel pada rel navigasi yang selalu
     gelap, dan di kepala halaman pada layar kecil yang mengikuti tema. Yang
     membedakan cuma warna; penanda pilihan aktif diserahkan ke CSS lewat
     kelas .tema-aktif supaya JavaScript tidak perlu tahu ia sedang berada di
     atas latar yang mana.

     Tanpa JavaScript tema tetap mengikuti setelan perangkat; yang hilang
     hanya kemampuan menimpanya. --}}
@php
    $diRel = $gaya === 'rel';

    $kelasWadah = $diRel
        ? 'bg-white/5 ring-white/10'
        : 'bg-permukaan-lembut ring-garis/70';

    $kelasTombol = $diRel
        ? 'text-sungai-200/70 hover:bg-white/10 hover:text-white'
        : 'text-teks-samar hover:bg-permukaan-sedang hover:text-teks';

    $pilihan = [
        'terang' => ['Terang', 'M12 3v1.5m0 15V21m9-9h-1.5m-15 0H3m15.4-6.4-1 1m-10.8 10.8-1 1m12.8 0-1-1M6.6 6.6l-1-1M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z'],
        'sistem' => ['Ikut sistem', 'M9 17H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-4m-6 0 .5 3m5.5-3-.5 3m-5 0h5'],
        'gelap'  => ['Gelap', 'M20.5 14.6A8.5 8.5 0 0 1 9.4 3.5a8.5 8.5 0 1 0 11.1 11.1z'],
    ];
@endphp

<div {{ $attributes->merge(['class' => "flex items-center gap-0.5 rounded-xl p-1 ring-1 {$kelasWadah}"]) }}
     data-pengalih-tema="{{ $gaya }}" role="group" aria-label="Tema tampilan">
    @foreach ($pilihan as $nilai => [$judul, $ikon])
        <button type="button" data-tema-pilihan="{{ $nilai }}" title="{{ $judul }}"
                aria-label="{{ $judul }}" aria-pressed="false"
                class="flex h-7 w-7 items-center justify-center rounded-lg transition {{ $kelasTombol }}">
            <svg class="h-[15px] w-[15px]" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}"/>
            </svg>
        </button>
    @endforeach
</div>
