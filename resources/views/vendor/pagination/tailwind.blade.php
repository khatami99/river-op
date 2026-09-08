{{-- Paginasi.

     Berkas bawaan Laravel diterbitkan dan ditulis ulang karena dua hal yang
     baru ketahuan setelah mode gelap dipasang: warnanya dipatok abu-abu
     harfiah sehingga tidak ikut berubah tema — tulisannya tinggal 1,97 : 1 di
     atas latar gelap — dan kalimatnya berbahasa Inggris, satu-satunya sudut
     aplikasi ini yang begitu.

     Kelas dark: bawaan sengaja dibuang seluruhnya. Kelas itu hanya menurut
     pada setelan perangkat, sehingga pilihan tema pengguna tidak akan
     dihiraukan justru di bagian ini saja. --}}
@php
    $kelasTombol = 'relative inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-[13px] font-semibold transition';
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-[13px] text-teks-samar">
            Menampilkan
            <span class="font-semibold text-teks">{{ $paginator->firstItem() }}</span>
            sampai
            <span class="font-semibold text-teks">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-teks">{{ $paginator->total() }}</span>
            data
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="{{ $kelasTombol }} cursor-default text-teks-pudar" aria-disabled="true">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="{{ $kelasTombol }} text-teks-sedang hover:bg-permukaan-sedang hover:text-teks">Sebelumnya</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="{{ $kelasTombol }} cursor-default text-teks-pudar">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="{{ $kelasTombol }} bg-aksi text-aksi-teks" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}"
                               class="{{ $kelasTombol }} text-teks-sedang hover:bg-permukaan-sedang hover:text-teks">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="{{ $kelasTombol }} text-teks-sedang hover:bg-permukaan-sedang hover:text-teks">Berikutnya</a>
            @else
                <span class="{{ $kelasTombol }} cursor-default text-teks-pudar" aria-disabled="true">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
