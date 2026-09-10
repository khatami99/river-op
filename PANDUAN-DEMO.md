# Panduan Demo — Sistem Informasi OP Sungai

Untuk yang memperagakan sistem ini di hadapan pimpinan. Ditulis supaya bisa
dibuka di HP saat itu juga.

---

## Sebelum berangkat

| | |
|---|---|
| Alamat sistem sudah diterima | `https://…` dari yang menyiapkan server |
| Alamat itu sudah dibuka sekali di peramban | halaman masuk tampil rapi berwarna biru tua |
| Aplikasi juru di HP sudah bisa masuk | jangan menunggu di lokasi |

**Uji semuanya sebelum berangkat, bukan di tempat.**

Akun untuk memperagakan:

| | Nama pengguna | Kata sandi |
|---|---|---|
| Dasbor dinas | `admin` | `admin123` |
| Aplikasi juru | `ahmad_juru` | `password` |

---

## Kalimat pembuka

Sebut dua hal ini di awal, sekali saja. Keduanya mencegah pertanyaan yang
mengganggu di tengah jalan:

> "Ini sistem untuk pelaporan OP sungai. Ada dua sisi — aplikasi di HP untuk
> juru di lapangan, dan dasbor di komputer untuk dinas.
>
> Laporan yang tampil ini masih **data contoh** untuk memperagakan alurnya.
> Nama-nama jurunya belum nama sungguhan."

Mengatakannya lebih dahulu jauh lebih baik daripada ditanya di tengah.

---

## Urutan yang disarankan

Urutannya sengaja menahan bagian terkuat sampai pimpinan sudah percaya
sistemnya benar-benar berjalan.

### 1 — Daftar DAS

**Master Data → DAS.** Enam puluh dua DAS, seluruhnya sudah masuk.

> "Ke-62 DAS di WS Cengal-Batulicin sudah ada semua di sistem, sesuai daftar
> resminya."

### 2 — Laporan yang masuk

**Laporan OP.** Tunjukkan penyaring bulan dan DAS.

### 3 — Detail satu laporan

Buka satu laporan. Tunjukkan foto, kondisi tiap bagian bangunan, dan titik
lokasinya.

### 4 — Blangko OP  ← bagian terkuat

Pada halaman detail, tekan **Blangko PDF**.

> "Yang keluar ini blangko OP yang biasa dipakai, tata letaknya sama. Jadi
> laporan dari lapangan bisa langsung dicetak dan diarsipkan tanpa diketik
> ulang."

**Berhenti sebentar di sini.** Ini yang membedakan sistem ini dari sekadar
aplikasi pencatatan. Beri waktu untuk dilihat.

### 5 — Aplikasi juru di HP

Masuk, buat satu laporan singkat, kirim. Lalu muat ulang dasbor — laporannya
muncul.

> "Kalau juru tidak ada sinyal, laporannya tersimpan di HP dan terkirim sendiri
> begitu dapat jaringan. Tidak ada yang hilang, dan juru tidak perlu mengingat
> untuk mengirim ulang."

### 6 — Rekapitulasi

**Rekapitulasi.** Rekap per DAS beserta baris TOTAL, dan daftar juru yang belum
melapor bulan itu.

> "Ini yang bisa dipakai menagih. Siapa yang belum melapor bulan ini kelihatan
> tanpa harus dicek satu-satu."

### 7 — Peta, kalau sempat

Taruh di akhir. Kalau waktunya habis, tidak apa-apa dilewati.

---

## Pertanyaan yang pasti ditanyakan

### "Kenapa petanya kosong?"

> "Titik koordinatnya memang belum diisi — baru 16 dari 62. Itu data yang hanya
> dinas yang punya. Begitu diisi, DAS-nya langsung muncul di peta."

Jangan meminta maaf. Sistemnya menunggu data, bukan rusak.

### "Ini datanya siapa? Saya tidak kenal nama-nama ini."

> "Data contoh, untuk memperagakan alurnya. Nanti diganti nama juru yang
> sebenarnya."

### "Kalau juru tidak punya sinyal bagaimana?"

> "Aplikasinya dirancang untuk itu. Laporan ditulis di HP dulu, dikirim
> belakangan begitu ada jaringan. Juru tidak perlu melakukan apa-apa."

### "Kalau HP juru hilang atau rusak?"

> "Laporan yang sudah terkirim aman di server. Yang belum terkirim memang ada
> di HP — sama seperti blangko kertas yang belum sampai kantor."

### "Bagaimana kami tahu ada juru yang tidak melapor?"

> "Ada di Rekapitulasi, dan di halaman Sinkronisasi kelihatan keadaan HP tiap
> juru — berapa laporan masih tertahan, dan kapan terakhir terhubung."

### "Datanya aman? Bisa hilang?"

> "Ada cadangan otomatis setiap hari, berisi seluruh data dan foto."

### "Berapa biayanya?"

> "Sistemnya sendiri tidak ada biaya lisensi. Yang perlu hanya server, sekitar
> tiga ratus ribu setahun untuk hosting biasa."

Angka sekecil itu biasanya menutup pembicaraan dengan baik.

### "Kapan bisa dipakai?"

> "Sistemnya sudah jalan. Yang perlu dari dinas tinggal data — koordinat DAS,
> pembagian juru, dan akun untuk juru yang sebenarnya."

Ini mengubah pimpinan dari penonton menjadi penentu. Pertanyaan bagus, jangan
dijawab dengan "belum tahu".

---

## Kalau ada yang macet

### Dasbor tidak mau terbuka di peramban

Alamatnya kemungkinan sudah berubah. Hubungi yang menyiapkan server dan minta
alamat terbarunya.

### Aplikasi di HP tidak bisa masuk

Layar masuk → **Uji koneksi server**.

| Yang muncul | Artinya |
|---|---|
| Kotak **hijau** | Jaringan tidak bermasalah. Berarti nama pengguna atau kata sandinya yang keliru. |
| Kotak **merah** | Server tidak terjangkau. Minta alamat terbaru, tempelkan di kolom Alamat server, tekan Uji Sekarang. |
| Kotak **kuning** | Ada yang menjawab tetapi bukan sistem ini — biasanya wifi yang memakai halaman persetujuan. Coba pakai kuota. |

### Semuanya macet

Lanjutkan dari dasbor di peramban saja. Bagian terkuat — blangko OP — ada di
sisi web, dan tidak membutuhkan HP sama sekali.

---

## Yang sebaiknya tidak dijanjikan

Sebut "**belum**", jangan "tidak bisa". Semuanya memang mungkin, hanya belum
dikerjakan:

- Batas wilayah DAS di peta — menunggu berkas resmi dari BWS
- Aplikasi di Play Store — sekarang dipasang langsung
- Laporan per kabupaten — perlu batas wilayah lebih dahulu supaya angkanya sahih

Bila ditanya sesuatu yang tidak diketahui jawabannya, **katakan akan dicek
dahulu.** Menebak di depan pimpinan jauh lebih mahal daripada mengaku belum
tahu.
