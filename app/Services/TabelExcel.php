<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Penyusun berkas Excel sederhana untuk export tabel.
 *
 * Dipakai bersama oleh export daftar laporan dan export rekapitulasi supaya
 * keduanya terlihat sama dan tidak perlu masing-masing mengatur ulang tebal
 * huruf, garis, dan lebar kolom.
 *
 * Sebelumnya keduanya berupa CSV. CSV memang terbuka di Excel, tetapi bukan
 * berkas Excel: tidak ada format, lebar kolomnya sempit, dan angka bisa
 * terbaca sebagai teks sehingga tidak dapat dijumlahkan.
 */
class TabelExcel
{
    private Spreadsheet $wb;
    private Worksheet $lembar;
    private int $baris = 1;
    private int $kolomTerbanyak = 1;

    public function __construct(string $judulLembar = 'Data')
    {
        $this->wb = new Spreadsheet();
        $this->lembar = $this->wb->getActiveSheet();

        // Judul lembar dibatasi 31 karakter oleh Excel, dan beberapa tanda
        // baca ditolak; dibersihkan agar berkasnya tidak gagal dibuka.
        $this->lembar->setTitle(mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $judulLembar), 0, 31));
    }

    public function judul(string $teks): static
    {
        $sel = 'A' . $this->baris;
        $this->lembar->setCellValue($sel, $teks);
        $this->lembar->getStyle($sel)->getFont()->setBold(true)->setSize(13);
        $this->baris++;

        return $this;
    }

    public function keterangan(string $label, string $nilai): static
    {
        $this->lembar->setCellValue('A' . $this->baris, $label);
        $this->lembar->setCellValue('B' . $this->baris, $nilai);
        $this->lembar->getStyle('A' . $this->baris)->getFont()->setBold(true);
        $this->baris++;

        return $this;
    }

    public function kosong(): static
    {
        $this->baris++;

        return $this;
    }

    /** @param list<string> $kolom */
    public function kepala(array $kolom): static
    {
        $this->tulisBaris($kolom);

        $terakhir = $this->hurufKolom(count($kolom));
        $rentang = 'A' . ($this->baris - 1) . ':' . $terakhir . ($this->baris - 1);

        $gaya = $this->lembar->getStyle($rentang);
        $gaya->getFont()->setBold(true);
        $gaya->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0A2C48');
        $gaya->getFont()->getColor()->setRGB('FFFFFF');
        $gaya->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $gaya->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $this;
    }

    /** @param list<mixed> $nilai */
    public function baris(array $nilai, bool $tebal = false): static
    {
        $this->tulisBaris($nilai);

        $terakhir = $this->hurufKolom(count($nilai));
        $rentang = 'A' . ($this->baris - 1) . ':' . $terakhir . ($this->baris - 1);

        $this->lembar->getStyle($rentang)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        if ($tebal) {
            $this->lembar->getStyle($rentang)->getFont()->setBold(true);
        }

        return $this;
    }

    public function selesai(): Spreadsheet
    {
        for ($i = 1; $i <= $this->kolomTerbanyak; $i++) {
            $this->lembar->getColumnDimension($this->hurufKolom($i))->setAutoSize(true);
        }

        $this->lembar->setSelectedCell('A1');

        return $this->wb;
    }

    /**
     * Angka ditulis sebagai angka, teks sebagai teks. Bedanya terasa saat
     * pegawai menjumlahkan sendiri kolomnya di Excel — inilah yang tidak bisa
     * dilakukan CSV.
     */
    private function tulisBaris(array $nilai): void
    {
        $kolom = 1;

        foreach ($nilai as $isi) {
            $sel = $this->hurufKolom($kolom) . $this->baris;

            if (is_int($isi) || is_float($isi)) {
                $this->lembar->setCellValueExplicit(
                    $sel,
                    $isi,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                );
            } else {
                $this->lembar->setCellValue($sel, $isi ?? '');
            }

            $kolom++;
        }

        $this->kolomTerbanyak = max($this->kolomTerbanyak, count($nilai));
        $this->baris++;
    }

    private function hurufKolom(int $nomor): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($nomor);
    }
}
