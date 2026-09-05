@extends('layouts.app')

@php
    $baru = ! $wilayah->exists;
    $ki = 'w-full rounded-xl border-0 bg-slate-50 px-3.5 py-2.5 text-[13.5px] text-slate-900 ring-1 ring-inset ring-slate-200 transition placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
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
                   value="{{ old('kode_sungai', $wilayah->kode_sungai) }}" placeholder="WS-CENGAL" required>
        </x-kolom>

        <x-kolom label="Kabupaten" nama="kabupaten_id" :wajib="true">
            <select id="kabupaten_id" name="kabupaten_id" class="{{ $ki }}" required>
                <option value="">Pilih kabupaten</option>
                @foreach ($daftarKabupaten as $k)
                    <option value="{{ $k->id }}" @selected((string) old('kabupaten_id', $wilayah->kabupaten_id) === (string) $k->id)>
                        {{ $k->nama }}
                    </option>
                @endforeach
            </select>
        </x-kolom>

        <x-kolom label="Deskripsi" nama="deskripsi">
            <textarea id="deskripsi" name="deskripsi" rows="3" class="{{ $ki }}"
                      placeholder="Keterangan singkat mengenai wilayah sungai ini">{{ old('deskripsi', $wilayah->deskripsi) }}</textarea>
        </x-kolom>

        <label class="flex items-center gap-2.5 border-t border-slate-100 pt-4 text-[13.5px] text-slate-700">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $wilayah->aktif ?? true))
                   class="h-4 w-4 rounded border-slate-300 text-sungai-600 focus:ring-sungai-500/30">
            Wilayah sungai aktif
        </label>

    </x-kartu-form>

@endsection
