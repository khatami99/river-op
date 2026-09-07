{{-- Blangko OP untuk arsip cetak. Tata letaknya mengikuti blangko kertas
     dinas: satu halaman per bangunan yang diperiksa, kolom sama persis, dan
     ditutup ruang tanda tangan pelaksana. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $laporan->das?->nama_das }} — Blangko OP</title>
    <style>
        @page { margin: 18mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #000; }
        .lembar { page-break-after: always; }
        .lembar:last-child { page-break-after: auto; }
        .kode { text-align: right; font-size: 9px; }
        h1 { font-size: 11px; margin: 0 0 10px; font-weight: bold; }
        .kepala td { padding: 1.5px 0; vertical-align: top; }
        .kepala .label { width: 150px; }
        .kepala .titik { width: 10px; }
        .tipe { margin: 8px 0 5px; }
        table.isi { width: 100%; border-collapse: collapse; }
        table.isi th, table.isi td { border: 0.6px solid #000; padding: 3px 4px; vertical-align: top; }
        table.isi th { font-weight: bold; text-align: center; background: #f2f2f2; }
        .no { width: 26px; text-align: center; }
        .bagian { width: 17%; }
        .pengamatan { width: 20%; }
        .tindakan { width: 25%; }
        .koordinat { width: 15%; font-size: 8px; }
        .catatan { margin-top: 5px; font-size: 8px; font-style: italic; }
        .ttd { margin-top: 22px; width: 100%; }
        .ttd td { font-size: 9px; }
        .ttd .kanan { text-align: center; width: 240px; }
        .garis { margin-top: 48px; border-bottom: 0.6px solid #000; }
    </style>
</head>
<body>

@forelse ($lembar as $l)
    <div class="lembar">
        <div class="kode">{{ str_replace('OP-', 'OP - ', $l['kode']) }}</div>
        <h1>CATATAN INSPEKSI BANGUNAN SUNGAI</h1>

        <table class="kepala">
            <tr>
                <td class="label">Nama bangunan</td><td class="titik">:</td><td>{{ $l['jenis'] }}</td>
            </tr>
            <tr>
                <td class="label">Lokasi bangunan</td><td class="titik">:</td><td>{{ $l['lokasi'] }}</td>
            </tr>
            <tr>
                <td class="label">Inspeksi dilakukan oleh</td><td class="titik">:</td><td>{{ $l['pemeriksa'] }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal pelaksanaan inspeksi</td><td class="titik">:</td><td>{{ $l['tanggal'] }}</td>
            </tr>
        </table>

        @if ($l['tipe'])
            <div class="tipe">Type bangunan : {{ $l['tipe'] }}</div>
        @endif

        <table class="isi">
            <thead>
                <tr>
                    <th class="no">No</th>
                    <th class="bagian">Bagian bangunan Yang Diamati</th>
                    <th class="pengamatan">Hasil Pengamatan</th>
                    <th class="tindakan">Tindakan yang perlu dilakukan</th>
                    <th class="koordinat">Koordinat</th>
                    <th>Ket.</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($l['baris'] as $b)
                    <tr>
                        <td class="no">{{ $b['no'] }}</td>
                        <td>{{ $b['bagian'] }}</td>
                        <td>{{ $b['pengamatan'] }}</td>
                        <td>{{ $b['tindakan'] }}</td>
                        <td class="koordinat">{!! nl2br(e($b['koordinat'])) !!}</td>
                        <td>{{ $b['keterangan'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="catatan">
            Isi Kolom 3 : harap dipilih yang relevan saja, Sedangkan yg tidak relevan harap dicoret
        </div>

        <table class="ttd">
            <tr>
                <td></td>
                <td class="kanan">
                    Tanda tangan pelaksana Inspeksi
                    <div class="garis"></div>
                    {{ $l['pemeriksa'] }}
                </td>
            </tr>
        </table>
    </div>
@empty
    <p>Laporan ini belum memuat bangunan yang diperiksa.</p>
@endforelse

</body>
</html>
