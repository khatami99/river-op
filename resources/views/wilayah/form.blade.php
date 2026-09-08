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
