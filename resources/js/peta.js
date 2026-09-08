import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import '../css/peta.css';

// Ikon bawaan Leaflet memuat berkas gambar lewat jalur relatif yang rusak
// setelah di-bundle, jadi penanda digambar sendiri memakai palet aplikasi.
function penanda(warna, isi) {
    return L.divIcon({
        className: '',
        html: `<div style="
                    width:30px;height:30px;border-radius:999px;
                    background:${warna};color:#fff;
                    display:flex;align-items:center;justify-content:center;
                    font:700 12px/1 'Plus Jakarta Sans',system-ui,sans-serif;
                    box-shadow:0 2px 8px rgba(10,44,72,.35);
                    border:2.5px solid #fff;">${isi}</div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 15],
    });
}

// Penanda polos, tanpa angka di dalamnya. Dipakai pada peta yang penandanya
// sedikit sehingga identitasnya bisa dibawa label di sebelah, bukan dijejalkan
// ke dalam lingkaran 30px yang cuma muat dua tiga karakter.
function penandaPolos(warna) {
    return L.divIcon({
        className: '',
        html: `<div style="
                    width:16px;height:16px;border-radius:999px;
                    background:${warna};
                    box-shadow:0 2px 8px rgba(10,44,72,.35);
                    border:3px solid #fff;"></div>`,
        iconSize: [16, 16],
        iconAnchor: [8, 8],
    });
}

// Isi popup berasal dari isian juru di lapangan, jadi diperlakukan sebagai
// teks biasa — bukan HTML — supaya tanda kurung sudut tidak merusak tampilan
// atau menyisipkan markup.
function lolos(teks) {
    const kotak = document.createElement('span');
    kotak.textContent = teks ?? '';
    return kotak.innerHTML;
}

function petaDasar(elemen, pusat, zoom) {
    const peta = L.map(elemen, { scrollWheelZoom: false }).setView(pusat, zoom);

    // Peta ini duduk di tengah halaman yang panjang. Bila roda mouse langsung
    // memperbesar peta, pengguna yang cuma ingin menggulir halaman malah
    // mengubah pandangan peta tanpa sengaja. Roda baru aktif setelah petanya
    // diklik, dan mati lagi begitu penunjuk keluar.
    peta.on('click', () => peta.scrollWheelZoom.enable());
    elemen.addEventListener('mouseleave', () => peta.scrollWheelZoom.disable());
    // Ubinnya sama persis di mode terang maupun gelap.
    //
    // Sempat dicoba dua cara menggelapkannya: basemap gelap CARTO, yang kini
    // menuntut kunci API sehingga ubinnya datang bertuliskan "API KEY
    // REQUIRED" melintang; lalu meredupkan ubin OpenStreetMap lewat CSS.
    // Yang kedua berhasil secara teknis tetapi terlihat salah — peta jadi
    // seperti rusak, bukan seperti menyesuaikan diri.
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; Kontributor OpenStreetMap',
    }).addTo(peta);

    // Modul ini berjalan sebelum tata letak selesai, sehingga Leaflet sempat
    // mengira kontainernya lebih kecil. Akibatnya bukan cuma petak abu-abu:
    // pandangan ikut dihitung terhadap ukuran yang salah, dan penanda
    // menumpuk di pojok. Menghitung ulang ukuran saja tidak cukup, karena
    // Leaflet mempertahankan titik tengah lama — pandangannya harus dipasang
    // ulang setelah ukurannya benar.
    peta.aturPandangan = () => peta.setView(pusat, zoom);

    // Begitu pengguna menggeser atau memperbesar peta, pandangan tidak boleh
    // dipaksa kembali. Sejak saat itu peta menjadi miliknya.
    // Ditangkap dari DOM, bukan dari kejadian Leaflet: fitBounds dan setView
    // milik program sendiri juga memicu zoomstart, sehingga peta akan langsung
    // dikira sudah disentuh dan tidak pernah dipasang ulang.
    let disentuh = false;
    const tandaiDisentuh = () => { disentuh = true; };
    elemen.addEventListener('pointerdown', tandaiDisentuh);
    elemen.addEventListener('wheel', tandaiDisentuh);

    const segarkan = () => {
        peta.invalidateSize();
        if (! disentuh) peta.aturPandangan();
    };

    if (typeof ResizeObserver !== 'undefined') {
        new ResizeObserver(segarkan).observe(elemen);
    }
    requestAnimationFrame(segarkan);

    // Pemanggil mengganti aturPandangan setelah penanda selesai dipasang,
    // sehingga ia perlu cara memicu penyegaran sekali lagi. Tanpa ini,
    // penyegaran pertama sempat memakai pandangan bawaan dan pandangan hasil
    // hitungan penanda tidak pernah terpakai.
    peta.segarkan = segarkan;

    return peta;
}

