# Menyiarkan Sistem dari Laptop Sendiri (Cloudflare Tunnel)

Cara memberi alamat `https://` publik kepada server yang berjalan di laptop,
tanpa hosting dan tanpa biaya. Laptop cukup ditinggal menyala; yang mendemokan
sistem hanya perlu membawa HP.

Seluruh langkah di bawah sudah dijalankan dan diuji pada mesin ini pada
10 September 2026, termasuk dari HP lewat jaringan seluler.

---

## Apa yang sebenarnya terjadi

Cloudflare menjalankan program kecil di laptop yang **menelepon keluar** ke
jaringan mereka. Semua permintaan yang datang ke alamat publikmu diteruskan
lewat sambungan itu ke `localhost:8000`.

Karena laptop yang menelepon keluar, tidak ada yang perlu diatur di router,
tidak perlu IP publik, dan tidak perlu membuka port apa pun.

**Konsekuensinya:** kalau laptop mati, tertidur, atau internetnya putus,
alamat itu ikut mati. Ini pengganti sementara, bukan pengganti hosting.

---

## Sekali saja: pasang cloudflared

```
winget install --id Cloudflare.cloudflared
```

Terpasang di `C:\Program Files (x86)\cloudflared\cloudflared.exe`.
Tidak perlu akun Cloudflare, tidak perlu mendaftar.

---

## Tiap kali mau menyiarkan

### 1. Nyalakan servernya

```
cd C:\laragon\www\river-op
php artisan serve
```

Biarkan jendela ini terbuka.

### 2. Nyalakan terowongannya, di jendela terminal LAIN

```
& "C:\Program Files (x86)\cloudflared\cloudflared.exe" tunnel --url http://localhost:8000
```

Sesudah beberapa detik akan muncul kotak berisi alamatmu:

```
https://sesuatu-yang-acak.trycloudflare.com
```

**Salin alamat itu.** Biarkan jendela ini terbuka juga — menutupnya berarti
mematikan terowongan.

### 3. Masukkan alamatnya ke aplikasi juru

Di HP: **layar masuk → Uji koneksi server**, tempelkan alamatnya pada kolom
Alamat server, lalu tekan **Uji Sekarang**. Tersimpan sekaligus langsung diuji.

Kalau muncul kotak hijau, selesai. Aplikasi akan mengingatnya, bahkan sesudah
ditutup paksa maupun sesudah juru keluar dari akunnya.

---

## Yang HARUS diperiksa lebih dahulu

### `APP_DEBUG` wajib mati

```
APP_DEBUG=false
```

Alamat `trycloudflare.com` dapat dibuka siapa pun yang mengetahuinya. Dengan
`APP_DEBUG=true`, setiap kesalahan menampilkan jejak kode **beserta seluruh isi
`.env`** — termasuk sandi basis data. Jalankan `php artisan config:clear`
sesudah mengubahnya.

### Aset harus ikut `https`

Sudah dibereskan di `bootstrap/app.php` lewat `trustProxies(at: '*')`.

Tanpa itu, halaman dilayani `https://` tetapi Laravel menuliskan alamat CSS dan
JavaScript sebagai `http://`, lalu peramban memblokir seluruhnya — dasbor
terbuka **tanpa gaya sama sekali dan tanpa satu pun tombol yang berfungsi.**
Kesalahan ini tidak menampilkan pesan apa pun; halamannya sekadar tampak rusak.

Kalau kelak sistem ini dipasang di hosting yang tidak berada di balik proxy,
baris itu boleh dilonggarkan.

---

## Yang paling sering menggagalkan

**Alamatnya berubah tiap kali terowongan dinyalakan ulang.** Terowongan cepat
memberi nama acak. Laptop restart, listrik padam, jendela tak sengaja tertutup —
alamatnya berbeda, dan aplikasi di HP tidak akan tahu.

Karena itu alamat server dibuat bisa diketik langsung dari dalam aplikasi.
Kirimkan alamat barunya lewat pesan, dan pemegang HP cukup menempelkannya.

**Laptop tertidur.** Setel Windows agar tidak pernah *sleep* selama disiarkan.
Sambungkan ke listrik. Laptop tidur = alamat mati.

**Butuh nama tetap?** Perlu domain sendiri yang didaftarkan ke Cloudflare, lalu
*named tunnel*. Perlu biaya, dan tidak setimpal untuk pemakaian sementara.

---

## Menghentikan

Tutup jendela terowongannya, atau tekan Ctrl+C di dalamnya. Alamatnya langsung
mati dan tidak akan pernah hidup lagi — alamat berikutnya selalu berbeda.

---

## Bukan pengganti hosting

Ini menyelamatkan demo, bukan menjalankan sistem. Untuk pemakaian sungguhan
oleh dinas, sistemnya harus berada di server yang menyala terus, dengan alamat
tetap dan cadangan harian yang berjalan sendiri. Lihat `PANDUAN-ADMIN.md`.
