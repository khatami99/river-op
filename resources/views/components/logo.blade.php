@props(['class' => 'h-9'])

{{-- Logo aplikasi: atap rumah Banjar di atas gelombang sungai.
     Latarnya tembus pandang, jadi garis putih di dalamnya ikut warna latar —
     biru tua di sidebar, putih di halaman terang. Bentuknya lebih tinggi
     daripada lebar, sehingga lebarnya dibiarkan menyesuaikan sendiri. --}}
<img src="{{ asset('gambar/logo-riverop.png') }}"
     alt="Logo Sistem Informasi OP Sungai"
     {{ $attributes->merge(['class' => $class . ' w-auto']) }}>
