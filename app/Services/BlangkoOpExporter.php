<?php

namespace App\Services;

use App\Models\ItemInspeksi;
use App\Models\LaporanOp;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Menuangkan satu laporan ke blangko OP resmi (OP-01.a s.d. OP-01.n).
 *
 * Berkas contoh dari dinas dipakai sebagai template agar tata letak, garis,
 * dan lebar kolomnya persis sama; isian ditaruh ke sel yang sudah ada, bukan
 * digambar ulang. Baris temuan dicocokkan lewat URUTAN bagian bangunan, bukan
 * namanya, karena penamaan di blangko berbeda tipis dari master data — ada
 * singkatan dan satu salah ketik yang sengaja tidak kami ubah.
 */
class BlangkoOpExporter
{
    private const BARIS_DATA_AWAL = 11;

    private const BULAN = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    public function untukLaporan(LaporanOp $laporan): Spreadsheet
    {
        $laporan->loadMissing([
            'juruSungai',
            'das',
            'itemInspeksi.jenisBangunan',
            'itemInspeksi.temuanKondisi.bagianBangunan',
        ]);

        $wb = IOFactory::load(resource_path('blangko/blangko-op.xlsx'));

        $terpakai = [];

        foreach ($laporan->itemInspeksi as $bangunan) {
            $namaSheet = $this->namaSheet($bangunan);

            if ($namaSheet === null || ! $wb->sheetNameExists($namaSheet)) {
                continue;
            }

            $dasar = $wb->getSheetByName($namaSheet);

            // Satu laporan dapat memuat beberapa bangunan berjenis sama,
            // sedangkan blangko hanya menyediakan satu lembar per jenis.
            // Lembarnya digandakan supaya tiap bangunan punya lembarnya sendiri.
            if (isset($terpakai[$namaSheet])) {
                $terpakai[$namaSheet]++;
                $lembar = clone $dasar;
                $lembar->setTitle($namaSheet . ' (' . $terpakai[$namaSheet] . ')');
                $wb->addSheet($lembar, $wb->getIndex($dasar) + $terpakai[$namaSheet] - 1);
            } else {
                $terpakai[$namaSheet] = 1;
                $lembar = $dasar;
            }

            $this->isiLembar($lembar, $laporan, $bangunan);
        }

        // Jenis bangunan yang tidak diperiksa tidak ikut dicetak, supaya
        // berkasnya tidak berisi belasan lembar kosong.
        foreach ($wb->getSheetNames() as $nama) {
            if (! isset($terpakai[$this->namaDasar($nama)])) {
                $wb->removeSheetByIndex($wb->getIndex($wb->getSheetByName($nama)));
            }
        }

        if ($wb->getSheetCount() === 0) {
            $wb->createSheet()
                ->setTitle('Kosong')
                ->setCellValue('A1', 'Laporan ini belum memuat bangunan yang diperiksa.');
        }

        $wb->setActiveSheetIndex(0);

        return $wb;
    }

    public function namaBerkas(LaporanOp $laporan): string
    {
        $bagian = array_filter([
            'Laporan Inspeksi Sungai',
            $laporan->das?->nama_das,
            self::BULAN[$laporan->bulan] ?? null,
            $laporan->tahun,
        ]);

        return preg_replace('/[^\w\s.\-]/u', '', implode(' ', $bagian)) . '.xlsx';
    }

    private function isiLembar(Worksheet $lembar, LaporanOp $laporan, ItemInspeksi $bangunan): void
    {
        $lembar->setCellValue('C5', ': ' . $this->barisLokasi($bangunan));
        $lembar->setCellValue('C6', ': ' . ($laporan->juruSungai?->nama ?? ''));
        $lembar->setCellValue('C7', ': ' . $this->tanggalPanjang($laporan->tanggal_inspeksi));

        $koordinat = $this->koordinat($bangunan);

        foreach ($bangunan->temuanKondisi as $temuan) {
            $urutan = $temuan->bagianBangunan?->urutan;

            if (! $urutan) {
                continue;
            }

            $baris = self::BARIS_DATA_AWAL + $urutan - 1;

            $lembar->setCellValue('C' . $baris, implode(' : ', $temuan->kondisiList()));
            $lembar->setCellValue('D' . $baris, $temuan->tindakan_diperlukan ?? '');
            $lembar->setCellValue('E' . $baris, $koordinat);
            $lembar->setCellValue('F' . $baris, $temuan->keterangan ?? '');
        }

        $this->isiTandaTangan($lembar, $laporan);
    }

    /**
     * Blangko meminta bentuk "Sungai <nama> pada ruas antara patok Km A s/d Km B".
     * Nama sungai belum dikumpulkan aplikasi juru, dan kilometernya pun masih
     * kosong, jadi bagian yang belum ada dibiarkan bertitik-titik persis seperti
     * blangko kosong — bukan dikarang.
     */
    private function barisLokasi(ItemInspeksi $bangunan): string
    {
        $km = fn ($nilai) => $nilai === null
            ? '......'
            : rtrim(rtrim(number_format((float) $nilai, 3, ',', '.'), '0'), ',');

        $lokasi = 'Sungai ............................ pada ruas antara patok Km '
            . $km($bangunan->lokasi_km_dari) . ' s/d Km ' . $km($bangunan->lokasi_km_sampai);

        return $bangunan->nama_bangunan
            ? $bangunan->nama_bangunan . ' — ' . $lokasi
            : $lokasi;
    }

    private function isiTandaTangan(Worksheet $lembar, LaporanOp $laporan): void
    {
        for ($r = self::BARIS_DATA_AWAL; $r <= $lembar->getHighestRow(); $r++) {
            $nilai = (string) $lembar->getCell('F' . $r)->getValue();

            if (str_contains($nilai, 'Tanda tangan pelaksana')) {
                $lembar->setCellValue('F' . ($r + 5), $laporan->juruSungai?->nama ?? '');

                return;
            }
        }
    }

    /** Blangko menuliskan koordinat dalam derajat-menit-detik, dua baris. */
    private function koordinat(ItemInspeksi $bangunan): string
    {
        if ($bangunan->koordinat_lat === null || $bangunan->koordinat_lng === null) {
            return '';
        }

        return $this->derajat((float) $bangunan->koordinat_lat, 'N', 'S') . "\n"
            . $this->derajat((float) $bangunan->koordinat_lng, 'E', 'W');
    }

    private function derajat(float $nilai, string $positif, string $negatif): string
    {
        $arah = $nilai >= 0 ? $positif : $negatif;
        $sisa = abs($nilai);

        $derajat = (int) $sisa;
        $menit = (int) (($sisa - $derajat) * 60);
        $detik = (int) round(((($sisa - $derajat) * 60) - $menit) * 60);

        if ($detik === 60) {
            $detik = 0;
            $menit++;
        }

        if ($menit === 60) {
            $menit = 0;
            $derajat++;
        }

        return $derajat . '°' . $menit . "'" . $detik . '" ' . $arah;
    }

    private function tanggalPanjang(mixed $tanggal): string
    {
        if (! $tanggal) {
            return '';
        }

        return $tanggal->day . ' ' . (self::BULAN[$tanggal->month] ?? '') . ' ' . $tanggal->year;
    }

    private function namaSheet(ItemInspeksi $bangunan): ?string
    {
        $kode = $bangunan->jenisBangunan?->kode;

        return $kode ? str_replace('OP-', 'OP - ', $kode) : null;
    }

    private function namaDasar(string $judul): string
    {
        return trim(preg_replace('/\s*\(\d+\)$/', '', $judul));
    }
}
