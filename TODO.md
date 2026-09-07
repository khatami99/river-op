# TODO — Sistem Informasi OP Sungai

Daftar pekerjaan yang belum selesai, diurutkan dari yang paling mendesak.
Diperbarui 7 September 2026.

---

## Sudah diputuskan

### Struktur wilayah: tingkat DAS ditambahkan — selesai 28 Agu 2026

Hanya ada satu wilayah sungai yang menjadi wewenang provinsi, yaitu Wilayah Sungai
Cengal, dan pembagian tugas juru dilakukan per DAS. Tabel `das` kini berada di antara
`wilayah_sungai` dan `juru_sungai`; juru serta laporan menunjuk ke DAS.

Tiga baris lama pada `wilayah_sungai` — Sungai Tabanio, Batulicin, Cantung — sudah
dipindahkan menjadi DAS dengan kode `DAS-...`, dan seluruh 16 laporan yang ada tetap
tersambung. Sisi Android ikut diselaraskan: `das_id` pada Room (migrasi versi 2 ke 3),
payload sync, master data, dan label di layar juru.

---

## Selesai 28 Agu 2026

- [x] **Pembatasan role ditegakkan.** Middleware `admin` menjaga seluruh rute
      perubahan data. Viewer tetap dapat melihat laporan tetapi menerima 403 saat
      mencoba menulis, termasuk bila permintaan ditembak langsung tanpa lewat tombol.
- [x] **CRUD Juru Sungai**, lengkap dengan pemilihan DAS wilayah kerja.
- [x] **CRUD DAS** dan **CRUD Wilayah Sungai**.
- [x] **Manajemen pengguna staf dinas**, dengan pengaman: admin tidak dapat
      menghapus, menurunkan peran, atau menonaktifkan akunnya sendiri, dan
      administrator aktif terakhir tidak dapat dihapus.

Penghapusan ditolak bila data masih dipakai — juru yang sudah punya laporan, DAS
yang masih dipegang juru, dan wilayah sungai yang masih menaungi DAS. Dalam kasus
itu admin diarahkan untuk menonaktifkan saja agar riwayat tetap utuh.

- [x] **Rekapitulasi per DAS** beserta baris total, daftar juru belum melapor,
      dan export CSV-nya. Seluruh angka diagregasi lewat SQL.
- [x] **Ringkasan laporan bermasalah di dashboard**, sehingga pertanyaan
      "ada laporan bermasalah tidak?" akhirnya terjawab dalam hitungan detik.
- [x] **Kolom `ada_masalah` pada `temuan_kondisi`.** Sebelumnya penilaian ini
      harus membaca kolom JSON baris per baris di PHP sehingga rekap tidak bisa
      diagregasi SQL. Penandanya kini disimpan saat sinkronisasi.
- [x] **Peta DAS** memakai Leaflet dan OpenStreetMap. Titik tiap DAS ditentukan
      admin dengan mengklik peta pada formulir DAS. Klik penanda memunculkan juru
      yang bertugas, ringkasan laporan, dan tombol tambah juru. Warna penanda
      menandakan ada tidaknya temuan bermasalah.
- [x] **Tambah juru langsung dari halaman DAS**, dengan DAS terpilih otomatis.

---

## Selesai 6 Sep 2026

- [x] **Penangkapan GPS di aplikasi Android.** Sebelumnya koordinat di-hardcode
      `null`, tanpa izin lokasi maupun pustaka lokasi. Kini titik diambil saat
      juru mencatat sebuah bangunan, ditampilkan beserta akurasinya, dan dapat
      diulang bila gagal. Pengambilan dibatasi 20 detik dan selalu mengembalikan
      hasil, sehingga layar juru tidak menggantung saat sinyal buruk di lapangan.
      Titik disimpan per bangunan, bukan per temuan, karena temuan berada di
      bangunan yang sama. Jalur datanya sudah diuji sampai muncul di dashboard
      sebagai tautan ke Google Maps.

---

## Selesai 7 Sep 2026

