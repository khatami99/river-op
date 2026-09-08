@use('App\Support\Bulan')

@props(['bulan', 'tahun', 'daftarTahun'])

{{-- Pemilih periode untuk halaman yang seluruh isinya adalah potret satu bulan
     (Dashboard dan Rekapitulasi).

     Ia berdiri di badan halaman, bukan di kop, karena dua alasan. Pertama,
     kontrol yang MENGUBAH APA YANG TERLIHAT tidak boleh bersebelahan dengan
     tombol yang MENGERJAKAN SESUATU seperti Export — keduanya jenis yang
     berbeda dan menyandingkannya mengaburkan batas itu. Kedua, urutan bacanya
     jadi sama dengan halaman Laporan OP: judul, lalu penyaring, lalu hasil.

     Dijadikan komponen supaya kedua halaman itu benar-benar sama bentuknya,
     bukan sekadar mirip. --}}
@php
    $kelasPilihan = 'rounded-xl border-0 bg-slate-50 py-2 pl-3 pr-8 text-[13px] font-semibold text-slate-800 ring-1 ring-inset ring-slate-200 transition focus:bg-white focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
@endphp

<form method="GET" class="mb-3.5 flex flex-wrap items-center gap-2 rounded-2xl bg-white px-4 py-3 ring-1 ring-slate-200/70">
    <label class="mr-1 text-[12px] font-bold uppercase tracking-wider text-slate-600">Periode</label>

    <select name="bulan" onchange="this.form.submit()" class="{{ $kelasPilihan }}">
        @foreach (Bulan::NAMA as $nomor => $nama)
            <option value="{{ $nomor }}" @selected($nomor === $bulan)>{{ $nama }}</option>
        @endforeach
    </select>

    <select name="tahun" onchange="this.form.submit()" class="{{ $kelasPilihan }}">
        @foreach ($daftarTahun as $t)
            <option value="{{ $t }}" @selected($t === $tahun)>{{ $t }}</option>
        @endforeach
    </select>

    {{-- Tanpa JavaScript, tombol ini yang menggantikan onchange. --}}
    <noscript>
        <button type="submit" class="rounded-xl bg-sungai-600 px-4 py-2 text-[13px] font-bold text-white">
            Tampilkan
        </button>
    </noscript>
</form>