// Titik tengah Kalimantan Selatan, dipakai bila belum ada DAS berkoordinat.
const PUSAT_KALSEL = [-3.0926, 115.2838];

/* ---------- Halaman peta sebaran DAS ---------- */
function siapkanPetaDas() {
    const wadah = document.getElementById('peta-das');
    if (!wadah || wadah._leaflet_id) return;

    const daftar = JSON.parse(wadah.dataset.das || '[]');
    const berkoordinat = daftar.filter((d) => d.lat !== null && d.lng !== null);

    const peta = petaDasar(wadah, PUSAT_KALSEL, 8);

    // Diserahkan ke petaDasar supaya batas pandang selalu dihitung ulang
    // setiap kontainernya berubah ukuran, bukan sekali saja di awal.
    if (berkoordinat.length) {
        peta.aturPandangan = () => peta.fitBounds(
            berkoordinat.map((d) => [d.lat, d.lng]),
            { padding: [60, 60], maxZoom: 11 }
        );
        requestAnimationFrame(peta.segarkan);
    }

    const panel = document.getElementById('panel-das');
    const kosong = document.getElementById('panel-kosong');

    berkoordinat.forEach((d) => {
        const warna = d.bermasalah > 0 ? '#a50e26' : (d.juru > 0 ? '#12639e' : '#475569');

        L.marker([d.lat, d.lng], { icon: penanda(warna, d.juru) })
            .addTo(peta)
            .on('click', () => tampilkanPanel(d, panel, kosong));
    });
}

function tampilkanPanel(d, panel, kosong) {
    if (!panel) return;

    kosong?.classList.add('hidden');
    panel.classList.remove('hidden');

    const daftarJuru = d.daftar_juru.length
        ? d.daftar_juru.map((j) => `
            <a href="${j.url}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 transition hover:bg-permukaan-lembut">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-info-latar text-[12px] font-extrabold text-info-teks">${j.inisial}</span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-[13.5px] font-semibold text-teks">${j.nama}</span>
                    <span class="block truncate text-[12px] text-teks-samar">${j.telepon}</span>
                </span>
            </a>`).join('')
        : '<p class="px-3 py-6 text-center text-[13px] text-teks-samar">Belum ada juru bertugas di DAS ini.</p>';

    panel.innerHTML = `
        <div class="border-b border-garis-halus px-5 py-4">
            <p class="text-[12px] font-bold uppercase tracking-wider text-peringatan-teks">${d.kode}</p>
            <h2 class="mt-0.5 text-base font-extrabold text-judul">${d.nama}</h2>
            <p class="mt-0.5 text-[12px] text-teks-samar">${d.wilayah}</p>
        </div>

        <div class="grid grid-cols-3 divide-x divide-garis-halus border-b border-garis-halus">
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums text-judul">${d.juru}</p>
                <p class="text-[11px] font-medium text-teks-samar">Juru</p>
            </div>
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums text-judul">${d.laporan}</p>
                <p class="text-[11px] font-medium text-teks-samar">Laporan</p>
            </div>
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums ${d.bermasalah > 0 ? 'text-bahaya-teks' : 'text-aman-teks'}">${d.bermasalah}</p>
                <p class="text-[11px] font-medium text-teks-samar">Bermasalah</p>
            </div>
        </div>

        <div class="px-2 py-2">${daftarJuru}</div>

        <div class="flex flex-wrap gap-2 border-t border-garis-halus px-5 py-4">
            <a href="${d.url}" class="rounded-xl bg-permukaan-sedang px-3.5 py-2 text-[13px] font-bold text-teks-sedang transition hover:bg-permukaan-kuat">
                Detail DAS
            </a>
            ${d.url_tambah_juru ? `
                <a href="${d.url_tambah_juru}" class="rounded-xl bg-aksi px-3.5 py-2 text-[13px] font-bold text-aksi-teks transition hover:bg-aksi-sorot">
                    Tambah Juru
                </a>` : ''}
        </div>`;
}

