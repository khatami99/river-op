<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisBangunanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kode' => 'OP-01.a',
                'nama' => 'Palung Sungai / Sudetan / Floodway',
                'tipe_konstruksi' => 'Sudetan, kanal banjir/floodway',
                'urutan' => 1,
                'bagian' => [
                    [
                        'nama' => 'Tebing',
                        'urutan' => 1,
                        'kondisi' => ['Lereng Longsor', 'Dasar Tergerus'],
                    ],
                    [
                        'nama' => 'Kegiatan di palung dan bantaran sungai',
                        'urutan' => 2,
                        'kondisi' => ['Penambangan pasir', 'Bangunan liar', 'Kegiatan pertanian'],
                    ],
                    [
                        'nama' => 'Sempadan Sungai',
                        'urutan' => 3,
                        'kondisi' => ['Bangunan liar', 'Patok batas sempadan rusak'],
                    ],
                ],
            ],
            [
                'kode' => 'OP-01.b',
                'nama' => 'Tanggul Banjir',
                'tipe_konstruksi' => 'Konstruksi tanah timbun',
                'urutan' => 2,
                'bagian' => [
                    [
                        'nama' => 'Puncak tanggul',
                        'urutan' => 1,
                        'kondisi' => ['Retak', 'Ambles', 'Berlubang', 'Tumbuh Semak Liar'],
                    ],
                    [
                        'nama' => 'Lereng tanggul',
                        'urutan' => 2,
                        'kondisi' => ['Retak', 'Longsor', 'Berlubang', 'Gebalan Rumput Botak', 'Tumbuh Semak Liar'],
                    ],
                    [
                        'nama' => 'Sistem Drainase tanggul',
                        'urutan' => 3,
                        'kondisi' => ['Tertutup Tanah', 'Tertutup Sampah'],
                    ],
                    [
                        'nama' => 'Tanah di sekitar kaki tanggul',
                        'urutan' => 4,
                        'kondisi' => ['Longsor'],
                    ],
                    [
                        'nama' => 'Patok Kilometer',
                        'urutan' => 5,
                        'kondisi' => ['Miring', 'Rusak', 'Hilang'],
                    ],
                    [
                        'nama' => 'Portal tanggul',
                        'urutan' => 6,
                        'kondisi' => ['Miring', 'Rusak'],
                    ],
                ],
            ],
            [
                'kode' => 'OP-01.c',
                'nama' => 'Tanggul Banjir atau Revetmen',
                'tipe_konstruksi' => 'Tembok pasangan batu kali',
                'urutan' => 3,
                'bagian' => [
                    [
                        'nama' => 'Puncak bangunan',
                        'urutan' => 1,
                        'kondisi' => ['Retak', 'Patah', 'Ambles', 'Berlubang', 'Tumbuh Semak Liar'],
                    ],
                    [
                        'nama' => 'Lereng',
                        'urutan' => 2,
                        'kondisi' => ['Retak', 'Longsor', 'Berlubang', 'Pasangan Batu Terlepas', 'Tumbuh Semak Liar'],
                    ],
                    [
                        'nama' => 'Bagian dasar bangunan',
                        'urutan' => 3,
                        'kondisi' => ['Gerowong', 'Tergerus'],
                    ],
                    [
                        'nama' => 'Sistem drainase',
                        'urutan' => 4,
                        'kondisi' => ['Tertutup tanah', 'Tertutup sampah'],
                    ],
                ],
            ],
            [
                'kode' => 'OP-01.d',
                'nama' => 'Turap atau Revetment',
                'tipe_konstruksi' => 'Konstruksi beton atau baja',
                'urutan' => 4,
                'bagian' => [
                    [
                        'nama' => 'Puncak bangunan',
                        'urutan' => 1,
                        'kondisi' => ['Pecah', 'Bengkok'],
                    ],
                    [
                        'nama' => 'Lereng',
                        'urutan' => 2,
                        'kondisi' => ['Miring', 'Melengkung', 'Retak', 'Patah', 'Berlubang', 'Keropos', 'Berkarat'],
                    ],
                    [
                        'nama' => 'Bagian dasar bangunan',
                        'urutan' => 3,
                        'kondisi' => ['Tergerus', 'Keropos'],
                    ],
                    [
                        'nama' => 'Sistem drainase',
                        'urutan' => 4,
                        'kondisi' => ['Tertutup tanah', 'Tertutup sampah'],
                    ],
                ],
            ],
            [
                'kode' => 'OP-01.e',
                'nama' => 'Krib',
                'tipe_konstruksi' => 'Konstruksi balok kayu atau beton',
                'urutan' => 5,
                'bagian' => [
                    [
                        'nama' => 'Tiang krib',
                        'urutan' => 1,
                        'kondisi' => ['Miring', 'Patah', 'Balok kayu lapuk'],
                    ],
                    [
                        'nama' => 'Ruang bebas diantara tiang krib',
                        'urutan' => 2,
                        'kondisi' => ['Sampah atau sangkrah tersangkut'],
                    ],
                    [
                        'nama' => 'Sistem sambungan antar tiang',
                        'urutan' => 3,
                        'kondisi' => ['Terlepas', 'Putus', 'Bengkok'],
                    ],
                    [
                        'nama' => 'Bagian kaki krib',
                        'urutan' => 4,
                        'kondisi' => ['Tergerus', 'Gerowong', 'Menggantung'],
                    ],
                    [
                        'nama' => 'Perikatan dengan tebing sungai di belakang krib',
                        'urutan' => 5,
                        'kondisi' => ['Tergerus', 'Longsor', 'Terlepas dari krib'],
                    ],
                ],
            ],
            [
                'kode' => 'OP-01.f',
                'nama' => 'Pelimpah Banjir',
                'tipe_konstruksi' => 'Konstruksi pasangan batu / beton / tumpukan bronjong',
                'urutan' => 6,
                'bagian' => [
                    [
                        'nama' => 'Formasi tumpukan bronjong',
                        'urutan' => 1,
                        'kondisi' => ['Miring', 'Melorot', 'Patah'],
                    ],
                    [
                        'nama' => 'Bagian dasar krib',
                        'urutan' => 2,
                        'kondisi' => ['Tergerus', 'Gerowong', 'Menggantung'],
                    ],
                    [
                        'nama' => 'Kawat bronjong',
                        'urutan' => 3,
                        'kondisi' => ['Putus', 'Berkarat'],
                    ],
                    [
                        'nama' => 'Batuan pengisi',
                        'urutan' => 4,
                        'kondisi' => ['Terlepas keluar dari bronjong'],
                    ],
                    [
                        'nama' => 'Perikatan dengan tebing sungai di belakang krib',
                        'urutan' => 5,
                        'kondisi' => ['Tergerus', 'Longsor', 'Terlepas dari krib'],
                    ],
                ],
            ],
            [
                'kode' => 'OP-01.g',
                'nama' => 'Tanggul Banjir dengan Pelimpah',
                'tipe_konstruksi' => 'Konstruksi tanah timbun',
                'urutan' => 7,
                'bagian' => [
                    [
                        'nama' => 'Puncak Pelimpah',
                        'urutan' => 1,
                        'kondisi' => ['Retak', 'Pecah', 'Pasangan batu terlepas', 'Kawat bronjong putus'],
                    ],
                    [
                        'nama' => 'Stilling basin (Kolam olak)',
                        'urutan' => 2,
                        'kondisi' => ['Retak', 'Patah', 'Tergerus bagian ujungnya', 'Gerowong', 'Kawat bronjong putus'],
                    ],
                    [
                        'nama' => 'Pangkal kiri / kanan pelimpah',
                        'urutan' => 3,
                        'kondisi' => ['Struktur terlepas dari tebing sungai'],
                    ],
                    [
                        'nama' => 'Pintu pelimpah',
                        'urutan' => 4,
                        'kondisi' => ['Daun pintu rusak', 'Tak dapat tertutup sempurna', 'Mesin penggerak rusak'],
                    ],
                    [
                        'nama' => 'Tembok sayap',
                        'urutan' => 5,
                        'kondisi' => ['Retak', 'Pecah', 'Kawat bronjong putus'],
                    ],
                    [
                        'nama' => 'Palung sungai di hulu',
                        'urutan' => 6,
                        'kondisi' => ['Sangkrah menyangkut', 'Tebingnya longsor'],
                    ],
                    [
                        'nama' => 'Palung sungai di hilir',
                        'urutan' => 7,
                        'kondisi' => ['Degradasi dasar sungai', 'Tebing tergerus', 'Tebing longsor'],
                    ],
                    [
                        'nama' => 'Papan peilskal',
                        'urutan' => 8,
                        'kondisi' => ['Tak terbaca', 'Rusak'],
                    ],
                ],
            ],
            [
                'kode' => 'OP-01.h',
                'nama' => 'Bangunan Pengendali / Pembagi Aliran / Bendung Gerak',
                'tipe_konstruksi' => 'Konstruksi tanah timbun',
                'urutan' => 8,
                'bagian' => [
                    [
                        'nama' => 'Puncak Pelimpah',
                        'urutan' => 1,
                        'kondisi' => ['Retak', 'Pecah', 'Pasangan batu terlepas'],
                    ],
                    [
                        'nama' => 'Stilling basin (Kolam olak)',
                        'urutan' => 2,
                        'kondisi' => ['Retak', 'Patah', 'Tergerus bagian ujungnya', 'Gerowong'],
                    ],
                    [
                        'nama' => 'Struktur dasar bangunan',
                        'urutan' => 3,
                        'kondisi' => ['Tergerus', 'Gerowong', 'Menggantung'],
                    ],
                    [
                        'nama' => 'Tembok sayap',
                        'urutan' => 4,
                        'kondisi' => ['Retak', 'Pecah', 'Pasangan batu terlepas'],
                    ],
                    [
                        'nama' => 'Pintu bangunan',
                        'urutan' => 5,
                        'kondisi' => ['Terganjal', 'Daun pintu rusak', 'Tak dapat tertutup sempurna', 'Tak dapat terbuka sempurna', 'Stang bengkok', 'Mesin penggerak rusak'],
                    ],
                    [
                        'nama' => 'Palung sungai bagian hulu',
                        'urutan' => 6,
                        'kondisi' => ['Sangkrah menyangkut', 'Tebingnya longsor'],
                    ],
                    [
                        'nama' => 'Papan duga air (peilskal)',
                        'urutan' => 7,
                        'kondisi' => ['Tak terbaca', 'Rusak'],
                    ],
                ],
            ],
            [
                'kode' => 'OP-01.i',
                'nama' => 'Bendung Karet',
                'tipe_konstruksi' => 'Berisi air atau berisi udara',
                'urutan' => 9,
                'bagian' => [
                    [
                        'nama' => 'Karet bendung',
                        'urutan' => 1,
                        'kondisi' => ['Retak', 'Bocor'],
                    ],
                    [
                        'nama' => 'Pompa bendung',
                        'urutan' => 2,
                        'kondisi' => ['Tak berfungsi maksimal', 'Rusak'],
                    ],
                    [
                        'nama' => 'Stilling basin (kolam olak)',
                        'urutan' => 3,
                        'kondisi' => ['Retak', 'Patah', 'Tergerus', 'Gerowong'],
                    ],
                    [
                        'nama' => 'Struktur dasar bangunan',
                        'urutan' => 4,
                        'kondisi' => ['Tergerus', 'Gerowong', 'Menggantung'],
                    ],
                    [
                        'nama' => 'Tembok sayap',
                        'urutan' => 5,
                        'kondisi' => ['Retak', 'Pecah', 'Pasangan batu terlepas'],
                    ],
                    [
                        'nama' => 'Pintu pengambilan',
                        'urutan' => 6,
                        'kondisi' => ['Daun pintu rusak', 'Terganjal', 'Tak dapat tertutup/terbuka rapat', 'Alat penggerak rusak'],
                    ],
                    [
                        'nama' => 'Palung sungai bagian hulu',
                        'urutan' => 7,
                        'kondisi' => ['Sangkrah menyangkut', 'Tebingnya longsor'],
                    ],
                    [
                        'nama' => 'Standby genset / power house',
                        'urutan' => 8,
                        'kondisi' => ['Mati tak berfungsi', 'Rusak', 'Lampu penerangan mati'],
                    ],
                    [
                        'nama' => 'Papan duga air (peilskal)',
                        'urutan' => 9,
                        'kondisi' => ['Tak terbaca', 'Rusak'],
                    ],
                ],
            ],
            [
                'kode' => 'OP-01.j',
                'nama' => 'Waduk Banjir',
                'tipe_konstruksi' => null,
                'urutan' => 10,
                'bagian' => [
                    [
                        'nama' => 'Pintu inlet / outlet',
                        'urutan' => 1,
                        'kondisi' => ['Terganjal', 'Daun pintu rusak', 'Tak dapat tertutup/terbuka rapat', 'Stang bengkok', 'Alat penggerak rusak'],
                    ],
                    [
                        'nama' => 'Puncak tanggul sekeliling waduk',
                        'urutan' => 2,
                        'kondisi' => ['Amblas', 'Bocor', 'Lereng longsor', 'Tumbuh semak liar', 'Gebalan rumput botak'],
                    ],
                    [
                        'nama' => 'Lereng tanggul',
                        'urutan' => 3,
                        'kondisi' => ['Longsor', 'Berlubang', 'Tumbuh semak liar', 'Gebalan rumput botak'],
                    ],
                    [
                        'nama' => 'Kolam waduk',
                        'urutan' => 4,
                        'kondisi' => ['Penuh sedimen', 'Penuh gulma', 'Ada bangunan liar'],
                    ],
                    [
                        'nama' => 'Papan duga (peilskal)',
                        'urutan' => 5,
                        'kondisi' => ['Tak terbaca', 'Rusak'],
                    ],
                ],
            ],
            [
                'kode' => 'OP-01.k',
                'nama' => 'Pompa Banjir',
                'tipe_konstruksi' => 'Konstruksi tanah timbun',
                'urutan' => 11,
                'bagian' => [
                    [
                        'nama' => 'Rumah pompa',
                        'urutan' => 1,
                        'kondisi' => ['Atap bocor', 'Pintu rusak'],
                    ],
                    [
                        'nama' => 'Mesin pompa',
                        'urutan' => 2,
                        'kondisi' => ['Rusak', 'Tak berfungsi'],
                    ],
                    [
                        'nama' => 'Standby genset',
                        'urutan' => 3,
                        'kondisi' => ['Mati tak berfungsi', 'Rusak'],
                    ],
                    [
                        'nama' => 'Lampu penerangan',
                        'urutan' => 4,
                        'kondisi' => ['Lampu penerangan mati'],
                    ],
                    [
                        'nama' => 'Sampah di hulu bendung',
                        'urutan' => 5,
                        'kondisi' => ['Menutup saringan sampah'],
                    ],
                    [
                        'nama' => 'Pintu outlet',
                        'urutan' => 6,
                        'kondisi' => ['Terganjal', 'Daun pintu rusak', 'Tak dapat tertutup/terbuka rapat', 'Stang bengkok', 'Alat penggerak rusak'],
                    ],
                ],
            ],
            [
                'kode' => 'OP-01.l',
                'nama' => 'Groundsill',
                'tipe_konstruksi' => 'Konstruksi batu bronjong',
                'urutan' => 12,
                'bagian' => [
                    [
                        'nama' => 'Konstruksi tubuh groundsill',
                        'urutan' => 1,
                        'kondisi' => ['Kawat bronjong putus', 'Ada bagian yang ambles'],
                    ],
                    [
                        'nama' => 'Stilling basin (kolam olak)',
                        'urutan' => 2,
                        'kondisi' => ['Patah', 'Tergerus bagian ujungnya', 'Gerowong', 'Kawat bronjong putus'],
                    ],
                    [
                        'nama' => 'Pangkal kiri/kanan groundsill',
                        'urutan' => 3,
                        'kondisi' => ['Struktur terlepas dari tebing sungai'],
                    ],
                    [
                        'nama' => 'Palung sungai di hulu',
                        'urutan' => 4,
                        'kondisi' => ['Sangkrah menyangkut', 'Tebingnya longsor'],
                    ],
                ],
            ],
            [
                'kode' => 'OP-01.m',
                'nama' => 'Pompa Groundsill',
                'tipe_konstruksi' => 'Konstruksi pasangan batu/beton',
                'urutan' => 13,
                'bagian' => [
                    [
                        'nama' => 'Konstruksi tubuh groundsill',
                        'urutan' => 1,
                        'kondisi' => ['Pecah', 'Retak', 'Putus'],
                    ],
                    [
                        'nama' => 'Stilling basin (kolam olak)',
                        'urutan' => 2,
                        'kondisi' => ['Patah', 'Tergerus bagian ujungnya', 'Gerowong'],
                    ],
                    [
                        'nama' => 'Pangkal kiri/kanan groundsill',
                        'urutan' => 3,
                        'kondisi' => ['Struktur terlepas dari tebing sungai'],
                    ],
                    [
                        'nama' => 'Palung sungai di hulu',
                        'urutan' => 4,
                        'kondisi' => ['Sangkrah menyangkut', 'Tebingnya longsor'],
                    ],
                ],
            ],
            [
                'kode' => 'OP-01.n',
                'nama' => 'Tidal Gate (Salinity Barrier)',
                'tipe_konstruksi' => null,
                'urutan' => 14,
                'bagian' => [
                    [
                        'nama' => 'Pintu bangunan',
                        'urutan' => 1,
                        'kondisi' => ['Macet', 'Seal bocor', 'Terganjal'],
                    ],
                    [
                        'nama' => 'Sistem penggerak pintu',
                        'urutan' => 2,
                        'kondisi' => ['Tak berfungsi', 'Macet'],
                    ],
                    [
                        'nama' => 'Power house',
                        'urutan' => 3,
                        'kondisi' => ['Atap bocor', 'Dinding/pintu rusak', 'Lampu putus'],
                    ],
                    [
                        'nama' => 'Bagian dasar bangunan',
                        'urutan' => 4,
                        'kondisi' => ['Retak', 'Pecah'],
                    ],
                    [
                        'nama' => 'Tanggul di hulu bangunan',
                        'urutan' => 5,
                        'kondisi' => ['Ambles', 'Tertutup semak'],
                    ],
                    [
                        'nama' => 'Sedimen di hulu dan hilir bangunan',
                        'urutan' => 6,
                        'kondisi' => ['Menumpuk'],
                    ],
                    [
                        'nama' => 'Papan duga (peilskal)',
                        'urutan' => 7,
                        'kondisi' => ['Tak terbaca', 'Rusak'],
                    ],
                ],
            ],
        ];

        foreach ($data as $urutan => $jenis) {
            $jenisBangunanId = DB::table('jenis_bangunan')->insertGetId([
                'kode'             => $jenis['kode'],
                'nama'             => $jenis['nama'],
                'tipe_konstruksi'  => $jenis['tipe_konstruksi'],
                'urutan'           => $jenis['urutan'],
                'aktif'            => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            foreach ($jenis['bagian'] as $bagian) {
                $bagianId = DB::table('bagian_bangunan')->insertGetId([
                    'jenis_bangunan_id' => $jenisBangunanId,
                    'nama'              => $bagian['nama'],
                    'urutan'            => $bagian['urutan'],
                    'aktif'             => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                foreach ($bagian['kondisi'] as $index => $kondisi) {
                    DB::table('pilihan_kondisi')->insert([
                        'bagian_bangunan_id' => $bagianId,
                        'kondisi'            => $kondisi,
                        'urutan'             => $index + 1,
                        'aktif'              => true,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                }
            }
        }
    }
}
