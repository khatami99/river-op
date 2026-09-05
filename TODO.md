# TODO — Sistem Informasi OP Sungai

Daftar pekerjaan yang belum selesai, diurutkan dari yang paling mendesak.
Diperbarui 28 Agustus 2026.

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

## Prioritas tinggi

- [ ] **Tegakkan pembatasan role.** Kolom `role` (`admin` / `viewer`) sudah ada dan
      ditampilkan di sidebar, tetapi tidak pernah diperiksa di controller mana pun.
      Akibatnya akun `viewer` memiliki akses yang sama persis dengan `admin`.
      Berisiko karena tampak aman padahal tidak.

- [ ] **CRUD Juru Sungai.** Admin dinas harus dapat menambah, mengubah, menonaktifkan
      juru, dan menentukan wilayah kerjanya. Saat ini juru hanya bisa dibuat lewat seeder.

- [ ] **CRUD Wilayah Sungai.** Tetap dibuat meski saat ini hanya ada satu wilayah sungai
      wewenang provinsi, karena bisa bertambah di kemudian hari.

- [ ] **Manajemen pengguna staf dinas.** Belum ada cara menambah akun staf selain seeder.

---

## Prioritas sedang

- [ ] **Tampilkan koordinat GPS di halaman detail.** Kolom `koordinat_lat` dan
      `koordinat_lng` sudah terisi di `item_inspeksi` dan `temuan_kondisi`, tetapi
      tidak pernah ditampilkan. Paling murah dikerjakan.

- [ ] **Ringkasan temuan bermasalah di dashboard.** Dashboard baru menjawab dua
      pertanyaan: berapa yang sudah lapor, dan siapa yang belum. Pertanyaan ketiga —
      "ada laporan bermasalah tidak?" — belum terjawab; angkanya baru muncul di
      halaman detail masing-masing laporan.

- [ ] **Rekapitulasi per wilayah.** Tabel sudah/belum melapor per sungai beserta totalnya.

- [ ] **Export Excel sungguhan (`.xlsx`).** Saat ini export berupa CSV. Terbuka dengan
      baik di Excel, tetapi bukan berkas Excel asli.

- [ ] **Export PDF** sesuai format blangko OP resmi, untuk kebutuhan cetak dan arsip.

- [ ] **Export daftar juru yang belum melapor.**

---

## Prioritas rendah

- [ ] **Monitoring sinkronisasi.** Perlu diketahui: server hanya mengenal satu nilai
      `sync_status`, yaitu `synced`. Laporan yang gagal terkirim tidak pernah sampai ke
      server, sehingga server tidak punya cara mengetahuinya. Status GAGAL dan PENDING
      hanya hidup di HP juru. Agar dinas bisa memantau, aplikasi Android harus
      mengirim laporan status secara terpisah — jadi ini bukan sekadar pekerjaan di sisi web.

- [ ] **Audit log.** Jejak perubahan data untuk kebutuhan pertanggungjawaban.

- [ ] **Peta sebaran laporan.** Koordinat sudah tersimpan; tinggal penyajiannya.
      Sengaja ditunda agar kompleksitas tidak melonjak.

- [ ] **Notifikasi** ke juru saat sinkronisasi berhasil atau gagal.

---

## Catatan

Poin *"hapus data lokal setelah konfirmasi server"* pada rencana awal Android
**sengaja tidak dijalankan**. Laporan berstatus SYNCED tetap disimpan di HP sebagai
riwayat; hanya berkas fotonya yang dihapus untuk menghemat penyimpanan. Juru sering
perlu membuka kembali laporan lamanya di lapangan.
