@props(['warna' => 'netral'])

@php
    $gaya = [
        'normal'  => 'bg-aman-latar text-aman-teks ring-aman-teks/25',
        'masalah' => 'bg-bahaya-latar text-bahaya-teks ring-bahaya-teks/25',
        'emas'    => 'bg-peringatan-latar text-peringatan-teks ring-peringatan-teks/25',
        'sungai'  => 'bg-info-latar text-info-teks ring-info-teks/25',
        'netral'  => 'bg-permukaan-sedang text-teks-lembut ring-garis-kuat/40',
    ][$warna] ?? 'bg-permukaan-sedang text-teks-lembut ring-garis-kuat/40';
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset $gaya",
]) }}>
    {{ $slot }}
</span>
