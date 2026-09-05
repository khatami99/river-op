@extends('layouts.app')

@php
    $baru = ! $das->exists;
    $ki = 'w-full rounded-xl border-0 bg-slate-50 px-3.5 py-2.5 text-[13.5px] text-slate-900 ring-1 ring-inset ring-slate-200 transition placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
@endphp

@section('judul', $baru ? 'Tambah DAS' : 'Ubah DAS')
@section('subjudul', $baru ? 'Daerah aliran sungai sebagai wilayah kerja juru' : $das->nama_das)

@section('konten')

    <x-kartu-form
        judul="Data Daerah Aliran Sungai"
        :aksi="$baru ? route('das.store') : route('das.update', $das)"
        :metode="$baru ? 'POST' : 'PUT'"
        :batal="route('das.index')"
        :labelSimpan="$baru ? 'Simpan DAS' : 'Simpan Perubahan'">

        <x-kolom label="Nama DAS" nama="nama_das" :wajib="true">
            <input id="nama_das" name="nama_das" type="text" class="{{ $ki }}"
                   value="{{ old('nama_das', $das->nama_das) }}" placeholder="Sungai Tabanio" required>
        </x-kolom>

        <x-kolom label="Kode DAS" nama="kode_das" :wajib="true" petunjuk="Harus unik, misalnya DAS-TABA-01.">
            <input id="kode_das" name="kode_das" type="text" class="{{ $ki }}"
                   value="{{ old('kode_das', $das->kode_das) }}" placeholder="DAS-TABA-01" required>
        </x-kolom>

        <x-kolom label="Wilayah Sungai" nama="wilayah_sungai_id" :wajib="true"
                 petunjuk="Wilayah sungai yang menaungi DAS ini.">
            <select id="wilayah_sungai_id" name="wilayah_sungai_id" class="{{ $ki }}" required>
                <option value="">Pilih wilayah sungai</option>
                @foreach ($daftarWilayah as $w)
                    <option value="{{ $w->id }}" @selected((string) old('wilayah_sungai_id', $das->wilayah_sungai_id) === (string) $w->id)>
                        {{ $w->nama_sungai }}
                    </option>
                @endforeach
            </select>
        </x-kolom>

        <x-kolom label="Deskripsi" nama="deskripsi">
            <textarea id="deskripsi" name="deskripsi" rows="3" class="{{ $ki }}"
                      placeholder="Keterangan singkat mengenai DAS ini">{{ old('deskripsi', $das->deskripsi) }}</textarea>
        </x-kolom>

        <label class="flex items-center gap-2.5 border-t border-slate-100 pt-4 text-[13.5px] text-slate-700">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $das->aktif ?? true))
                   class="h-4 w-4 rounded border-slate-300 text-sungai-600 focus:ring-sungai-500/30">
            DAS aktif — hanya DAS aktif yang dapat dipilih saat menambah juru
        </label>

    </x-kartu-form>

@endsection
