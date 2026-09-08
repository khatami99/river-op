{{-- Paginasi ringkas: hanya maju dan mundur, tanpa nomor halaman.
     Warnanya memakai token yang sama dengan paginasi penuh. --}}
@php
    $kelasTombol = 'relative inline-flex h-9 items-center justify-center rounded-lg px-3.5 text-[13px] font-semibold transition';
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between gap-2">
        @if ($paginator->onFirstPage())
            <span class="{{ $kelasTombol }} cursor-default text-teks-pudar" aria-disabled="true">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="{{ $kelasTombol }} text-teks-sedang hover:bg-permukaan-sedang hover:text-teks">Sebelumnya</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               class="{{ $kelasTombol }} text-teks-sedang hover:bg-permukaan-sedang hover:text-teks">Berikutnya</a>
        @else
            <span class="{{ $kelasTombol }} cursor-default text-teks-pudar" aria-disabled="true">Berikutnya</span>
        @endif
    </nav>
@endif
