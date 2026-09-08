import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

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
        const warna = d.bermasalah > 0 ? '#c8102e' : (d.juru > 0 ? '#127cc4' : '#94a3b8');

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
            <a href="${j.url}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 transition hover:bg-slate-50">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sungai-50 text-[12px] font-extrabold text-sungai-700">${j.inisial}</span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-[13.5px] font-semibold text-slate-800">${j.nama}</span>
                    <span class="block truncate text-[12px] text-slate-500">${j.telepon}</span>
                </span>
            </a>`).join('')
        : '<p class="px-3 py-6 text-center text-[13px] text-slate-500">Belum ada juru bertugas di DAS ini.</p>';

    panel.innerHTML = `
        <div class="border-b border-slate-100 px-5 py-4">
            <p class="text-[12px] font-bold uppercase tracking-wider text-emas-600">${d.kode}</p>
            <h2 class="mt-0.5 text-base font-extrabold text-sungai-950">${d.nama}</h2>
            <p class="mt-0.5 text-[12px] text-slate-500">${d.wilayah}</p>
        </div>

        <div class="grid grid-cols-3 divide-x divide-slate-100 border-b border-slate-100">
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums text-sungai-950">${d.juru}</p>
                <p class="text-[11px] font-medium text-slate-500">Juru</p>
            </div>
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums text-sungai-950">${d.laporan}</p>
                <p class="text-[11px] font-medium text-slate-500">Laporan</p>
            </div>
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums ${d.bermasalah > 0 ? 'text-bata-600' : 'text-hijau-600'}">${d.bermasalah}</p>
                <p class="text-[11px] font-medium text-slate-500">Bermasalah</p>
            </div>
        </div>

        <div class="px-2 py-2">${daftarJuru}</div>

        <div class="flex flex-wrap gap-2 border-t border-slate-100 px-5 py-4">
            <a href="${d.url}" class="rounded-xl bg-slate-100 px-3.5 py-2 text-[13px] font-bold text-slate-700 transition hover:bg-slate-200">
                Detail DAS
            </a>
            ${d.url_tambah_juru ? `
                <a href="${d.url_tambah_juru}" class="rounded-xl bg-sungai-950 px-3.5 py-2 text-[13px] font-bold text-white transition hover:bg-sungai-900">
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
        // Angka pada penanda adalah jumlah DAS yang dinaungi wilayah ini.
        L.marker([w.lat, w.lng], { icon: penanda(w.aktif ? '#b3831f' : '#94a3b8', w.das) })
            .addTo(peta)
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
        <div class="border-b border-slate-100 px-5 py-4">
            <p class="text-[12px] font-bold uppercase tracking-wider text-emas-600">${lolos(w.kode)}</p>
            <h2 class="mt-0.5 text-base font-extrabold text-sungai-950">${lolos(w.nama)}</h2>
        </div>

        <div class="grid grid-cols-3 divide-x divide-slate-100 border-b border-slate-100">
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums text-sungai-950">${w.das}</p>
                <p class="text-[11px] font-medium text-slate-500">DAS</p>
            </div>
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums text-sungai-950">${w.juru}</p>
                <p class="text-[11px] font-medium text-slate-500">Juru</p>
            </div>
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums text-sungai-950">${w.laporan}</p>
                <p class="text-[11px] font-medium text-slate-500">Laporan</p>
            </div>
        </div>

        <p class="px-5 py-3 text-[12px] leading-relaxed text-slate-500">
            Penanda diletakkan pada rata-rata titik ${w.das_bertitik} DAS yang sudah bertitik.
            Wilayah sungai sendiri berupa wilayah, bukan titik.
        </p>

        <div class="border-t border-slate-100 px-5 py-4">
            <a href="${w.url}" class="rounded-xl bg-sungai-950 px-3.5 py-2 text-[13px] font-bold text-white transition hover:bg-sungai-900">
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
        tanda = L.marker([lat, lng], { icon: penanda('#127cc4', '') }).addTo(peta);
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
        const warna = t.bermasalah ? '#c8102e' : '#127cc4';
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
        const warna = bermasalah ? '#c8102e' : '#127cc4';

        const baris = [
            `<strong>${lolos(t.jenis)}</strong>`,
            t.nama ? lolos(t.nama) : null,
            t.sungai ? `Sungai ${lolos(t.sungai)}` : null,
            t.das ? `<span style="color:#64748b">${lolos(t.das)}</span>` : null,
            t.juru ? `<span style="color:#64748b">${lolos(t.juru)} &middot; ${t.tanggal || ''}</span>` : null,
            bermasalah
                ? `<span style="color:#c8102e;font-weight:700">${t.bermasalah} temuan bermasalah</span>`
                : '<span style="color:#15803d">Tidak ada temuan bermasalah</span>',
            t.url ? `<a href="${t.url}" style="color:#127cc4;font-weight:700">Buka laporan</a>` : null,
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
        L.marker([d.lat, d.lng], { icon: penanda('#168088', '') })
            .addTo(lapisanDas)
            .bindPopup(
                `<strong>${lolos(d.nama)}</strong><br>` +
                `<span style="color:#64748b">${lolos(d.kode)} &middot; titik perkiraan</span><br>` +
                `<a href="${d.url}" style="color:#127cc4;font-weight:700">Detail DAS</a>`
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
