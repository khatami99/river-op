@props(['class' => 'h-9 w-9'])

{{-- Atap rumah Banjar (emas) di atas gelombang sungai (biru) --}}
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M24 6.5 41 23.5h-7.4L24 13.9l-9.6 9.6H7L24 6.5Z" fill="currentColor" class="text-emas-400"/>
    <path d="M15.2 9.4 18 12.2M32.8 9.4 30 12.2" stroke="currentColor" class="text-emas-300"
          stroke-width="2.2" stroke-linecap="round"/>
    <path d="M7 31.2c3.4-2.8 6.8-2.8 10.2 0s6.8 2.8 10.2 0 6.8-2.8 10.2 0"
          stroke="currentColor" class="text-sungai-400" stroke-width="3.4" stroke-linecap="round"/>
    <path d="M7 38.4c3.4-2.8 6.8-2.8 10.2 0s6.8 2.8 10.2 0 6.8-2.8 10.2 0"
          stroke="currentColor" class="text-sungai-600" stroke-width="3.4" stroke-linecap="round"/>
</svg>
