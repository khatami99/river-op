@props(['judul', 'aksi', 'metode' => 'POST', 'batal', 'labelSimpan' => 'Simpan'])

<form method="POST" action="{{ $aksi }}" class="max-w-2xl">
    @csrf
    @if (strtoupper($metode) !== 'POST')
        @method($metode)
    @endif

    <div class="rounded-2xl bg-white ring-1 ring-slate-200/70">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-bold text-sungai-950">{{ $judul }}</h2>
        </div>

        <div class="space-y-4 px-5 py-5">
            {{ $slot }}
        </div>

        <div class="flex items-center gap-2 border-t border-slate-100 px-5 py-4">
            <button type="submit"
                    class="rounded-xl bg-sungai-950 px-5 py-2.5 text-[13px] font-bold text-white shadow-lg shadow-sungai-950/15 transition hover:bg-sungai-900">
                {{ $labelSimpan }}
            </button>
            <a href="{{ $batal }}"
               class="rounded-xl px-4 py-2.5 text-[13px] font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                Batal
            </a>
        </div>
    </div>
</form>
