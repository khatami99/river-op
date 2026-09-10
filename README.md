# River OP — Backend API & Dashboard Dinas

Sistem digitalisasi pelaporan Operasi & Pemeliharaan (OP) Sungai
Dinas PUPR Provinsi Kalimantan Selatan

Satu aplikasi Laravel melayani dua konsumen:

- **API** untuk aplikasi Android yang dipakai juru sungai di lapangan
- **Dashboard web** untuk staf dinas di kantor

---

## Dokumen lain

| | |
|---|---|
| [PANDUAN-ADMIN.md](PANDUAN-ADMIN.md) | Untuk staf dinas yang memakai sistem, bukan yang membangunnya |
| [PANDUAN-DEMO.md](PANDUAN-DEMO.md) | Untuk yang memperagakan sistem di hadapan pimpinan |
| [TEROWONGAN.md](TEROWONGAN.md) | Menyiarkan sistem dari laptop sendiri lewat Cloudflare Tunnel |
| [TODO.md](TODO.md) | Yang sudah selesai, yang belum, dan risiko yang harus diketahui pemelihara |

---

## Tech Stack

- **Framework:** Laravel 12 (PHP 8.2+)
- **Database:** MySQL
- **Auth API:** Laravel Sanctum (token)
- **Auth Dashboard:** Session, guard `staf`
- **Frontend Dashboard:** Blade + Tailwind CSS 4 + Vite

---

## Requirements

- PHP >= 8.2
- Composer
- Node.js & npm — wajib, dashboard tidak akan bergaya tanpa build asset
- MySQL

---

## Instalasi

