# River OP — Backend API & Dashboard Dinas

Sistem digitalisasi pelaporan Operasi & Pemeliharaan (OP) Sungai
Dinas PUPR Provinsi Kalimantan Selatan

Satu aplikasi Laravel melayani dua konsumen:

- **API** untuk aplikasi Android yang dipakai juru sungai di lapangan
- **Dashboard web** untuk staf dinas di kantor

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
| Export | `/laporan/export` | Unduh CSV mengikuti filter yang sedang aktif |
| Juru sungai | `/juru` | Daftar juru dan kalender kepatuhan 12 bulan |
| DAS | `/das` | Daftar daerah aliran sungai beserta juru yang bertugas |
| Wilayah sungai | `/wilayah` | Wilayah sungai beserta DAS di bawahnya |
| Rekapitulasi | `/rekap` | Rekap per DAS, daftar juru belum melapor, export |
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
kabupaten
    └── wilayah_sungai
            └── das
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
Provinsi Kalimantan Selatan, yaitu Wilayah Sungai Cengal. Pembagian tugas juru
sungai dilakukan per DAS (Daerah Aliran Sungai) di bawahnya, sehingga juru dan
laporan menunjuk ke `das`, bukan langsung ke `wilayah_sungai`. Tabel wilayah
sungai tetap dipertahankan karena wilayah sungai baru bisa muncul di kemudian hari.

Satu laporan mewakili satu kunjungan dan dapat memuat banyak bangunan, termasuk
beberapa bangunan berjenis sama yang dibedakan lewat kolom `nama_bangunan`.
Setiap temuan menyimpan sampai tiga foto: `foto`, `foto_2`, dan `foto_3`.

---

## Akun Default (Development)

**Juru Sungai:**
| Username | Password | Wilayah |
|----------|----------|---------|
| ahmad_juru | password | Sungai Tabanio (Tanah Laut) |
| bambang_juru | password | Sungai Batulicin (Tanah Bumbu) |
| candra_juru | password | Sungai Cantung (Kotabaru) |

**Staf Dinas:**
| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | admin |

---

## Sistem Warna Dashboard

Palet diturunkan dari logo aplikasi dan logo Provinsi Kalimantan Selatan.
Token didefinisikan di `resources/css/app.css`.

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

Export PDF sesuai blangko resmi, rekapitulasi per wilayah, audit log,
monitoring sinkronisasi, manajemen pengguna, dan peta sebaran laporan.

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
- Set `APP_URL` sesuai domain produksi, lalu sesuaikan `baseUrl` pada
  `RetrofitClient` di aplikasi Android.
- Gunakan HTTPS di produksi karena aplikasi Android mengirim token pada
  setiap permintaan.