- [x] **Nama sungai dan patok kilometer pada bangunan.** Blangko menulis lokasi
      sebagai "Sungai <nama> pada ruas antara patok Km A s/d Km B", dan menurut
      dinas bagian ini normalnya memang diisi. Nama sungainya sebelumnya tidak
      ada di mana pun, sedangkan `lokasi_km_dari` dan `lokasi_km_sampai` sudah
      punya kolom sampai ke database tetapi **di-hardcode `null`** oleh aplikasi
      Android — persis pola yang dulu terjadi pada koordinat GPS.

      Kolom `nama_sungai` ditambahkan pada `item_inspeksi`, dan aplikasi juru
      kini menyediakan isian nama sungai serta ruas kilometer pada layar
      pencatatan bangunan (Room versi 3 ke 4). Koma maupun titik diterima
      sebagai pemisah desimal, mengikuti papan ketik yang dipakai juru.
      Baris Lokasi bangunan pada blangko hasil export kini terisi penuh.

- [x] **Export blangko OP resmi (.xlsx).** Sebelumnya buntu karena belum ada
      contoh blangko aslinya. Dua berkas laporan sungguhan dari dinas kini
      dipakai sebagai template, sehingga tata letak, garis, dan lebar kolomnya
      persis sama — isian ditaruh ke sel yang sudah ada, bukan digambar ulang.

      Tombol "Unduh Blangko OP" ada di halaman detail laporan. Hanya jenis
      bangunan yang benar-benar diperiksa yang ikut tercetak, dan bila satu
      laporan memuat beberapa bangunan berjenis sama, lembarnya digandakan
      (`OP - 01.e`, `OP - 01.e (2)`) karena blangko hanya menyediakan satu
      lembar per jenis.

      Baris temuan dicocokkan lewat **urutan** bagian bangunan, bukan namanya:
      13 dari 14 lembar sudah cocok jumlah barisnya dengan master data kita,
      dan sisa perbedaan hanya pada penulisan nama (singkatan, serta satu salah
      ketik pada blangko asli yang sengaja tidak diubah). Koordinat ditulis
      dalam derajat-menit-detik mengikuti contoh isian dinas.

- [x] **62 DAS resmi diseed** sesuai daftar "Nama DAS" pada Peta Wilayah Sungai
      Cengal-Batulicin (Lampiran V.87, Kode WS 04.10.B), bernomor 001-062.
      Nama wilayah sungainya ikut dibetulkan menjadi **WS Cengal-Batulicin**
      dengan kode **04.10.B**; sebelumnya tersimpan "WS Cengal" karangan seeder
      lama, lengkap dengan satu DAS yang bahkan tidak berada di wilayah sungai
      ini.

- [x] **Kabupaten dipindah dari wilayah sungai ke DAS, banyak-ke-banyak.**
      Sebelumnya kabupaten menempel di wilayah sungai, padahal WS
      Cengal-Batulicin membentang di Kotabaru, Tanah Bumbu, dan Tanah Laut —
      jadi nilainya pasti salah untuk sebagian data. Satu DAS pun bisa
      melintasi dua kabupaten, karena batas DAS mengikuti punggung bukit
      sedangkan batas kabupaten digambar manusia. Admin dapat mengoreksinya
      lewat formulir DAS.

      **Jangan dipakai menghitung.** DAS yang melintasi dua kabupaten akan
      terhitung dua kali. Angka per kabupaten baru sahih bila sudah ada poligon
      batas wilayah dan dinilai dari titik GPS tiap bangunan.

- [x] **Koordinat DAS diisi untuk 16 DAS**, sisanya sengaja dibiarkan kosong.
      Sifatnya perkiraan dengan ketelitian sekitar sepuluh kilometer, dan di
      formulir kini dilabeli "Titik Perkiraan" agar tidak dibaca sebagai data
      resmi. DAS adalah wilayah, bukan titik.

- [x] **Data contoh** lewat `LaporanContohSeeder`: 6 juru, 23 laporan, 64
      bangunan bertitik, tersebar Juni-September 2026 di sekitar DAS
      masing-masing. Menghapus seluruh laporan lebih dulu dan tidak berjalan di
      produksi. Jangan dijalankan bila sudah ada laporan sungguhan.

