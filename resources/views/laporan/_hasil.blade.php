@use('App\Support\Bulan')

{{-- Area hasil: tabel atau peta.

     Dipisah dari halamannya supaya bisa dikirim ulang sendirian ketika
     penyaring berubah, tanpa memuat ulang seluruh halaman. Itulah yang membuat
     hasilnya bisa berubah sambil pengguna mengetik tanpa kehilangan kursor. --}}
    @if ($tampilan === 'peta')

        @if ($tanpaTitik > 0)
            <div class="mt-3.5 flex items-start gap-3 rounded-2xl bg-peringatan-latar p-4 ring-1 ring-peringatan-teks/25">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-peringatan-teks" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
                </svg>
                <p class="text-[13px] leading-relaxed text-peringatan-teks-pekat">
                    <span class="font-semibold">{{ $tanpaTitik }} bangunan tidak muncul di peta</span>
                    karena diperiksa sebelum aplikasi juru menangkap titik GPS. Koordinatnya tidak
                    dapat dilengkapi belakangan.
                </p>
            </div>
        @endif

        <div class="mt-3.5 overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-[12px]">
                    <span class="flex items-center gap-2">
                        <span class="h-3.5 w-3.5 rounded-full border-2 border-white bg-[#c8102e] shadow"></span>
                        <span class="text-teks-lembut">Ada temuan bermasalah</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="h-3.5 w-3.5 rounded-full border-2 border-white bg-[#127cc4] shadow"></span>
                        <span class="text-teks-lembut">Tidak ada temuan bermasalah</span>
                    </span>
                </div>
                <label class="flex cursor-pointer items-center gap-2 text-[12px] font-semibold text-teks-lembut">
                    <input type="checkbox" id="lapisan-das" checked
                           class="h-4 w-4 rounded border-garis-kuat text-info-teks focus:ring-sungai-500/30">
                    Tampilkan titik DAS
                </label>
            </div>

            @if ($dataTitik->isEmpty())
                <x-kosong judul="Tidak ada bangunan bertitik yang cocok"
                          pesan="Coba longgarkan penyaring, atau lihat dalam bentuk tabel."/>
            @else
                <div id="peta-sebaran" class="relative h-[clamp(20rem,52vh,34rem)] w-full border-t border-garis-halus"
                     data-titik="{{ $dataTitik->toJson() }}"
                     data-das="{{ $dataDas->toJson() }}"></div>
            @endif
        </div>

    @else

    {{-- Tabel --}}
    <div class="mt-3.5 overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
        @if ($laporan->isEmpty())
            <x-kosong judul="Tidak ada laporan yang cocok" pesan="Coba ubah atau reset filter di atas."/>
        @else
            <div class="overflow-x-auto scrollbar-halus">
                <table class="w-full">
                    <thead>
                        <tr class="bg-permukaan-lembut/70 text-left text-[12px] font-bold uppercase tracking-wider text-teks-lembut">
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Juru Sungai</th>
                            <th class="px-5 py-3">DAS</th>
                            <th class="px-5 py-3">Periode</th>
                            <th class="px-5 py-3 text-center">Total Bangunan</th>
                            <th class="px-5 py-3">Cuaca</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis-halus text-[13.5px]">
                        @foreach ($laporan as $item)
                            <tr class="transition hover:bg-permukaan-lembut">
                                <td class="whitespace-nowrap px-5 py-3 font-semibold tabular-nums text-teks-sedang">
                                    {{ optional($item->tanggal_inspeksi)->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-info-latar text-[11px] font-extrabold text-info-teks">
                                            {{ strtoupper(substr($item->juruSungai?->nama ?? '-', 0, 1)) }}
                                        </span>
                                        <span class="font-semibold text-teks">{{ $item->juruSungai?->nama ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-teks-samar">{{ $item->das?->nama_das ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-teks-samar">
                                    {{ Bulan::singkat($item->bulan) }} {{ $item->tahun }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-permukaan-sedang px-1.5 text-[12px] font-bold tabular-nums text-teks-lembut">
                                        {{ $item->item_inspeksi_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 capitalize text-teks-samar">{{ $item->cuaca ?? '-' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <a href="{{ route('laporan.show', $item) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-info-teks transition hover:bg-info-latar">
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
                <div class="border-t border-garis-halus px-5 py-3">{{ $laporan->links() }}</div>
            @endif
        @endif
    </div>

    @endif
