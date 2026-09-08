@extends('layouts.app')

@php
    $baru = ! $das->exists;
    $ki = 'w-full rounded-xl border-0 bg-permukaan-lembut px-3.5 py-2.5 text-[13.5px] text-teks-tegas ring-1 ring-inset ring-garis transition placeholder:text-teks-samar focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
@endphp

@section('judul', $baru ? 'Tambah DAS' : 'Ubah DAS')
@section('subjudul', $baru ? 'Daerah aliran sungai sebagai wilayah kerja juru' : $das->nama_das)

@push('kepala')
    @vite('resources/js/peta.js')
@endpush

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

        <x-kolom label="Kabupaten" nama="kabupaten_ids"
                 petunjuk="Boleh lebih dari satu, karena batas DAS mengikuti punggung bukit dan tidak selalu berhenti di batas kabupaten. Boleh juga dikosongkan bila belum diketahui.">
            @php $terpilih = collect(old('kabupaten_ids', $das->exists ? $das->kabupaten->pluck('id')->all() : []))->map(fn ($v) => (string) $v); @endphp
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach ($daftarKabupaten as $k)
                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl bg-permukaan-lembut px-3.5 py-2.5 text-[13.5px] text-teks-sedang ring-1 ring-inset ring-garis transition hover:bg-permukaan hover:ring-info-teks/40">
                        <input type="checkbox" name="kabupaten_ids[]" value="{{ $k->id }}"
                               @checked($terpilih->contains((string) $k->id))
                               class="h-4 w-4 rounded border-garis-kuat text-info-teks focus:ring-sungai-500/30">
                        {{ $k->nama }}
                    </label>
                @endforeach
            </div>
        </x-kolom>

        <x-kolom label="Deskripsi" nama="deskripsi">
            <textarea id="deskripsi" name="deskripsi" rows="3" class="{{ $ki }}"
                      placeholder="Keterangan singkat mengenai DAS ini">{{ old('deskripsi', $das->deskripsi) }}</textarea>
        </x-kolom>

        {{-- Titik pada peta --}}
        <div class="border-t border-garis-halus pt-4">
            <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                <label class="text-[13px] font-semibold text-teks-sedang">Titik Perkiraan pada Peta</label>
                <div class="flex items-center gap-2">
                    <span id="label-koordinat" class="text-[12px] font-medium tabular-nums text-teks-samar">
                        @if ($das->punyaKoordinat())
                            {{ number_format($das->koordinat_lat, 5) }}, {{ number_format($das->koordinat_lng, 5) }}
                        @else
                            Belum ditentukan
                        @endif
                    </span>
                    <button type="button" id="hapus-koordinat"
                            class="{{ $das->punyaKoordinat() ? '' : 'hidden' }} rounded-lg px-2 py-1 text-[12px] font-bold text-bahaya-teks transition hover:bg-bahaya-latar">
                        Hapus titik
                    </button>
                </div>
            </div>

            <div id="peta-pilih" class="relative h-64 w-full overflow-hidden rounded-xl ring-1 ring-garis"></div>

            <input type="hidden" id="koordinat_lat" name="koordinat_lat" value="{{ old('koordinat_lat', $das->koordinat_lat) }}">
            <input type="hidden" id="koordinat_lng" name="koordinat_lng" value="{{ old('koordinat_lng', $das->koordinat_lng) }}">

            <p class="mt-1.5 text-[12px] text-teks-samar">
                DAS sebenarnya berupa wilayah, bukan titik &mdash; batasnya mengikuti punggung
                bukit. Titik ini hanya pegangan agar DAS bisa ditandai dan diklik di peta,
                bukan letak resminya. Boleh dikosongkan, tetapi DAS tanpa titik tidak akan
                muncul di halaman Peta DAS.
            </p>
            @error('koordinat_lat')<p class="mt-1.5 text-[12px] font-medium text-bahaya-teks">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2.5 border-t border-garis-halus pt-4 text-[13.5px] text-teks-sedang">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $das->aktif ?? true))
                   class="h-4 w-4 rounded border-garis-kuat text-info-teks focus:ring-sungai-500/30">
            DAS aktif — hanya DAS aktif yang dapat dipilih saat menambah juru
        </label>

    </x-kartu-form>

@endsection