- [x] **Dua bug peta diperbaiki.** Pandangan peta dihitung sebelum kontainernya
      selesai diukur sehingga penanda menumpuk di pojok; kini dipasang ulang
      setiap kontainer berubah ukuran, dan berhenti memaksa begitu pengguna
      menggeser sendiri. Roda mouse juga tidak lagi membajak gulir halaman —
      zoom roda baru aktif setelah petanya diklik.

---

## Prioritas sedang

- [ ] **Peta sebaran laporan.** Prasyaratnya sudah lengkap: koordinat
      tertangkap aplikasi Android, dan data contoh sudah menyebar di 6 DAS
      sehingga hasilnya langsung bisa dinilai. Tinggal menampilkan seluruh
      titik bangunan dalam satu peta yang dapat disaring per periode, per DAS,
      per jenis bangunan, dan hanya yang bermasalah. Satu titik mewakili satu
      bangunan, bukan satu laporan, karena GPS-nya memang direkam per bangunan.

- [ ] **Batas wilayah DAS dan WS pada peta.** Keduanya wilayah, bukan titik.
      Perlu shapefile atau GeoJSON resmi — dapat diminta ke BWS Kalimantan III
      selaku pengelola WS Cengal-Batulicin, BPDAS wilayah Kalsel, atau diunduh
      dari Ina-Geoportal (BIG). Peta lampiran yang sudah ada membuktikan
      berkasnya memang ada, sebab garis batasnya dicetak dari data geospasial.

      Setelah dapat: titik DAS turun menjadi cadangan, titik GPS bangunan bisa
      dicocokkan otomatis ke DAS-nya sehingga juru yang salah pilih DAS
      ketahuan sendiri, dan urusan kabupaten selesai karena bisa dinilai per
      titik. Poligon mentah perlu disederhanakan dulu agar peta tidak berat.

      Batas sengaja tidak digambar sendiri karena batas yang keliru lebih
      berbahaya bagi sistem instansi daripada tidak ada peta sama sekali.

- [ ] **Export daftar laporan dan rekap sebagai `.xlsx`.** Blangko OP sudah
      berupa Excel asli, tetapi export daftar laporan dan rekapitulasi masih
      CSV. PhpSpreadsheet sudah terpasang, jadi tinggal dipakai.

- [ ] **Export PDF** sesuai format blangko OP resmi. Export Excel sudah ada dan
      masih bisa diedit dinas sebelum dicetak; PDF berguna untuk arsip final
      yang tidak boleh berubah.

- [ ] **Perbarui dependensi yang rentan.** `composer audit` melaporkan 27
      advisory pada 7 paket — guzzle, commonmark, symfony, dan laravel/framework.
      Semuanya bawaan lama, bukan dari PhpSpreadsheet yang baru dipasang.

---

## Prioritas rendah

- [ ] **Monitoring sinkronisasi.** Perlu diketahui: server hanya mengenal satu nilai
      `sync_status`, yaitu `synced`. Laporan yang gagal terkirim tidak pernah sampai ke
      server, sehingga server tidak punya cara mengetahuinya. Status GAGAL dan PENDING
      hanya hidup di HP juru. Agar dinas bisa memantau, aplikasi Android harus
      mengirim laporan status secara terpisah — jadi ini bukan sekadar pekerjaan di sisi web.

- [ ] **Audit log.** Jejak perubahan data untuk kebutuhan pertanggungjawaban.

- [ ] **Notifikasi** ke juru saat sinkronisasi berhasil atau gagal.

---

## Catatan

Poin *"hapus data lokal setelah konfirmasi server"* pada rencana awal Android
**sengaja tidak dijalankan**. Laporan berstatus SYNCED tetap disimpan di HP sebagai
riwayat; hanya berkas fotonya yang dihapus untuk menghemat penyimpanan. Juru sering
perlu membuka kembali laporan lamanya di lapangan.
