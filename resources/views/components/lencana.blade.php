@props(['warna' => 'netral'])

@php
    $gaya = [
        'normal'  => 'bg-hijau-50 text-hijau-700 ring-hijau-500/20',
        'masalah' => 'bg-bata-50 text-bata-700 ring-bata-500/20',
        'emas'    => 'bg-emas-50 text-emas-800 ring-emas-500/25',
        'sungai'  => 'bg-sungai-50 text-sungai-700 ring-sungai-500/20',
        'netral'  => 'bg-slate-100 text-slate-600 ring-slate-400/20',
    ][$warna] ?? 'bg-slate-100 text-slate-600 ring-slate-400/20';
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset $gaya",
]) }}>
    {{ $slot }}
</span>
