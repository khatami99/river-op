{{-- Area hasil, dikirim ulang sendirian saat penyaring berubah.
     Dipisahkan supaya halaman penuh dan potongannya merender markup
     yang sama persis — tidak ada dua sumber yang bisa berselisih. --}}
<div class="mt-3.5 overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
    @if ($juru->isEmpty())
        <x-kosong judul="Tidak ada juru sungai yang cocok" pesan="Coba ubah kata kunci pencarian."/>
    @else
        <div class="overflow-x-auto scrollbar-halus">
            <table class="w-full">
                <thead>
                    <tr class="bg-permukaan-lembut/70 text-left text-[12px] font-bold uppercase tracking-wider text-teks-lembut">
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">DAS</th>
                        <th class="px-5 py-3 text-center">Laporan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis-halus text-[13.5px]">
                    @foreach ($juru as $orang)
                        <tr class="transition hover:bg-permukaan-lembut">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-info-latar text-[12px] font-extrabold text-info-teks">
                                        {{ strtoupper(substr($orang->nama, 0, 1)) }}
                                    </span>
                                    <span class="font-semibold text-teks">{{ $orang->nama }}</span>
                                </div>
                            </td>
                                                            <td class="px-5 py-3 text-teks-samar">{{ $orang->das?->nama_das ?? '-' }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-permukaan-sedang px-1.5 text-[12px] font-bold tabular-nums text-teks-lembut">
                                    {{ $orang->laporan_op_count }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <x-lencana :warna="$orang->aktif ? 'normal' : 'netral'">
                                    {{ $orang->aktif ? 'Aktif' : 'Nonaktif' }}
                                </x-lencana>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right">
                                <a href="{{ route('juru.show', $orang) }}"
                                   class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-info-teks transition hover:bg-info-latar">
                                    Detail
                                </a>
                                @if (auth('staf')->user()?->isAdmin())
                                    <a href="{{ route('juru.edit', $orang) }}"
                                       class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-teks-lembut transition hover:bg-permukaan-sedang">
                                        Ubah
                                    </a>
                                    <form method="POST" action="{{ route('juru.destroy', $orang) }}" class="inline"
                                          onsubmit="return confirm('Hapus juru {{ $orang->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg px-2.5 py-1.5 text-[13px] font-bold text-bahaya-teks transition hover:bg-bahaya-latar">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($juru->hasPages())
            <div class="border-t border-garis-halus px-5 py-3">{{ $juru->links() }}</div>
        @endif
    @endif
</div>
