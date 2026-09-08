{{-- Area hasil, dikirim ulang sendirian saat penyaring berubah. --}}
<div class="overflow-hidden rounded-2xl bg-permukaan ring-1 ring-garis/70">
    @if ($log->isEmpty())
        <div class="px-6 py-20 text-center">
            <p class="text-sm font-bold text-teks-sedang">Belum ada aktivitas tercatat</p>
            <p class="mt-1 text-[13px] text-teks-samar">
                Perubahan data juru, DAS, wilayah sungai, dan pengguna akan muncul di sini.
            </p>
        </div>
    @else
        <div class="divide-y divide-garis-halus">
            @foreach ($log as $baris)
                <div class="px-5 py-3.5">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span class="rounded-lg px-2 py-0.5 text-[11px] font-bold {{ $warnaAksi[$baris->aksi] ?? 'bg-permukaan-sedang text-teks-lembut' }}">
                            {{ $baris->labelAksi() }}
                        </span>
                        <span class="text-[12px] font-bold uppercase tracking-wider text-teks-lembut">
                            {{ $namaJenis[$baris->jenis_data] ?? $baris->jenis_data }}
                        </span>
                        <span class="text-[13.5px] font-semibold text-judul">{{ $baris->ringkasan }}</span>
                        <span class="ml-auto text-[12px] tabular-nums text-teks-samar">
                            {{ $baris->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    <p class="mt-1 text-[12px] text-teks-samar">
                        oleh <span class="font-semibold text-teks-lembut">{{ $baris->nama_pelaku }}</span>
                        @if ($baris->ip)
                            <span class="text-teks-samar">&middot; {{ $baris->ip }}</span>
                        @endif
                    </p>

                    @if ($baris->aksi === 'diubah' && isset($baris->perubahan['sesudah']))
                        <div class="mt-2 overflow-x-auto">
                            <table class="text-[12px]">
                                <tbody>
                                    @foreach ($baris->perubahan['sesudah'] as $kolom => $sesudah)
                                        <tr>
                                            <td class="py-0.5 pr-3 align-top font-semibold text-teks-samar">{{ $kolom }}</td>
                                            <td class="py-0.5 pr-2 align-top text-bahaya-teks line-through">
                                                {{ \Illuminate\Support\Str::limit((string) ($baris->perubahan['sebelum'][$kolom] ?? '—'), 60) ?: '—' }}
                                            </td>
                                            <td class="py-0.5 align-top text-aman-teks">
                                                {{ \Illuminate\Support\Str::limit((string) $sesudah, 60) ?: '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="border-t border-garis-halus px-5 py-3.5">
            {{ $log->links() }}
        </div>
    @endif
</div>
