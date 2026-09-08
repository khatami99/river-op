@props(['kunci', 'urutan', 'kelas' => ''])

{{-- Kepala kolom yang bisa diklik untuk mengurutkan.

     Tetap berupa tautan sungguhan, bukan tombol dengan JavaScript, supaya
     halaman ini masih bisa diurutkan bila skripnya gagal dimuat. Saat skrip
     hidup, klik dicegat dan hanya area hasilnya yang dikirim ulang. --}}
@php
    $aktif = $urutan['kolom'] === $kunci;

    // Kolom yang belum aktif dimulai dari besar ke kecil: tanggal terbaru dan
    // bangunan terbanyak hampir selalu yang dicari lebih dulu.
    $arah = $aktif && $urutan['arah'] === 'desc' ? 'asc' : 'desc';

    $panah = $aktif
        ? ($urutan['arah'] === 'asc' ? 'M8 14l4-4 4 4' : 'M8 10l4 4 4-4')
        : 'M8 9l4-4 4 4M8 15l4 4 4-4';
@endphp

<th class="px-5 py-3 {{ $kelas }}"
    aria-sort="{{ $aktif ? ($urutan['arah'] === 'asc' ? 'ascending' : 'descending') : 'none' }}">
    <a href="{{ request()->fullUrlWithQuery(['urut' => $kunci, 'arah' => $arah, 'page' => null, 'fragmen' => null]) }}"
       data-urut="{{ $kunci }}" data-arah="{{ $arah }}"
       title="Urutkan menurut {{ strtolower($slot) }}"
       class="group inline-flex items-center gap-1 whitespace-nowrap transition hover:text-teks">
        {{ $slot }}
        <svg class="h-3.5 w-3.5 shrink-0 transition {{ $aktif ? 'text-info-teks' : 'text-teks-pudar group-hover:text-teks-samar' }}"
             fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $panah }}"/>
        </svg>
    </a>
</th>
