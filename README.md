# River OP API — Backend

Backend API untuk sistem pelaporan Operasi & Pemeliharaan (OP) Sungai  
Dinas PUPR Provinsi Kalimantan Selatan

---

## Tech Stack

- **Framework:** Laravel 12 (PHP)
- **Database:** MySQL
- **Auth:** Laravel Sanctum (token-based)

---

## Requirements

- PHP >= 8.2
- Composer
- MySQL

---

## Instalasi

```bash
git clone <repo-url>
cd river-op-api
composer install
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

Jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

Jalankan server:

```bash
php artisan serve
```

---

## API Endpoints

### Auth
| Method | Endpoint | Deskripsi | Auth |
|--------|----------|-----------|------|
| POST | `/api/auth/login` | Login juru sungai | ❌ |
| POST | `/api/auth/logout` | Logout | ✅ |

### Master Data
| Method | Endpoint | Deskripsi | Auth |
|--------|----------|-----------|------|
| GET | `/api/master/sync` | Ambil semua master data | ✅ |
| GET | `/api/master/profil` | Profil juru sungai login | ✅ |

### Laporan
| Method | Endpoint | Deskripsi | Auth |
|--------|----------|-----------|------|
| POST | `/api/laporan/sync` | Sync batch laporan dari HP | ✅ |

---

## Struktur Database

```
kabupaten
    └── wilayah_sungai
            └── juru_sungai
            └── laporan_op
                    └── item_inspeksi
                            └── temuan_kondisi

jenis_bangunan (OP-01.a s/d OP-01.n)
    └── bagian_bangunan
            └── pilihan_kondisi

staf_dinas
```

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

## Fase Pengembangan

- [x] Fase 1 — Backend API Laravel
- [ ] Fase 2 — Android App (Kotlin)
- [ ] Fase 3 — Integrasi & Sync
- [ ] Fase 4 — Web Dashboard (Next.js)
- [ ] Fase 5 — Deploy

---

## Struktur Folder Penting

```
app/
├── Http/
│   └── Controllers/
│       ├── AuthController.php
│       ├── MasterController.php
│       └── LaporanController.php
└── Models/
    ├── Kabupaten.php
    ├── WilayahSungai.php
    ├── JenisBangunan.php
    ├── BagianBangunan.php
    ├── PilihanKondisi.php
    ├── JuruSungai.php
    ├── StafDinas.php
    ├── LaporanOp.php
    ├── ItemInspeksi.php
    └── TemuanKondisi.php

database/
├── migrations/
└── seeders/
    ├── DatabaseSeeder.php
    ├── KabupatenSeeder.php
    ├── JenisBangunanSeeder.php
    ├── WilayahSungaiSeeder.php
    ├── JuruSungaiSeeder.php
    └── StafDinasSeeder.php

routes/
└── api.php
```