/* ---------- Peta wilayah sungai ---------- */
function siapkanPetaWs() {
    const wadah = document.getElementById('peta-ws');
    if (!wadah || wadah._leaflet_id) return;

    const daftar = JSON.parse(wadah.dataset.ws || '[]');
    if (!daftar.length) return;

    const peta = petaDasar(wadah, [daftar[0].lat, daftar[0].lng], 8);
    const panel = document.getElementById('panel-ws');
    const kosong = document.getElementById('panel-ws-kosong');

    daftar.forEach((w) => {
        // Identitasnya dibawa label di sebelah penanda, bukan angka di dalam
        // lingkaran. Angka telanjang di atas peta adalah teka-teki: "62" bisa
        // berarti kode, jumlah DAS, atau nomor urut, dan pembaca baru tahu
        // setelah mengklik. Di peta DAS lingkaran yang bentuknya sama malah
        // berisi jumlah juru — bentuk yang sama, dua arti berbeda.
        //
        // Cara ini hanya bisa dipakai di sini. Wilayah sungai paling banyak
        // tiga empat; DAS ada 62, dan memberi label pada semuanya akan jadi
        // tulisan yang saling menimpa.
        const namaPendek = w.nama.replace(/^Wilayah Sungai\s+/i, '');

        L.marker([w.lat, w.lng], { icon: penandaPolos(w.aktif ? '#935c13' : '#475569') })
            .addTo(peta)
            // Muncul saat penunjuk lewat, bukan terus-menerus: label yang
            // selalu tampil menutupi peta yang justru ingin dilihat.
            //
            // Sengaja bukan saat diklik. Klik sudah membuka panel di sebelah
            // yang memuat nama, kode, jumlah DAS, juru, dan laporan — label
            // yang muncul bersamaan cuma mengulang yang sudah terbaca di situ.
            .bindTooltip(`${lolos(namaPendek)} &middot; ${w.das} DAS`, {
                direction: 'right',
                offset: [10, 0],
                className: 'label-peta',
            })
            .on('click', () => tampilkanPanelWs(w, panel, kosong));
    });

    peta.aturPandangan = () => {
        if (daftar.length > 1) {
            peta.fitBounds(daftar.map((w) => [w.lat, w.lng]), { padding: [60, 60], maxZoom: 9 });
        } else {
            peta.setView([daftar[0].lat, daftar[0].lng], 8);
        }
    };
    requestAnimationFrame(peta.segarkan);
}

