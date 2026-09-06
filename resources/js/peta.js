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

function petaDasar(elemen, pusat, zoom) {
    const peta = L.map(elemen, { scrollWheelZoom: true }).setView(pusat, zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; Kontributor OpenStreetMap',
    }).addTo(peta);

    // Modul ini berjalan sebelum tata letak selesai, sehingga Leaflet sempat
    // mengira kontainernya lebih kecil: petak abu-abu muncul dan fitBounds
    // meleset. Ukurannya dihitung ulang begitu kontainer berubah.
    if (typeof ResizeObserver !== 'undefined') {
        new ResizeObserver(() => peta.invalidateSize()).observe(elemen);
    }
    requestAnimationFrame(() => peta.invalidateSize());

    return peta;
}

// Titik tengah Kalimantan Selatan, dipakai bila belum ada DAS berkoordinat.
const PUSAT_KALSEL = [-3.0926, 115.2838];

/* ---------- Halaman peta sebaran DAS ---------- */
function siapkanPetaDas() {
    const wadah = document.getElementById('peta-das');
    if (!wadah) return;

    const daftar = JSON.parse(wadah.dataset.das || '[]');
    const berkoordinat = daftar.filter((d) => d.lat !== null && d.lng !== null);

    const peta = petaDasar(wadah, PUSAT_KALSEL, 8);

    // Dijalankan setelah invalidateSize agar batas pandang dihitung
    // terhadap ukuran kontainer yang sebenarnya.
    if (berkoordinat.length) {
        requestAnimationFrame(() => {
            peta.invalidateSize();
            peta.fitBounds(berkoordinat.map((d) => [d.lat, d.lng]), {
                padding: [60, 60],
                maxZoom: 11,
            });
        });
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
                    <span class="block truncate text-[12px] text-slate-400">${j.nip}</span>
                </span>
            </a>`).join('')
        : '<p class="px-3 py-6 text-center text-[13px] text-slate-400">Belum ada juru bertugas di DAS ini.</p>';

    panel.innerHTML = `
        <div class="border-b border-slate-100 px-5 py-4">
            <p class="text-[11px] font-bold uppercase tracking-widest text-emas-600">${d.kode}</p>
            <h2 class="mt-0.5 text-base font-extrabold text-sungai-950">${d.nama}</h2>
            <p class="mt-0.5 text-[12px] text-slate-400">${d.wilayah}</p>
        </div>

        <div class="grid grid-cols-3 divide-x divide-slate-100 border-b border-slate-100">
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums text-sungai-950">${d.juru}</p>
                <p class="text-[11px] font-medium text-slate-400">Juru</p>
            </div>
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums text-sungai-950">${d.laporan}</p>
                <p class="text-[11px] font-medium text-slate-400">Laporan</p>
            </div>
            <div class="px-3 py-3 text-center">
                <p class="text-lg font-extrabold tabular-nums ${d.bermasalah > 0 ? 'text-bata-600' : 'text-hijau-600'}">${d.bermasalah}</p>
                <p class="text-[11px] font-medium text-slate-400">Bermasalah</p>
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

/* ---------- Pemilih koordinat pada formulir DAS ---------- */
function siapkanPemilihKoordinat() {
    const wadah = document.getElementById('peta-pilih');
    if (!wadah) return;

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
    if (!wadah) return;

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

    // Pandangan selalu diatur ulang setelah ukuran dihitung, termasuk saat
    // titiknya hanya satu. Tanpa itu Leaflet tidak menggambar ulang dan
    // petaknya hanya terisi di sebagian kontainer.
    requestAnimationFrame(() => {
        peta.invalidateSize();
        if (titik.length > 1) {
            peta.fitBounds(titik.map((t) => [t.lat, t.lng]), { padding: [50, 50], maxZoom: 16 });
        } else {
            peta.setView([titik[0].lat, titik[0].lng], 15);
        }
    });
}

siapkanPetaDas();
siapkanPemilihKoordinat();
siapkanPetaLaporan();
