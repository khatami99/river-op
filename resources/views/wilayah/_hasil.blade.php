{{-- Area hasil, dikirim ulang sendirian saat penyaring berubah.
     Dipisahkan supaya halaman penuh dan potongannya merender markup
     yang sama persis — tidak ada dua sumber yang bisa berselisih. --}}
<div class="mt-3.5 overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
    @if ($wilayah->isEmpty())
        <x-kosong judul="Tidak ada wilayah sungai yang cocok" pesan="Coba ubah kata kunci pencarian."/>
    @else
        <div class="scrollbar-halus overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-permukaan-lembut/70 text-left text-[12px] font-bold uppercase tracking-wider text-teks-lembut">
                        <th class="px-5 py-3">Nama Wilayah Sungai</th>
                        <th class="px-5 py-3">Kode</th>
                        <th class="px-5 py-3 text-center">DAS</th>
                        <th class="px-5 py-3 text-center">Juru</th>
                        <th class="px-5 py-3 text-center">Laporan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis-halus text-[13.5px]">
                    @foreach ($wilayah as $sungai)
                        <tr class="transition hover:bg-permukaan-lembut">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-peringatan-latar">
                                        <svg class="h-4 w-4 text-peringatan-teks" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M5 8l7-5 7 5M5 8v8l7 5 7-5V8"/>
                                        </svg>
                                    </span>
                                    <span class="font-semibold text-teks">{{ $sungai->nama_sungai }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 font-medium text-teks-samar">{{ $sungai->kode_sungai }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-info-latar px-1.5 text-[12px] font-bold tabular-nums text-info-teks">
                                    {{ $sungai->das_count }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-permukaan-sedang px-1.5 text-[12px] font-bold tabular-nums text-teks-lembut">
                                    {{ $sungai->juru_sungai_count }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-permukaan-sedang px-1.5 text-[12px] font-bold tabular-nums text-teks-lembut">
                                    {{ $sungai->laporan_op_count }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <x-lencana :warna="$sungai->aktif ? 'hijau' : 'abu'">
                                    {{ $sungai->aktif ? 'Aktif' : 'Nonaktif' }}
                                </x-lencana>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('wilayah.show', $sungai) }}"
                                       class="rounded-lg px-2.5 py-1.5 text-[13px] font-semibold text-info-teks transition hover:bg-info-latar">Detail</a>
                                    @if (auth('staf')->user()?->isAdmin())
                                        <a href="{{ route('wilayah.edit', $sungai) }}"
                                           class="rounded-lg px-2.5 py-1.5 text-[13px] font-semibold text-teks-lembut transition hover:bg-permukaan-sedang">Ubah</a>
                                        <form method="POST" action="{{ route('wilayah.destroy', $sungai) }}"
                                              onsubmit="return confirm('Hapus wilayah sungai ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-lg px-2.5 py-1.5 text-[13px] font-semibold text-bahaya-teks transition hover:bg-bahaya-latar">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($wilayah->hasPages())
            <div class="border-t border-garis-halus px-5 py-3">{{ $wilayah->links() }}</div>
        @endif
    @endif
</div>
