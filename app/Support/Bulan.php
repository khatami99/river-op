<?php

namespace App\Support;

class Bulan
{
    public const NAMA = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public static function nama(?int $bulan): string
    {
        return self::NAMA[$bulan] ?? '-';
    }

    public static function singkat(?int $bulan): string
    {
        $nama = self::nama($bulan);

        return $nama === '-' ? '-' : substr($nama, 0, 3);
    }
}
