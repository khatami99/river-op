@extends('layouts.app')

@php
    $ki = 'rounded-xl border-0 bg-permukaan-lembut px-3 py-2 text-[13px] text-teks-tegas ring-1 ring-inset ring-garis focus:bg-permukaan focus:ring-2 focus:ring-inset focus:ring-sungai-500 focus:outline-none';
    $namaJenis = [
        'JuruSungai' => 'Juru Sungai',
        'Das' => 'DAS',
        'WilayahSungai' => 'Wilayah Sungai',
        'StafDinas' => 'Pengguna',
    ];
    $warnaAksi = [
        'dibuat' => 'bg-aman-latar text-aman-teks',
        'diubah' => 'bg-info-latar text-info-teks',
        'dihapus' => 'bg-bahaya-latar text-bahaya-teks',
    ];
@endphp

@section('judul', 'Log Aktivitas')
@section('subjudul')
    <span id="jumlah-hasil">Jejak perubahan data master oleh staf dinas</span>
@endsection

@section('konten')

    <form method="GET" data-saring data-hasil="hasil-log"
          class="mb-3.5 flex flex-wrap items-center gap-2 rounded-2xl bg-permukaan p-4 ring-1 ring-garis/70">
        <select name="pelaku" class="{{ $ki }}">
            <option value="">Semua pelaku</option>
            @foreach ($daftarPelaku as $p)
                <option value="{{ $p->id }}" @selected((string) $filter['pelaku'] === (string) $p->id)>{{ $p->nama }}</option>
            @endforeach
        </select>

        <select name="jenis" class="{{ $ki }}">
            <option value="">Semua jenis data</option>
            @foreach ($daftarJenis as $j)
                <option value="{{ $j }}" @selected($filter['jenis'] === $j)>{{ $namaJenis[$j] ?? $j }}</option>
            @endforeach
        </select>

        <select name="aksi" class="{{ $ki }}">
            <option value="">Semua aksi</option>
            @foreach (['dibuat' => 'Ditambahkan', 'diubah' => 'Diubah', 'dihapus' => 'Dihapus'] as $nilai => $label)
                <option value="{{ $nilai }}" @selected($filter['aksi'] === $nilai)>{{ $label }}</option>
            @endforeach
        </select>

        <button type="submit" class="rounded-xl bg-aksi px-4 py-2 text-[13px] font-bold text-aksi-teks transition hover:bg-aksi-sorot">
            Terapkan
        </button>

        @if (array_filter($filter))
            <a href="{{ route('log.index') }}" class="rounded-xl bg-permukaan-sedang px-3 py-2 text-[13px] font-bold text-teks-lembut transition hover:bg-permukaan-kuat">
                Reset
            </a>
        @endif
    </form>

    <div id="hasil-log">
        @include('log._hasil')
    </div>

@endsection