```bash
git clone https://github.com/khatami99/river-op.git
cd river-op
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=river_op_db
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migration, seeder, dan siapkan penyimpanan foto:

```bash
php artisan migrate --seed
php artisan storage:link
```

Menjalankan tes:

```bash
php artisan test
```

Tes memakai SQLite dalam memori, jadi tidak menyentuh database pengembangan.

Build asset frontend:

```bash
npm run build
```

Jalankan server:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Dashboard: <http://127.0.0.1:8000/login>

> Gunakan `--host=0.0.0.0` supaya aplikasi Android di perangkat lain
> pada jaringan yang sama bisa mencapai API.

### Saat mengembangkan tampilan

Jalankan Vite agar perubahan Blade langsung terlihat tanpa build ulang:

```bash
npm run dev
```

---

## Dashboard Dinas

Diakses staf dinas melalui browser. Juru sungai **tidak** memakai halaman ini.

| Halaman | Rute | Isi |
|---------|------|-----|
| Login | `/login` | Autentikasi staf, guard `staf` |
| Dashboard | `/dashboard` | Ringkasan periode, progres pelaporan, daftar juru yang belum melapor |
| Laporan OP | `/laporan` | Filter bulan, tahun, sungai, juru, dan pencarian |
| Detail laporan | `/laporan/{id}` | Tampilan gaya blangko OP beserta galeri foto |
| Unduh Excel | `/laporan/export` | Berkas `.xlsx` mengikuti penyaring yang sedang aktif |
| Juru sungai | `/juru` | Daftar juru dan kalender kepatuhan 12 bulan |
| DAS | `/das` | Peta DAS di atas, daftar berpaginasi di bawahnya |
| Wilayah sungai | `/wilayah` | Wilayah sungai beserta DAS di bawahnya |
| Rekapitulasi | `/rekap` | Rekap per DAS, daftar juru belum melapor, export |
| Batas Wilayah | `/batas` | Memasukkan poligon batas DAS dan WS dari berkas shapefile/GeoJSON |
| Sinkronisasi | `/sinkronisasi` | Keadaan antrean di tiap HP juru, termasuk yang belum pernah melapor |
| Log Aktivitas | `/log` | Jejak perubahan data master oleh staf, khusus admin |
| Pengguna | `/staf` | Manajemen akun staf dinas — hanya admin |

### Peran staf dinas

| | Admin | Viewer |
|---|---|---|
| Melihat laporan, juru, DAS, wilayah | ✅ | ✅ |
| Export | ✅ | ✅ |
| Menambah, mengubah, menghapus data master | ✅ | ❌ |
| Manajemen akun staf | ✅ | ❌ |

Pembatasan ditegakkan middleware `admin` pada rute, bukan sekadar menyembunyikan
tombol, sehingga permintaan yang ditembak langsung tetap ditolak dengan 403.

Penghapusan ditolak selama data masih dipakai: juru yang sudah memiliki laporan,
DAS yang masih dipegang juru, dan wilayah sungai yang masih menaungi DAS. Untuk
kasus itu gunakan penonaktifan agar riwayat tetap utuh. Admin juga tidak dapat
menghapus, menurunkan peran, atau menonaktifkan akunnya sendiri.

---

## API Endpoints

Dipakai aplikasi Android. Autentikasi memakai token Sanctum.

### Auth
| Method | Endpoint | Deskripsi | Auth |
|--------|----------|-----------|------|
| POST | `/api/auth/login` | Login juru sungai | ❌ |
| POST | `/api/auth/logout` | Logout | ✅ |

### Master Data
| Method | Endpoint | Deskripsi | Auth |
|--------|----------|-----------|------|
| GET | `/api/master/sync` | Ambil semua master data | ✅ |
| GET | `/api/master/profil` | Profil juru sungai yang login | ✅ |

### Laporan
| Method | Endpoint | Deskripsi | Auth |
|--------|----------|-----------|------|
| POST | `/api/laporan/sync` | Sync batch laporan dari HP | ✅ |
| POST | `/api/laporan/upload-foto` | Unggah satu foto, mengembalikan URL | ✅ |
| POST | `/api/sinkronisasi/status` | HP mengabarkan keadaan antreannya sendiri | ✅ |

Seluruh rute API dijaga `auth:sanctum` **dan** `juru.aktif`. Yang kedua menolak
token milik juru yang sudah dinonaktifkan dinas — tanpa itu, menonaktifkan juru
tidak mengerjakan apa pun terhadap HP yang sudah memegang token.

Permintaan tak terautentikasi ke `api/*` selalu dijawab **401**, tidak pernah
dialihkan ke halaman login web. Klien yang mengikuti pengalihan akan menerima
HTML berkode 200 dan menyangka permintaannya berhasil.

---

## Pemisahan Autentikasi

API dan dashboard berbagi satu aplikasi, tetapi autentikasinya sengaja dipisah:

| | Juru sungai | Staf dinas |
|---|---|---|
| Rute | `routes/api.php` | `routes/web.php` |
| Mekanisme | Token Sanctum, stateless | Session + CSRF |
| Middleware | `auth:sanctum` | `auth:staf` |
| Model | `JuruSungai` | `StafDinas` |

> `StafDinas` sengaja **tidak** memakai trait `HasApiTokens`. Sanctum menyimpan
> token secara polymorphic, sehingga middleware `auth:sanctum` akan menerima token
> dari model mana pun yang memiliki trait tersebut. Tanpa pembatasan ini, token milik
> staf dinas bisa dipakai menembak endpoint API milik juru sungai. Dashboard tidak
> membutuhkan token karena sudah memakai session.

---

## Struktur Database

```
wilayah_sungai
    └── das ──── das_kabupaten ──── kabupaten
            ├── juru_sungai
            └── laporan_op
                    └── item_inspeksi
                            └── temuan_kondisi

jenis_bangunan (OP-01.a s/d OP-01.n)
    └── bagian_bangunan
            └── pilihan_kondisi

staf_dinas
```

**Mengapa ada tingkat DAS.** Hanya ada satu wilayah sungai yang menjadi wewenang
Provinsi Kalimantan Selatan, yaitu WS Cengal-Batulicin (Kode WS 04.10.B), yang
menaungi 62 DAS. Pembagian tugas juru sungai dilakukan per DAS (Daerah Aliran
Sungai), sehingga juru dan laporan menunjuk ke `das`, bukan langsung ke
`wilayah_sungai`. Tabel wilayah sungai tetap dipertahankan karena wilayah sungai
baru bisa muncul di kemudian hari — menambahkannya cukup lewat menu, tanpa ubah kode.

**Mengapa kabupaten menempel di DAS dan boleh lebih dari satu.** Batas DAS
mengikuti punggung bukit, sedangkan batas kabupaten digambar manusia, jadi satu
DAS bisa melintasi dua kabupaten. Sebelumnya kabupaten menempel di wilayah
sungai, dan itu keliru: WS Cengal-Batulicin sendiri membentang di Kotabaru,
Tanah Bumbu, dan Tanah Laut.

Nilai ini **label, bukan angka** — jangan dipakai menjumlahkan laporan per
kabupaten, karena DAS lintas kabupaten akan terhitung dua kali. Perhitungan per
kabupaten baru sahih bila sudah ada poligon batas wilayah dan dinilai dari titik
GPS tiap bangunan.

**Titik DAS itu perkiraan.** DAS adalah wilayah, bukan titik. Koordinat pada
`das` semata-mata pegangan agar DAS dapat ditandai dan diklik di peta, dengan
ketelitian sekitar sepuluh kilometer, dan boleh dikosongkan. Dari 62 DAS, baru
16 yang terisi.

Satu laporan mewakili satu kunjungan dan dapat memuat banyak bangunan, termasuk
beberapa bangunan berjenis sama yang dibedakan lewat kolom `nama_bangunan`.
Setiap temuan menyimpan sampai tiga foto: `foto`, `foto_2`, dan `foto_3`.

---

## Akun Default (Development)

**Juru Sungai:**
| Username | Password | DAS |
|----------|----------|-----|
| ahmad_juru | password | DAS Sampanahan |
| bambang_juru | password | DAS Cantung |
| candra_juru | password | DAS Batulicin |
| dedi_juru | password | DAS Kusan |
| eko_juru | password | DAS Satui |
| fajar_juru | password | DAS Kintap |

**Staf Dinas:**
| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | admin |

---

## Sistem Warna Dashboard

Palet diturunkan dari logo aplikasi dan logo Provinsi Kalimantan Selatan.
Token didefinisikan di `resources/css/app.css`, dan nilai yang sama dipakai
aplikasi Android di `app/src/main/res/values/colors.xml`.

| Token | Peran | Asal |
|-------|-------|------|
| `sungai-50…950` | Warna primer: sidebar, kartu utama, aksi | Gelombang pada logo aplikasi |
| `emas-50…900` | Aksen: indikator aktif, angka kunci | Warna yang hadir di kedua logo |
| `hijau-*` | Kondisi normal | Perisai logo provinsi |
| `bata-*` | Kondisi bermasalah | Bingkai logo provinsi |

---

## Fase Pengembangan

- [x] Fase 1 — Backend API Laravel
- [x] Fase 2 — Android App (Kotlin, offline-first)
- [x] Fase 3 — Integrasi & Sync
- [x] Fase 4 — Web Dashboard (Blade + Tailwind)
- [ ] Fase 5 — Deploy

### Belum dikerjakan

**Deployment.** Sistem masih berjalan di laptop pengembang. Untuk keperluan
sementara, `TEROWONGAN.md` menjelaskan cara menyiarkannya lewat Cloudflare
Tunnel tanpa biaya.

**Data.** Kodenya sudah siap, isinya yang belum: dari 62 DAS, 46 belum
berkoordinat, 61 belum berkabupaten, dan 56 belum berjuru. Poligon batas
wilayah juga belum ada satu pun — halaman pengunggahnya sudah jadi, berkas
resminya yang masih harus diminta ke BWS Kalimantan III atau BPDAS Barito.

Rincian beserta alasannya ada di [TODO.md](TODO.md).

**Export blangko OP resmi sudah ada, dalam dua bentuk.** Halaman detail laporan
menyediakan tombol **Blangko Excel** dan **Blangko PDF**, keduanya mengikuti
blangko OP-01.a s.d. OP-01.n. Template Excel aslinya disimpan di
`resources/blangko/` dan diisi memakai PhpSpreadsheet sehingga tata letaknya
persis blangko dinas; PDF dipakai untuk arsip yang tidak boleh berubah.
Keduanya membaca sumber data yang sama supaya isinya tidak mungkin berbeda.

**Monitoring sinkronisasi bukan sekadar halaman web.** Server tidak mungkin
mengetahui laporan yang gagal terkirim, sebab laporan itu memang tidak pernah
sampai. Aplikasi juru mengabarkan keadaan antreannya sendiri lewat
`POST /api/sinkronisasi/status` pada setiap akhir percobaan sinkronisasi.

**Seluruh peta memakai Leaflet dan OpenStreetMap**, tanpa kunci API maupun
tagihan: peta DAS, peta lokasi bangunan pada detail laporan, dan peta sebaran
seluruh bangunan yang dapat disaring per DAS, jenis bangunan, dan periode.

**Batas wilayah dapat dimasukkan sendiri** lewat Master Data → Batas Wilayah,
menerima `.zip` shapefile apa adanya dari BWS atau berkas `.geojson`. Berkas
berkoordinat UTM ditolak beserta petunjuk perbaikannya dengan QGIS —
menerimanya berarti seluruh batas tergambar di tempat yang salah tanpa ada yang
menyadarinya. Batas tidak pernah digambar sendiri.

**Cadangan berjalan tiap hari pukul 01.00** lewat `riverop:cadangkan`, berisi
dump basis data dan seluruh foto. Ditulis dengan PHP, bukan memanggil
`mysqldump`, supaya tidak bergantung pada berkas yang belum tentu ada di server
dinas. Perlu satu entri cron; tanpa itu tidak ada galat apa pun, cadangannya
sekadar tidak pernah dibuat.

**Seluruh pesan sistem berbahasa Indonesia**, termasuk pesan kesalahan bawaan
Laravel, dan dijaga `BahasaIndonesiaTest`. Terjemahannya di `lang/id/`.

---

## Struktur Folder Penting

```
app/
├── Http/Controllers/
│   ├── AuthController.php          API: login juru
│   ├── MasterController.php        API: master data
│   ├── LaporanController.php       API: sync & upload foto
│   └── Web/                        Dashboard dinas
│       ├── LoginController.php
│       ├── DashboardController.php
│       ├── LaporanWebController.php
│       ├── JuruSungaiWebController.php
│       └── WilayahSungaiWebController.php
├── Models/
└── Support/
    └── Bulan.php                   Nama bulan dalam bahasa Indonesia

resources/
├── css/app.css                     Token warna Tailwind
└── views/
    ├── layouts/app.blade.php       Kerangka dashboard
    ├── components/                 logo, lencana, kosong
    ├── auth/  dashboard/  laporan/  juru/  wilayah/

routes/
├── api.php                         Endpoint aplikasi Android
└── web.php                         Halaman dashboard dinas
```

---

## Catatan Deployment

- Jalankan `npm run build` di server; asset hasil build tidak ikut ke repositori.
- Pastikan `php artisan storage:link` sudah dijalankan agar foto dapat diakses.
- Set `APP_URL` sesuai domain produksi. Alamat server pada aplikasi Android
  **tidak lagi diedit di kode** — ia dibaca dari `local.properties` saat build.
- `APP_ENV=production` dan **`APP_DEBUG=false`**. Dengan debug menyala, setiap
  galat menampilkan jejak kode beserta seluruh isi `.env`, termasuk sandi basis
  data, kepada siapa pun yang membuka halamannya.
- `APP_LOCALE=id` dan `APP_FALLBACK_LOCALE=id`. Bila terlewat, sebagian pesan
  kembali berbahasa Inggris tanpa galat apa pun.
- Pasang entri cron agar cadangan harian berjalan:
  `* * * * * cd /jalur/ke/river-op && php artisan schedule:run >> /dev/null 2>&1`
- Gunakan HTTPS. Aplikasi Android mengirim token pada setiap permintaan, dan
  build rilisnya menolak dibangun dengan alamat non-`https`.
- Di balik proxy atau CDN, `trustProxies` sudah disetel di `bootstrap/app.php`.
  Tanpa itu Laravel menuliskan alamat aset sebagai `http://` pada halaman yang
  dilayani `https://`, dan peramban memblokir seluruhnya — dasbor terbuka tanpa
  gaya dan tanpa tombol yang berfungsi, tanpa pesan galat apa pun.
