@extends('layouts.app')

@php
    $baru = ! $juru->exists;
    $ki = 'w-full rounded-xl border-0 bg-permukaan-lembut px-3.5 py-2.5 text-[13.5px] text-teks-tegas ring-1 ring-inset ring-garis transition placeholder:text-teks-samar focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
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

        <x-kolom label="Nomor Telepon" nama="no_telepon"
                 petunjuk="Dipakai saat dinas perlu menghubungi juru yang belum melapor.">
            <input id="no_telepon" name="no_telepon" type="tel" class="{{ $ki }}"
                   placeholder="08xxxxxxxxxx"
                   value="{{ old('no_telepon', $juru->no_telepon) }}">
        </x-kolom>

        <x-kolom label="Nomor SK Pengangkatan" nama="nomor_sk"
                 petunjuk="Diterbitkan per angkatan, sehingga satu SK biasanya memuat beberapa juru. Karena itu nomor SK bukan pembeda antarjuru.">
            <input id="nomor_sk" name="nomor_sk" type="text" class="{{ $ki }}"
                   value="{{ old('nomor_sk', $juru->nomor_sk) }}">
        </x-kolom>

        <x-kolom label="DAS Wilayah Kerja" nama="das_id"
                 petunjuk="Daerah aliran sungai yang menjadi tanggung jawab juru ini. Boleh dikosongkan bila wilayah tugasnya belum diputuskan — tetapi selama begitu, laporan yang ia kirim masuk tanpa DAS dan tidak muncul di Rekapitulasi.">
            @php $dasTerpilih = old('das_id', $juru->das_id); @endphp
            <select id="das_id" name="das_id" class="{{ $ki }}"
                    data-pilihan-cari="Ketik nama DAS&hellip;">
                {{-- Pilihan kosong selalu ada sekarang, sebab "belum
                     ditugaskan" adalah keadaan yang sah: juru bisa didata
                     lebih dulu sementara wilayah kerjanya belum diputuskan,
                     dan harus ada cara mengembalikannya ke keadaan itu.

                     Ia juga mencegah kecelakaan yang lebih halus: tanpa
                     baris ini, DAS pertama menurut abjad akan terpilih
                     diam-diam, dan admin yang lupa menyentuh kolom ini
                     menugaskan juru ke DAS yang salah tanpa pernah tahu. --}}
                <option value="">Belum ditugaskan</option>
                @foreach ($daftarDas as $d)
                    <option value="{{ $d->id }}" @selected((string) $dasTerpilih === (string) $d->id)>
                        {{ $d->namaRingkas() }} ({{ $d->kode_das }})
                    </option>
                @endforeach
            </select>
        </x-kolom>

        <div class="border-t border-garis-halus pt-4">
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

        <label class="flex items-center gap-2.5 border-t border-garis-halus pt-4 text-[13.5px] text-teks-sedang">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $juru->aktif ?? true))
                   class="h-4 w-4 rounded border-garis-kuat text-info-teks focus:ring-sungai-500/30">
            Akun aktif — juru nonaktif tidak dapat masuk ke aplikasi
        </label>

    </x-kartu-form>

@endsection
