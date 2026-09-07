@use('App\Support\Bulan')

{{-- Area hasil: tabel atau peta.

     Dipisah dari halamannya supaya bisa dikirim ulang sendirian ketika
     penyaring berubah, tanpa memuat ulang seluruh halaman. Itulah yang membuat
     hasilnya bisa berubah sambil pengguna mengetik tanpa kehilangan kursor. --}}
    @if ($tampilan === 'peta')

        @if ($tanpaTitik > 0)
            <div class="mt-3.5 flex items-start gap-3 rounded-2xl bg-emas-50 p-4 ring-1 ring-emas-500/20">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-emas-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
                </svg>
                <p class="text-[13px] leading-relaxed text-emas-900">
                    <span class="font-semibold">{{ $tanpaTitik }} bangunan tidak muncul di peta</span>
                    karena diperiksa sebelum aplikasi juru menangkap titik GPS. Koordinatnya tidak
                    dapat dilengkapi belakangan.
                </p>
            </div>
        @endif

        <div class="mt-3.5 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-[12px]">
                    <span class="flex items-center gap-2">
                        <span class="h-3.5 w-3.5 rounded-full border-2 border-white bg-[#c8102e] shadow"></span>
                        <span class="text-slate-600">Ada temuan bermasalah</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="h-3.5 w-3.5 rounded-full border-2 border-white bg-[#127cc4] shadow"></span>
                        <span class="text-slate-600">Tidak ada temuan bermasalah</span>
                    </span>
                </div>
                <label class="flex cursor-pointer items-center gap-2 text-[12px] font-semibold text-slate-600">
                    <input type="checkbox" id="lapisan-das" checked
                           class="h-4 w-4 rounded border-slate-300 text-sungai-600 focus:ring-sungai-500/30">
                    Tampilkan titik DAS
                </label>
            </div>

            @if ($dataTitik->isEmpty())
                <x-kosong judul="Tidak ada bangunan bertitik yang cocok"
                          pesan="Coba longgarkan penyaring, atau lihat dalam bentuk tabel."/>
            @else
                <div id="peta-sebaran" class="relative h-[clamp(20rem,52vh,34rem)] w-full border-t border-slate-100"
                     data-titik="{{ $dataTitik->toJson() }}"
                     data-das="{{ $dataDas->toJson() }}"></div>
            @endif
        </div>

    @else

    {{-- Tabel --}}
    <div class="mt-3.5 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
        @if ($laporan->isEmpty())
            <x-kosong judul="Tidak ada laporan yang cocok" pesan="Coba ubah atau reset filter di atas."/>
        @else
            <div class="overflow-x-auto scrollbar-halus">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/70 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400">
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Juru Sungai</th>
                            <th class="px-5 py-3">DAS</th>
                            <th class="px-5 py-3">Periode</th>
                            <th class="px-5 py-3 text-center">Total Bangunan</th>
                            <th class="px-5 py-3">Cuaca</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[13.5px]">
                        @foreach ($laporan as $item)
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-3 font-semibold tabular-nums text-slate-700">
                                    {{ optional($item->tanggal_inspeksi)->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sungai-50 text-[11px] font-extrabold text-sungai-700">
                                            {{ strtoupper(substr($item->juruSungai?->nama ?? '-', 0, 1)) }}
                                        </span>
                                        <span class="font-semibold text-slate-800">{{ $item->juruSungai?->nama ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-slate-500">{{ $item->das?->nama_das ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-slate-500">
                                    {{ Bulan::singkat($item->bulan) }} {{ $item->tahun }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-slate-100 px-1.5 text-[12px] font-bold tabular-nums text-slate-600">
                                        {{ $item->item_inspeksi_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 capitalize text-slate-500">{{ $item->cuaca ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <a href="{{ route('laporan.show', $item) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-sungai-600 transition hover:bg-sungai-50">
                                        Detail
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($laporan->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">{{ $laporan->links() }}</div>
            @endif
        @endif
    </div>

    @endif
