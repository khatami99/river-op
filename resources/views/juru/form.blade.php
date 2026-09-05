@extends('layouts.app')

@php
    $baru = ! $juru->exists;
    $ki = 'w-full rounded-xl border-0 bg-slate-50 px-3.5 py-2.5 text-[13.5px] text-slate-900 ring-1 ring-inset ring-slate-200 transition placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
@endphp

@section('judul', $baru ? 'Tambah Juru Sungai' : 'Ubah Juru Sungai')
@section('subjudul', $baru ? 'Buat akun juru baru beserta wilayah kerjanya' : $juru->nama)

@section('konten')

    <x-kartu-form
        judul="Data Juru Sungai"
        :aksi="$baru ? route('juru.store') : route('juru.update', $juru)"
        :metode="$baru ? 'POST' : 'PUT'"
        :batal="route('juru.index')"
        :labelSimpan="$baru ? 'Simpan Juru' : 'Simpan Perubahan'">

        @if ($kembaliKeDas)
            <input type="hidden" name="kembali_ke_das" value="{{ $kembaliKeDas }}">
        @endif

        <x-kolom label="Nama Lengkap" nama="nama" :wajib="true">
            <input id="nama" name="nama" type="text" class="{{ $ki }}"
                   value="{{ old('nama', $juru->nama) }}" required>
        </x-kolom>

        <x-kolom label="NIP" nama="nip" petunjuk="Boleh dikosongkan bila juru belum memiliki NIP.">
            <input id="nip" name="nip" type="text" class="{{ $ki }}"
                   value="{{ old('nip', $juru->nip) }}">
        </x-kolom>

        <x-kolom label="DAS Wilayah Kerja" nama="das_id" :wajib="true"
                 petunjuk="Menentukan daerah aliran sungai yang menjadi tanggung jawab juru ini.">
            <select id="das_id" name="das_id" class="{{ $ki }}" required>
                <option value="">Pilih DAS</option>
                @foreach ($daftarDas as $d)
                    <option value="{{ $d->id }}" @selected((string) old('das_id', $juru->das_id) === (string) $d->id)>
                        {{ $d->nama_das }} ({{ $d->kode_das }})
                    </option>
                @endforeach
            </select>
        </x-kolom>

        <div class="border-t border-slate-100 pt-4">
            <x-kolom label="Username" nama="username" :wajib="true"
                     petunjuk="Dipakai juru untuk masuk ke aplikasi Android.">
                <input id="username" name="username" type="text" class="{{ $ki }}"
                       value="{{ old('username', $juru->username) }}" required autocomplete="off">
            </x-kolom>
        </div>

        <x-kolom label="Password" nama="password" :wajib="$baru"
                 :petunjuk="$baru ? 'Minimal 6 karakter.' : 'Kosongkan bila tidak ingin mengganti password.'">
            <input id="password" name="password" type="password" class="{{ $ki }}"
                   autocomplete="new-password" @required($baru)>
        </x-kolom>

        <label class="flex items-center gap-2.5 border-t border-slate-100 pt-4 text-[13.5px] text-slate-700">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $juru->aktif ?? true))
                   class="h-4 w-4 rounded border-slate-300 text-sungai-600 focus:ring-sungai-500/30">
            Akun aktif — juru nonaktif tidak dapat masuk ke aplikasi
        </label>

    </x-kartu-form>

@endsection
