@props(['judul' => 'Belum ada data', 'pesan' => null])

<div class="flex flex-col items-center justify-center px-6 py-16 text-center">
    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-permukaan-sedang">
        <svg class="h-6 w-6 text-teks-samar" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-4l-1.5 2h-5L8 13H4"/>
        </svg>
    </div>
    <p class="text-sm font-semibold text-teks-sedang">{{ $judul }}</p>
    @if ($pesan)
        <p class="mt-1 max-w-xs text-sm text-teks-samar">{{ $pesan }}</p>
    @endif
</div>
