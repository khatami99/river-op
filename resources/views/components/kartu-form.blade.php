@props(['judul', 'aksi', 'metode' => 'POST', 'batal', 'labelSimpan' => 'Simpan'])

<form method="POST" action="{{ $aksi }}" class="max-w-2xl">
    @csrf
    @if (strtoupper($metode) !== 'POST')
        @method($metode)
    @endif

    <div class="rounded-2xl bg-permukaan ring-1 ring-garis/70">
        <div class="border-b border-garis-halus px-5 py-4">
            <h2 class="text-sm font-bold text-judul">{{ $judul }}</h2>
        </div>

        <div class="space-y-4 px-5 py-5">
            {{ $slot }}
        </div>

        <div class="flex items-center gap-2 border-t border-garis-halus px-5 py-4">
            <button type="submit"
                    class="rounded-xl bg-aksi px-5 py-2.5 text-[13px] font-bold text-aksi-teks shadow-lg shadow-aksi/25 transition hover:bg-aksi-sorot">
                {{ $labelSimpan }}
            </button>
            <a href="{{ $batal }}"
               class="rounded-xl px-4 py-2.5 text-[13px] font-semibold text-teks-samar transition hover:bg-permukaan-sedang hover:text-teks-sedang">
                Batal
            </a>
        </div>
    </div>
</form>
