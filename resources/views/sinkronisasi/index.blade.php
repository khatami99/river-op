@extends('layouts.app')

@section('judul', 'Monitoring Sinkronisasi')
@section('subjudul', $juru->count() . ' juru aktif')

@section('konten')

    <div class="mb-3.5 flex flex-wrap items-start gap-3 rounded-2xl bg-teal-50 p-4 ring-1 ring-teal-600/15">
        <svg class="mt-0.5 h-4 w-4 shrink-0 text-teal-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-[13px] leading-relaxed text-teal-900">
            Angka di halaman ini dilaporkan oleh aplikasi di HP juru, bukan dihitung server.
            Laporan yang gagal terkirim memang tidak pernah sampai ke sini, sehingga satu-satunya
            cara mengetahuinya adalah aplikasi yang mengabarkannya sendiri.
            <span class="font-semibold">Juru yang belum pernah melapor berarti aplikasinya belum diperbarui atau belum dibuka.</span>
        </p>
    </div>

    <div class="mb-3.5 grid gap-3 sm:grid-cols-3">
        @foreach ([
            ['Belum pernah melapor', $belumPernah, 'text-slate-700'],
            ['Punya laporan gagal', $adaGagal, $adaGagal > 0 ? 'text-bata-600' : 'text-hijau-600'],
            ['Punya laporan tertunda', $adaTertunda, $adaTertunda > 0 ? 'text-emas-700' : 'text-hijau-600'],
        ] as [$label, $nilai, $warna])
            <div class="rounded-2xl bg-white px-5 py-4 ring-1 ring-slate-200/70">
                <p class="text-2xl font-extrabold tabular-nums {{ $warna }}">{{ $nilai }}</p>
                <p class="mt-0.5 text-[12px] font-medium text-slate-500">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="border-b border-slate-100 text-[12px] font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-5 py-3">Juru Sungai</th>
                        <th class="px-5 py-3">DAS</th>
                        <th class="px-5 py-3 text-right">Tertunda</th>
                        <th class="px-5 py-3 text-right">Gagal</th>
                        <th class="px-5 py-3 text-right">Terkirim</th>
                        <th class="px-5 py-3">Laporan Terakhir</th>
                        <th class="px-5 py-3">Keadaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($juru as $j)
                        @php $s = $j->statusSinkronisasi; @endphp
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="px-5 py-3">
                                <a href="{{ route('juru.show', $j) }}" class="font-semibold text-sungai-950 hover:underline">{{ $j->nama }}</a>
                                @if ($s?->versi_aplikasi)
                                    <span class="ml-1.5 text-[11px] text-slate-500">v{{ $s->versi_aplikasi }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $j->das?->nama_das ?? '-' }}</td>
                            <td class="px-5 py-3 text-right tabular-nums {{ $s && $s->jumlah_tertunda > 0 ? 'font-bold text-emas-700' : 'text-slate-500' }}">
                                {{ $s?->jumlah_tertunda ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-right tabular-nums {{ $s && $s->jumlah_gagal > 0 ? 'font-bold text-bata-600' : 'text-slate-500' }}">
                                {{ $s?->jumlah_gagal ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-right tabular-nums text-slate-500">{{ $s?->jumlah_terkirim ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-500">
                                {{ $s ? $s->dilaporkan_pada->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="px-5 py-3">
                                @if (! $s)
                                    <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600">Belum pernah melapor</span>
                                @elseif ($s->jumlah_gagal > 0)
                                    <span class="rounded-lg bg-bata-50 px-2 py-0.5 text-[11px] font-bold text-bata-700">Ada yang gagal terkirim</span>
                                @elseif ($s->basi())
                                    <span class="rounded-lg bg-emas-50 px-2 py-0.5 text-[11px] font-bold text-emas-700">Sudah lama tidak melapor</span>
                                @elseif ($s->jumlah_tertunda > 0)
                                    <span class="rounded-lg bg-emas-50 px-2 py-0.5 text-[11px] font-bold text-emas-700">Menunggu terkirim</span>
                                @else
                                    <span class="rounded-lg bg-hijau-50 px-2 py-0.5 text-[11px] font-bold text-hijau-700">Aman</span>
                                @endif

                                @if ($s?->pesan_terakhir)
                                    <span class="mt-1 block text-[11px] text-slate-500">{{ $s->pesan_terakhir }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
