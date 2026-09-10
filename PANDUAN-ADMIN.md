# Panduan Admin — Sistem Informasi OP Sungai

Dinas PUPR Provinsi Kalimantan Selatan

Tulisan ini untuk **yang memakai sistem**, bukan yang membangunnya. Tidak ada
istilah pemrograman di sini kecuali pada satu bagian terakhir yang memang
ditujukan kepada petugas IT.

---

## Isi

1. [Masuk dan peran pengguna](#1-masuk-dan-peran-pengguna)
2. [Urutan pengisian data](#2-urutan-pengisian-data)
3. [Juru sungai](#3-juru-sungai)
4. [Batas wilayah dari BWS](#4-batas-wilayah-dari-bws)
5. [Membaca laporan yang masuk](#5-membaca-laporan-yang-masuk)
6. [Ketika laporan juru tidak sampai](#6-ketika-laporan-juru-tidak-sampai)
7. [Cadangan data](#7-cadangan-data)
8. [Untuk petugas IT](#8-untuk-petugas-it)

---

## 1. Masuk dan peran pengguna

Dua peran, dan bedanya tegas:

| Peran | Boleh | Tidak boleh |
|---|---|---|
| **Administrator** | Segalanya | — |
| **Peninjau** | Melihat laporan, rekapitulasi, peta, dan seluruh data master | Mengubah apa pun, membuka Pengguna dan Log Aktivitas |

Berikan **Peninjau** kepada pejabat yang cuma perlu memantau. Peran ini tidak
bisa merusak data walau salah pencet, jadi tidak perlu ragu memberikannya.

Akun dibuat di **Sistem → Pengguna**. Seorang admin tidak dapat menghapus atau
menonaktifkan akunnya sendiri — itu disengaja, supaya tidak ada keadaan di mana
sistem kehilangan seluruh administratornya.

> **Lupa password admin?** Tidak ada tombol "lupa password" di layar masuk. Yang
> bisa mengaturnya ulang adalah admin lain, lewat Sistem → Pengguna. **Karena
> itu buatlah minimal dua akun administrator.** Bila semuanya terkunci, hanya
> petugas IT yang bisa menolong (lihat bagian 8).

---

## 2. Urutan pengisian data

Data saling bergantung, dan urutannya tidak bisa dibalik:

```
Wilayah Sungai  →  Daerah Aliran Sungai (DAS)  →  Juru Sungai
```

DAS tidak bisa dibuat sebelum wilayah sungainya ada. Juru boleh didata lebih
dulu tanpa DAS bila wilayah tugasnya belum diputuskan — ia akan ditandai
**"Belum ditugaskan"** pada daftar juru sampai ditentukan.

**Kabupaten juga bertingkat.** Kabupaten ditulis lebih dulu pada Wilayah Sungai,
dan hanya yang tertulis di sana yang muncul sebagai pilihan pada DAS di
bawahnya. Bila ada kabupaten baru hasil pemekaran, cukup ketik namanya pada
formulir Wilayah Sungai — sistem membuatkannya sendiri. Nama yang belum dikenal
harus diklik pada baris "Tambah kabupaten baru", supaya salah ketik tidak
diam-diam melahirkan kabupaten palsu.

---

## 3. Juru sungai

### Menambah juru

**Master Data → Juru Sungai → Tambah Juru.** Yang wajib hanya nama dan
username. DAS boleh dikosongkan.

Username dan password yang dibuat di sini dipakai juru untuk masuk ke
**aplikasi Android**, bukan ke web ini.

### Juru lupa password

Buka **Master Data → Juru Sungai**, klik **Ubah** pada juru tersebut, isi
password baru, simpan. Kosongkan kolom password bila tidak ingin menggantinya.
Beri tahu juru password barunya, dan ia perlu masuk ulang di aplikasinya.

### Memindahkan juru ke DAS lain

Dua jalan, keduanya sah:

- Lewat **Ubah** pada juru itu, ganti DAS wilayah kerjanya; atau
- Lewat **Ubah DAS** tujuan, centang nama juru itu pada "Juru yang Bertugas".

Cara kedua memindahkannya dari DAS lamanya secara otomatis — keterangan
"kini di DAS Anu" pada tiap pilihan menunjukkan dari mana ia akan ditarik.

### Satu DAS boleh dipegang beberapa juru

DAS itu wilayah yang luas. Centang sebanyak yang diperlukan.

> **Juru yang dicopot dari semua DAS** masih bisa masuk ke aplikasi, tetapi
> laporan yang ia kirim tidak muncul di Rekapitulasi mana pun. Bila ia sudah
> tidak bertugas, **nonaktifkan akunnya** lewat Ubah, jangan cuma dicopot
> DAS-nya.

### Menonaktifkan juru

**Berlaku seketika, termasuk pada HP yang sudah dipegangnya.** Begitu
dinonaktifkan, aplikasinya menolak mengirim laporan dan meminta juru itu masuk
ulang — yang tidak akan berhasil selama akunnya masih nonaktif.

Menonaktifkan bersifat sementara dan aman dibatalkan: mengaktifkannya kembali
memulihkan keadaan tanpa perlu menghubungi juru atau menyuruhnya memasang ulang
aplikasi.

Laporan yang sudah pernah ia kirim **tetap utuh**. Menonaktifkan tidak menghapus
apa pun.

---

## 4. Batas wilayah dari BWS

**Master Data → Batas Wilayah.**

Batas DAS dan wilayah sungai tidak dibuat sendiri — ia diminta dari instansi
yang berwenang, lalu diunggah. Selama belum ada, DAS diwakili satu titik
perkiraan pada peta.

### Meminta berkasnya

Kirimkan permintaan ke **BWS Kalimantan III**, **BPDAS Barito**, atau unduh
sendiri dari **Ina-Geoportal** milik Badan Informasi Geospasial. Kalimat yang
perlu disebutkan sudah tertulis lengkap di halaman Batas Wilayah, tinggal
disalin.

Yang penting diminta: format **shapefile** atau **GeoJSON**, sistem koordinat
**WGS 84 (EPSG:4326)**.

### Mengunggah

1. Pilih batas itu untuk DAS atau untuk Wilayah Sungai.
2. Unggah berkasnya. Shapefile dikompres jadi satu `.zip` lebih dulu —
   seluruh isinya sekaligus (`.shp`, `.dbf`, `.shx`), bukan `.shp` saja.
3. Sistem menampilkan isinya berikut pasangan yang diusulkan. **Periksa
   dulu**, betulkan yang keliru, baru simpan.

Tidak ada yang tersimpan sebelum langkah ketiga. Batal kapan pun aman.

### Kalau berkasnya ditolak

Pesan penolakan menyebutkan sebabnya. Yang paling sering: koordinatnya belum
WGS 84. Langkah perbaikannya dengan QGIS (gratis, qgis.org) tertulis di halaman
itu juga.

Penolakan itu disengaja. Berkas berkoordinat salah yang diterima akan
menggambar seluruh batas di tempat yang keliru, dan tidak akan ada yang
menyadarinya.

---

## 5. Membaca laporan yang masuk

**Dashboard** menjawab satu pertanyaan: bulan ini siapa yang belum melapor, dan
laporan mana yang punya temuan bermasalah. Seluruh kartu di sana bisa dipencet
dan membawa ke daftar yang bersangkutan.

**Laporan OP** memuat daftarnya. Bisa disaring, diurutkan, dan ditampilkan
sebagai peta sebaran bangunan. Tombol **Export Excel** mengunduh yang sedang
tersaring, bukan seluruhnya.

**Rekapitulasi** memuat ringkasan per DAS untuk satu bulan, dengan baris TOTAL
di paling bawah. DAS yang belum punya juru ditandai, dan angkanya ditulis
sebagai tanda hubung — bukan nol — sebab "belum ada yang bertugas" berbeda
artinya dengan "sudah diperiksa, hasilnya nol".

**Detail laporan** memuat tombol **Blangko Excel** dan **Blangko PDF**, yaitu
blangko OP resmi yang siap dicetak dan diserahkan.

---

## 6. Ketika laporan juru tidak sampai

**Sistem → Sinkronisasi** memperlihatkan keadaan antrean di HP tiap juru:
berapa laporan masih tertunda, berapa yang gagal terkirim, dan kapan terakhir
kali HP-nya mengabarkan diri.

Aplikasi juru mencoba mengirim sendiri tiap lima belas menit selama ada
jaringan, dan juru mendapat notifikasi bila laporannya gagal terkirim.

Sesi masuk di HP berumur **30 hari**. Sesudah itu — atau bila akunnya
dinonaktifkan — aplikasi memberi tahu juru bahwa ia harus masuk ulang, dan
menahan laporannya di HP sampai itu dikerjakan. Tidak ada yang hilang.

Yang perlu diperiksa bila sebuah laporan tidak kunjung muncul:

1. **Apakah akunnya masih aktif?** Juru yang dinonaktifkan tidak bisa mengirim
   apa pun. Aplikasinya memberi tahu dia dan meminta masuk ulang, tetapi bila
   penonaktifannya tidak disengaja, dialah yang akan menelepon lebih dulu.
2. **Apakah HP-nya pernah mengabarkan diri?** Kolom terakhir pada halaman
   Sinkronisasi. Bila kosong sama sekali, juru itu belum pernah berhasil
   terhubung — biasanya salah alamat server atau belum pernah masuk.
3. **Apakah antreannya berisi?** Angka "tertunda" yang tidak turun-turun
   berarti HP-nya tidak mendapat jaringan.
4. **Apakah gagalnya banyak?** Angka "gagal" berarti kiriman sampai ke server
   tetapi ditolak. Ini perlu petugas IT.

Data laporan **tidak pernah hilang** karena gagal terkirim; ia tetap tersimpan
di HP juru sampai berhasil.

---

## 7. Cadangan data

Sistem membuat cadangan **tiap hari pukul 01.00** secara otomatis, berisi
seluruh data dan seluruh foto laporan. Empat belas cadangan terakhir disimpan,
yang lebih lama dibuang sendiri.

> ### Yang harus dikerjakan manusia
>
> Cadangan itu tersimpan **di server yang sama**. Ia melindungi dari
> penghapusan tak sengaja, tetapi **tidak melindungi dari kerusakan server**.
>
> Tunjuk satu orang untuk menyalin berkas cadangan terbaru ke tempat lain —
> Google Drive dinas, hard disk kantor, apa saja asal bukan mesin yang sama —
> **sekurang-kurangnya sebulan sekali**. Tanpa ini, satu disk mati berarti
> laporan lapangan berbulan-bulan hilang, dan itu pekerjaan yang tidak bisa
> diulang karena keadaan bangunannya sudah berubah.

Berkasnya ada di `storage/app/cadangan/` pada server, bernama seperti
`riverop-2026-09-10-0100.zip`. Di dalamnya ada `KETERANGAN.txt` yang memuat
cara mengembalikannya.

---

## 8. Untuk petugas IT

### Menjalankan cadangan di luar jadwal

```
php artisan riverop:cadangkan
```

Pilihan: `--simpan=30` untuk mengubah berapa yang disimpan, `--tanpa-foto`
untuk hanya basis datanya.

### Agar jadwal harian benar-benar berjalan

Server harus punya satu entri cron:

```
* * * * * cd /jalur/ke/river-op && php artisan schedule:run >> /dev/null 2>&1
```

Tanpa entri itu **tidak ada pesan galat apa pun** — cadangan cuma diam-diam
tidak pernah dibuat. Sesudah memasang, pastikan dengan `php artisan
schedule:list`.

### Mengembalikan dari cadangan

1. Siapkan basis data MySQL kosong.
2. Muat `basis-data.sql` dari dalam zip ke basis data itu.
3. Salin seluruh isi `foto/` ke `storage/app/public/`.
4. `php artisan storage:link`

### Seluruh administrator terkunci

Buat admin baru langsung dari baris perintah:

```
php artisan tinker
>>> App\Models\StafDinas::create(['nama' => 'Admin Baru', 'username' => 'admin2', 'password' => 'passwordbaru', 'role' => 'admin', 'aktif' => true]);
```

### Alamat server untuk aplikasi Android

Aplikasi Android membaca alamatnya dari `local.properties` saat dibangun, bukan
dari kode. Untuk build rilis, `riverop.baseUrl.release` **wajib** diisi dan
harus `https://` — bila tidak, pembangunannya menolak berjalan. Itu disengaja,
supaya aplikasi rilis tidak pernah terkirim dengan alamat pengembangan.

### Catatan pemeliharaan

Pustaka pembaca shapefile (`gasparesganga/php-shapefile`) memakai bentuk
penulisan yang sudah *deprecated* pada PHP 8.4 dan akan menjadi galat fatal
pada PHP 9. Sebelum menaikkan versi PHP ke 9, perbarui pustaka itu lebih dulu
dan pastikan halaman Batas Wilayah masih bisa membaca berkas `.zip`.
