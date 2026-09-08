@props(['staf'])

{{-- Akun yang sedang masuk, di sudut kanan atas.

     Sebelumnya ini tinggal di dalam rel navigasi — yang menghilang di layar
     di bawah 1024px. Akibatnya di HP dan tablet tidak ada cara melihat sedang
     masuk sebagai siapa, dan tidak ada cara keluar sama sekali. Untuk
     aplikasi yang dibuka di komputer bersama, itu bukan sekadar merepotkan:
     pegawai berikutnya duduk dan langsung memakai akun orang sebelumnya.

     Kop halaman satu-satunya bagian yang tampil di semua ukuran layar.

     Tanpa foto, sebab staf_dinas memang tidak menyimpannya — dan di panel
     admin orang tidak sedang mengenali wajah siapa pun, cuma memastikan "ini
     akun saya". Nama dan username sudah menjawab itu. --}}
@php
    $peran = [
        'admin' => ['Administrator', 'sungai'],
        'viewer' => ['Peninjau', 'netral'],
    ][$staf->role ?? ''] ?? [ucfirst($staf->role ?? '-'), 'netral'];
@endphp

<details class="relative shrink-0" data-panel-pilihan>
    <summary class="flex cursor-pointer select-none items-center gap-2 rounded-xl py-1.5 pl-1.5 pr-2 ring-1 ring-garis/70 transition hover:bg-permukaan-lembut
                    list-none [&::-webkit-details-marker]:hidden">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emas-400 text-[13px] font-extrabold text-emas-900">
            {{ strtoupper(mb_substr($staf->nama ?? 'S', 0, 1)) }}
        </span>
        <span class="hidden min-w-0 text-left leading-tight sm:block">
            <span class="block max-w-[10rem] truncate text-[13px] font-bold text-teks">{{ $staf->nama ?? '-' }}</span>
            <span class="block text-[11px] text-teks-samar">{{ $peran[0] }}</span>
        </span>
        <svg class="h-4 w-4 shrink-0 text-teks-samar" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
        </svg>
    </summary>

    <div class="absolute right-0 z-40 mt-2 w-64 rounded-2xl bg-permukaan p-1.5 shadow-lg shadow-sungai-950/10 ring-1 ring-garis">
        <div class="px-3 py-2.5">
            <p class="truncate text-sm font-bold text-teks">{{ $staf->nama ?? '-' }}</p>

            {{-- Username, bukan nomor baris basis data: inilah yang pegawai
                 ketik saat masuk, jadi inilah yang menjawab "akun siapa ini". --}}
            <p class="mt-0.5 truncate text-[12px] text-teks-samar">{{ $staf->username ?? '-' }}</p>

            <div class="mt-2">
                <x-lencana :warna="$peran[1]">{{ $peran[0] }}</x-lencana>
            </div>
        </div>

        <div class="my-1 h-px bg-garis-halus"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-[13px] font-semibold text-bahaya-teks transition hover:bg-bahaya-latar">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Keluar
            </button>
        </form>
    </div>
</details>
