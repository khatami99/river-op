@props(['class' => 'h-9'])

{{-- Lambang Provinsi Kalimantan Selatan.
     Sengaja hanya dipakai di tempat lembaganya memperkenalkan diri — halaman
     masuk dan dokumen yang keluar dari sistem — bukan di tiap layar kerja.
     Menempelkan dua logo berdampingan di mana-mana justru melemahkan
     keduanya. --}}
<img src="{{ asset('gambar/logo-kalsel.png') }}"
     alt="Lambang Provinsi Kalimantan Selatan"
     {{ $attributes->merge(['class' => $class . ' w-auto']) }}>
