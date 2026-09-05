@extends('layouts.app')

@php
    $baru = ! $staf->exists;
    $diriSendiri = ! $baru && $staf->id === auth('staf')->id();
    $ki = 'w-full rounded-xl border-0 bg-slate-50 px-3.5 py-2.5 text-[13.5px] text-slate-900 ring-1 ring-inset ring-slate-200 transition placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
@endphp

@section('judul', $baru ? 'Tambah Akun Staf' : 'Ubah Akun Staf')
@section('subjudul', $baru ? 'Akun untuk mengakses dashboard dinas' : $staf->nama)

@section('konten')

    @if ($diriSendiri)
        <div class="mb-3.5 flex items-start gap-3 rounded-2xl bg-emas-50 p-4 ring-1 ring-emas-500/20">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emas-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>
            </svg>
            <p class="text-[13px] leading-relaxed text-emas-900">
                Ini akun Anda sendiri. Peran dan status aktifnya tidak dapat diturunkan,
                supaya Anda tidak terkunci dari sistem.
            </p>
        </div>
    @endif

    <x-kartu-form
        judul="Data Akun Staf"
        :aksi="$baru ? route('staf.store') : route('staf.update', $staf)"
        :metode="$baru ? 'POST' : 'PUT'"
        :batal="route('staf.index')"
        :labelSimpan="$baru ? 'Simpan Akun' : 'Simpan Perubahan'">

        <x-kolom label="Nama Lengkap" nama="nama" :wajib="true">
            <input id="nama" name="nama" type="text" class="{{ $ki }}"
                   value="{{ old('nama', $staf->nama) }}" required>
        </x-kolom>

        <x-kolom label="Username" nama="username" :wajib="true">
            <input id="username" name="username" type="text" class="{{ $ki }}"
                   value="{{ old('username', $staf->username) }}" required autocomplete="off">
        </x-kolom>

        <x-kolom label="Password" nama="password" :wajib="$baru"
                 :petunjuk="$baru ? 'Minimal 6 karakter.' : 'Kosongkan bila tidak ingin mengganti password.'">
            <input id="password" name="password" type="password" class="{{ $ki }}"
                   autocomplete="new-password" @required($baru)>
        </x-kolom>

        <x-kolom label="Peran" nama="role" :wajib="true"
                 petunjuk="Admin dapat mengubah data master. Viewer hanya dapat melihat laporan dan rekap.">
            <select id="role" name="role" class="{{ $ki }}" required @disabled($diriSendiri)>
                <option value="viewer" @selected(old('role', $staf->role) === 'viewer')>Viewer — hanya melihat</option>
                <option value="admin" @selected(old('role', $staf->role) === 'admin')>Admin — dapat mengubah data</option>
            </select>
            @if ($diriSendiri)
                <input type="hidden" name="role" value="admin">
            @endif
        </x-kolom>

        <label class="flex items-center gap-2.5 border-t border-slate-100 pt-4 text-[13.5px] text-slate-700">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $staf->aktif ?? true))
                   @disabled($diriSendiri)
                   class="h-4 w-4 rounded border-slate-300 text-sungai-600 focus:ring-sungai-500/30">
            Akun aktif — akun nonaktif tidak dapat masuk
        </label>
        @if ($diriSendiri)
            <input type="hidden" name="aktif" value="1">
        @endif

    </x-kartu-form>

@endsection