function tampilkanPanelWs(w, panel, kosong) {
    if (!panel) return;

    kosong?.classList.add('hidden');
    panel.classList.remove('hidden');

    panel.innerHTML = `
        <div class="border-b border-garis-halus px-5 py-4">
            <p class="text-[12px] font-bold uppercase tracking-wider text-peringatan-teks">${lolos(w.kode)}</p>
            <h2 class="mt-0.5 text-base font-extrabold text-judul">${lolos(w.nama)}</h2>
        </div>

        <div class="grid grid-cols-3 divide-x divide-garis-halus border-b border-garis-halus">
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums text-judul">${w.das}</p>
                <p class="text-[11px] font-medium text-teks-samar">DAS</p>
            </div>
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums text-judul">${w.juru}</p>
                <p class="text-[11px] font-medium text-teks-samar">Juru</p>
            </div>
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums text-judul">${w.laporan}</p>
                <p class="text-[11px] font-medium text-teks-samar">Laporan</p>
            </div>
        </div>

        <p class="px-5 py-3 text-[12px] leading-relaxed text-teks-samar">
            Penanda diletakkan pada rata-rata titik ${w.das_bertitik} DAS yang sudah bertitik.
            Wilayah sungai sendiri berupa wilayah, bukan titik.
        </p>

        <div class="border-t border-garis-halus px-5 py-4">
            <a href="${w.url}" class="rounded-xl bg-aksi px-3.5 py-2 text-[13px] font-bold text-aksi-teks transition hover:bg-aksi-sorot">
                Detail Wilayah Sungai
            </a>
        </div>`;
}

/* ---------- Pemilih koordinat pada formulir DAS ---------- */
function siapkanPemilihKoordinat() {
    const wadah = document.getElementById('peta-pilih');
    if (!wadah || wadah._leaflet_id) return;

    const isiLat = document.getElementById('koordinat_lat');
    const isiLng = document.getElementById('koordinat_lng');
    const label = document.getElementById('label-koordinat');
    const tombolHapus = document.getElementById('hapus-koordinat');

    const latAwal = parseFloat(isiLat.value);
    const lngAwal = parseFloat(isiLng.value);
    const ada = !Number.isNaN(latAwal) && !Number.isNaN(lngAwal);

    const peta = petaDasar(wadah, ada ? [latAwal, lngAwal] : PUSAT_KALSEL, ada ? 12 : 8);

    let tanda = null;

    function taruh(lat, lng) {
        if (tanda) peta.removeLayer(tanda);
        tanda = L.marker([lat, lng], { icon: penanda('#12639e', '') }).addTo(peta);
        isiLat.value = lat.toFixed(7);
        isiLng.value = lng.toFixed(7);
        if (label) label.textContent = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        tombolHapus?.classList.remove('hidden');
    }

    if (ada) taruh(latAwal, lngAwal);

    peta.on('click', (e) => taruh(e.latlng.lat, e.latlng.lng));

    tombolHapus?.addEventListener('click', () => {
        if (tanda) peta.removeLayer(tanda);
        tanda = null;
        isiLat.value = '';
        isiLng.value = '';
        if (label) label.textContent = 'Belum ditentukan';
        tombolHapus.classList.add('hidden');
    });
}

/* ---------- Peta lokasi bangunan pada satu laporan ---------- */
function siapkanPetaLaporan() {
    const wadah = document.getElementById('peta-laporan');
    if (!wadah || wadah._leaflet_id) return;

    const titik = JSON.parse(wadah.dataset.titik || '[]');
    if (!titik.length) return;

    const peta = petaDasar(wadah, [titik[0].lat, titik[0].lng], 14);

    titik.forEach((t) => {
        const warna = t.bermasalah ? '#a50e26' : '#12639e';
        const judul = [t.nama, t.label].filter(Boolean).join(' — ');

        L.marker([t.lat, t.lng], { icon: penanda(warna, t.nomor) })
            .addTo(peta)
            .bindPopup(
                `<strong>Bangunan ${t.nomor}</strong><br>${judul}<br>` +
                `<span style="color:#64748b">${t.lat.toFixed(5)}, ${t.lng.toFixed(5)}</span>`
            );
    });

    // Satu titik tetap perlu setView, bukan fitBounds, supaya zoom-nya masuk akal.
    peta.aturPandangan = () => {
        if (titik.length > 1) {
            peta.fitBounds(titik.map((t) => [t.lat, t.lng]), { padding: [50, 50], maxZoom: 16 });
        } else {
            peta.setView([titik[0].lat, titik[0].lng], 15);
        }
    };
    requestAnimationFrame(peta.segarkan);
}

