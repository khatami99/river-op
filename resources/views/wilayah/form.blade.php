@extends('layouts.app')

@php
    $baru = ! $wilayah->exists;
    $ki = 'w-full rounded-xl border-0 bg-permukaan-lembut px-3.5 py-2.5 text-[13.5px] text-teks-tegas ring-1 ring-inset ring-garis transition placeholder:text-teks-samar focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
@endphp

@section('judul', $baru ? 'Tambah Wilayah Sungai' : 'Ubah Wilayah Sungai')
@section('subjudul', $baru ? 'Satuan wewenang tertinggi yang menaungi DAS' : $wilayah->nama_sungai)

@section('konten')

    <x-kartu-form
        judul="Data Wilayah Sungai"
        :aksi="$baru ? route('wilayah.store') : route('wilayah.update', $wilayah)"
        :metode="$baru ? 'POST' : 'PUT'"
        :batal="route('wilayah.index')"
        :labelSimpan="$baru ? 'Simpan Wilayah Sungai' : 'Simpan Perubahan'">

        <x-kolom label="Nama Wilayah Sungai" nama="nama_sungai" :wajib="true">
            <input id="nama_sungai" name="nama_sungai" type="text" class="{{ $ki }}"
                   value="{{ old('nama_sungai', $wilayah->nama_sungai) }}" placeholder="Wilayah Sungai Cengal" required>
        </x-kolom>

        <x-kolom label="Kode" nama="kode_sungai" :wajib="true" petunjuk="Harus unik, misalnya WS-CENGAL.">
            <input id="kode_sungai" name="kode_sungai" type="text" class="{{ $ki }}"
                   value="{{ old('kode_sungai', $wilayah->kode_sungai) }}" placeholder="04.10.B" required>
        </x-kolom>

        {{-- Kabupaten yang disinggung wilayah sungai ini.

             Diketik, bukan dicentang dari daftar tetap. Alasannya pemekaran:
             daftar kabupaten Kalimantan Selatan bukan sesuatu yang berlaku
             selamanya — Tanah Bumbu dan Balangan sendiri baru lahir pada
             2003. Daftar yang dikunci di dalam kode berarti suatu hari nanti
             ada kabupaten yang tidak bisa dipakai sampai aplikasinya diubah
             programmer, dan itu tidak boleh terjadi untuk sesuatu sesederhana
             nama wilayah.

             Yang diketik tetap disimpan sebagai data, bukan sebagai tulisan
             bebas: nama yang cocok memakai kabupaten yang sudah ada, nama
             yang belum dikenal dibuatkan barunya. Dengan begitu ia tetap
             punya identitas, sehingga bisa ditawarkan di formulir DAS dan
             tetap bisa dikelompokkan di Export Excel. --}}
        <x-kolom label="Kabupaten yang Dilalui" nama="kabupaten_nama"
                 petunjuk="Hanya yang tertulis di sini yang bisa dipilih pada DAS di bawahnya.">
            {{-- Kolom koma inilah bentuk dasarnya, dan bentuk inilah yang
                 dikirim ke server. JavaScript menyembunyikannya lalu menaruh
                 tampilan chip di atasnya, sambil terus menyelaraskan nilainya
                 ke sini — jadi controller tidak perlu tahu apa-apa soal chip,
                 dan tanpa JavaScript formulir ini masih bisa dipakai. --}}
            <input id="kabupaten_nama" name="kabupaten_nama" type="text" class="{{ $ki }}"
                   value="{{ old('kabupaten_nama', $wilayah->exists ? $wilayah->kabupaten->pluck('nama')->join(', ') : '') }}"
                   placeholder="Kotabaru, Tanah Bumbu, Tanah Laut"
                   data-chip
                   data-chip-baru="Tambah kabupaten baru"
                   data-chip-petunjuk="Ketik nama kabupaten&hellip;"
                   data-chip-pilihan="{{ $daftarKabupaten->map(fn ($k) => ['nilai' => $k->nama, 'label' => $k->nama])->toJson() }}">

            {{-- Ejaan yang sudah dipakai, untuk disalin apa adanya. Hanya
                 berguna pada bentuk dasar; begitu chip terpasang, sarannya
                 muncul sambil mengetik dan baris ini disembunyikan. --}}
            @if ($daftarKabupaten->isNotEmpty())
                <p data-daftar-ejaan class="mt-2 text-[12px] leading-relaxed text-teks-samar">
                    <span class="font-semibold text-teks-lembut">Sudah terdaftar:</span>
                    {{ $daftarKabupaten->pluck('nama')->join(', ') }}.
                </p>
            @endif
        </x-kolom>

        <x-kolom label="Deskripsi" nama="deskripsi">
            <textarea id="deskripsi" name="deskripsi" rows="3" class="{{ $ki }}"
                      placeholder="Keterangan singkat mengenai wilayah sungai ini">{{ old('deskripsi', $wilayah->deskripsi) }}</textarea>
        </x-kolom>

        <label class="flex items-center gap-2.5 border-t border-garis-halus pt-4 text-[13.5px] text-teks-sedang">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $wilayah->aktif ?? true))
                   class="h-4 w-4 rounded border-garis-kuat text-info-teks focus:ring-sungai-500/30">
            Wilayah sungai aktif
        </label>

    </x-kartu-form>

@endsection
