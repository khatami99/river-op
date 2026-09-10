@extends('layouts.app')

@section('judul', $wilayah->nama_sungai)
@section('subjudul', $wilayah->kode_sungai)

@push('kepala')
    @vite('resources/js/peta.js')
@endpush

@section('konten')

    <x-aksi-halaman :kembali="route('wilayah.index')"/>

    <div class="relative overflow-hidden rounded-2xl bg-sungai-950 p-5">
            <svg class="pointer-events-none absolute -bottom-6 -right-8 w-56 opacity-20" viewBox="0 0 400 120" fill="none">
                <path d="M0 40c40-24 80-24 120 0s80 24 120 0 80-24 120 0 80 24 120 0" stroke="#5db8f8" stroke-width="9" stroke-linecap="round"/>
                <path d="M0 76c40-24 80-24 120 0s80 24 120 0 80-24 120 0 80 24 120 0" stroke="#127cc4" stroke-width="9" stroke-linecap="round"/>
            </svg>

            <div class="relative">
                <p class="text-[12px] font-bold uppercase tracking-wider text-emas-400">Wilayah Sungai</p>
                <p class="mt-1.5 text-lg font-extrabold text-white">{{ $wilayah->nama_sungai }}</p>

                <dl class="mt-4 grid gap-x-6 gap-y-4 border-t border-white/10 pt-4 sm:grid-cols-3 lg:grid-cols-5">
                    @php
                        $info = [
                            'Kode' => $wilayah->kode_sungai,
                            'Jumlah DAS' => $wilayah->das_count,
                            'Jumlah Juru' => $wilayah->juru_sungai_count,
                            'Total Laporan' => $wilayah->laporan_op_count,
                        ];
                    @endphp
                    @foreach ($info as $label => $nilai)
                        <div>
                            <dt class="text-[12px] text-sungai-300/80">{{ $label }}</dt>
                            <dd class="mt-0.5 text-[15px] font-bold tabular-nums text-white">{{ $nilai }}</dd>
                        </div>
                    @endforeach
                    <div>
                        <dt class="text-[12px] text-sungai-300/80">Status</dt>
                        <dd class="mt-1">
                            <x-lencana :warna="$wilayah->aktif ? 'normal' : 'netral'">
                                {{ $wilayah->aktif ? 'Aktif' : 'Nonaktif' }}
                            </x-lencana>
                        </dd>
                    </div>
                </dl>

                @if ($wilayah->deskripsi)
                    <p class="mt-4 border-t border-white/10 pt-4 text-[13px] leading-relaxed text-sungai-200/70">
                        {{ $wilayah->deskripsi }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Letak DAS di bawah wilayah sungai ini.

             Peta di sini menjawab pertanyaan yang khas halaman ini: DAS mana
             saja yang bernaung di sini dan di mana letaknya. Wilayah sungai
             sendiri tidak punya titik — ia wilayah, bukan titik — sehingga yang
             digambar adalah DAS-DAS di dalamnya. --}}
    {{-- Peta dan panel ringkasannya berdiri sebaris berdampingan.

         Panel ini menjawab klik pada peta, jadi ia harus berada di sebelahnya.
         Sebelumnya ia jatuh ke baris berikutnya di sisi kiri, sehingga
         mengklik penanda di kanan atas memunculkan jawabannya secara diagonal
         di kiri bawah — jauh dari yang baru saja disentuh. --}}
    @if ($dataPeta->isNotEmpty())
        <div class="mt-3.5 grid gap-3.5 lg:grid-cols-3">
            <div class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70 lg:col-span-2">
                <div id="peta-das" class="relative h-[clamp(18rem,40vh,24rem)] w-full"
                     data-das="{{ $dataPeta->toJson() }}"></div>

                {{-- Catatan ini menempel pada petanya, sebab ia menerangkan
                     apa yang TIDAK ada di peta itu. --}}
                @if ($belumBerkoordinat > 0)
                    <p class="border-t border-garis-halus px-5 py-2.5 text-[12px] text-teks-samar">
                        {{ $belumBerkoordinat }} DAS belum punya titik, sehingga tidak muncul di peta.
                    </p>
                @endif
            </div>

            <div class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70 lg:self-start">
                <div id="panel-kosong" class="flex flex-col items-center px-6 py-10 text-center">
                    <p class="text-sm font-bold text-teks-sedang">Pilih sebuah DAS</p>
                    <p class="mt-1 max-w-[15rem] text-[13px] leading-relaxed text-teks-samar">
                        Klik penanda di peta untuk melihat juru yang bertugas beserta ringkasan laporannya.
                    </p>
                </div>
                <div id="panel-das" class="hidden"></div>
            </div>
        </div>
    @endif

        {{-- DAS di bawah wilayah sungai ini --}}
    <div class="mt-3.5 overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
        <div class="flex items-center justify-between px-5 py-4">
                <h2 class="text-sm font-bold text-judul">Daerah Aliran Sungai</h2>
                <x-lencana warna="sungai">{{ $daftarDas->total() }} DAS</x-lencana>
            </div>

            @if ($daftarDas->isEmpty())
                <x-kosong judul="Belum ada DAS" pesan="Wilayah sungai ini belum memiliki DAS."/>
            @else
                <ul class="divide-y divide-garis-halus border-t border-garis-halus">
                    @foreach ($daftarDas as $item)
                        <li>
                            <a href="{{ route('das.show', $item) }}" class="flex items-center gap-3 px-5 py-3 transition hover:bg-permukaan-lembut">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-info-latar">
                                    <svg class="h-4 w-4 text-info-teks" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 15c2.5-2 5.5-2 8 0s5.5 2 8 0M3 9c2.5-2 5.5-2 8 0s5.5 2 8 0"/>
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[13.5px] font-semibold text-teks">{{ $item->nama_das }}</p>
                                    <p class="truncate text-[12px] text-teks-samar">{{ $item->kode_das }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-4 text-[12px] text-teks-samar">
                                    <span><span class="font-bold tabular-nums text-teks-sedang">{{ $item->juru_sungai_count }}</span> juru</span>
                                    <span><span class="font-bold tabular-nums text-teks-sedang">{{ $item->laporan_op_count }}</span> laporan</span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($daftarDas->hasPages())
                    <div class="border-t border-garis-halus px-5 py-3">{{ $daftarDas->links() }}</div>
                @endif
            @endif
    </div>

@endsection