/* ---------- Peta sebaran seluruh bangunan ---------- */
function siapkanPetaSebaran() {
    const wadah = document.getElementById('peta-sebaran');
    if (!wadah || wadah._leaflet_id) return;

    const titik = JSON.parse(wadah.dataset.titik || '[]');
    const daftarDas = JSON.parse(wadah.dataset.das || '[]');
    if (!titik.length) return;

    const peta = petaDasar(wadah, [titik[0].lat, titik[0].lng], 9);

    // Bangunan dan DAS dipisah ke lapisan sendiri-sendiri. Kelak, bila batas
    // wilayah DAS sudah didapat, lapisan DAS tinggal diganti isinya menjadi
    // poligon tanpa mengubah apa pun di sekitarnya.
    const lapisanBangunan = L.layerGroup().addTo(peta);
    const lapisanDas = L.layerGroup().addTo(peta);

    titik.forEach((t) => {
        const bermasalah = t.bermasalah > 0;
        const warna = bermasalah ? '#a50e26' : '#12639e';

        const baris = [
            `<strong>${lolos(t.jenis)}</strong>`,
            t.nama ? lolos(t.nama) : null,
            t.sungai ? `Sungai ${lolos(t.sungai)}` : null,
            t.das ? `<span style="color:#64748b">${lolos(t.das)}</span>` : null,
            t.juru ? `<span style="color:#64748b">${lolos(t.juru)} &middot; ${t.tanggal || ''}</span>` : null,
            bermasalah
                ? `<span style="color:#a50e26;font-weight:700">${t.bermasalah} temuan bermasalah</span>`
                : '<span style="color:#0d6132">Tidak ada temuan bermasalah</span>',
            t.url ? `<a href="${t.url}" style="color:#12639e;font-weight:700">Buka laporan</a>` : null,
        ].filter(Boolean);

        L.circleMarker([t.lat, t.lng], {
            radius: 7,
            color: '#fff',
            weight: 2,
            fillColor: warna,
            fillOpacity: 0.9,
        })
            .addTo(lapisanBangunan)
            .bindPopup(baris.join('<br>'));
    });

    // Teal, bukan biru tua. Penanda bangunan yang tidak bermasalah sudah biru,
    // dan dua biru berdekatan membuat kedua lapisan sukar dibedakan sekilas.
    daftarDas.forEach((d) => {
        L.marker([d.lat, d.lng], { icon: penanda('#156b71', '') })
            .addTo(lapisanDas)
            .bindPopup(
                `<strong>${lolos(d.nama)}</strong><br>` +
                `<span style="color:#64748b">${lolos(d.kode)} &middot; titik perkiraan</span><br>` +
                `<a href="${d.url}" style="color:#12639e;font-weight:700">Detail DAS</a>`
            );
    });

    const saklar = document.getElementById('lapisan-das');
    saklar?.addEventListener('change', () => {
        if (saklar.checked) lapisanDas.addTo(peta);
        else peta.removeLayer(lapisanDas);
    });

    peta.aturPandangan = () => {
        if (titik.length > 1) {
            peta.fitBounds(titik.map((t) => [t.lat, t.lng]), { padding: [40, 40], maxZoom: 13 });
        } else {
            peta.setView([titik[0].lat, titik[0].lng], 13);
        }
    };
    requestAnimationFrame(peta.segarkan);
}

/**
 * Dijadikan fungsi agar dapat dipanggil ulang. Halaman Laporan OP mengganti
 * area hasilnya tanpa memuat ulang halaman, sehingga elemen petanya benar-benar
 * baru dan harus dibangun lagi dari nol.
 *
 * Elemen yang sudah dipasangi peta dilewati, sebab Leaflet menolak memasang
 * dua peta pada satu elemen yang sama.
 */
function siapkanSemuaPeta() {
    siapkanPetaDas();
    siapkanPetaWs();
    siapkanPemilihKoordinat();
    siapkanPetaLaporan();
    siapkanPetaSebaran();
}

window.siapkanSemuaPeta = siapkanSemuaPeta;
siapkanSemuaPeta();
