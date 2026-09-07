/**
 * Umpan balik gerak untuk dashboard.
 *
 * Aplikasi ini dirender di server, sehingga setiap klik berarti memuat halaman
 * baru. Di sela itu tidak terjadi apa-apa di layar, dan pengguna tidak tahu
 * apakah kliknya masuk atau aplikasinya macet. Semua yang ada di berkas ini
 * menjawab pertanyaan itu — bukan hiasan.
 *
 * Yang sengaja TIDAK dibuat: animasi masuk pada setiap kartu, angka yang
 * berhitung naik, dan peralihan halus antarhalaman. Semuanya menunda saat
 * pegawai bisa mulai membaca, dan pegawai dinas membuka halaman yang sama
 * puluhan kali sehari — gerak yang menyenangkan di kali pertama menjadi
 * penghalang di kali kelima puluh.
 */

const hematGerak = window.matchMedia('(prefers-reduced-motion: reduce)');

/* ---------- Bilah kemajuan saat berpindah halaman ---------- */

function siapkanBilahKemajuan() {
    const bilah = document.createElement('div');
    bilah.id = 'bilah-kemajuan';
    bilah.innerHTML = '<span></span>';
    document.body.appendChild(bilah);

    const isi = bilah.firstElementChild;
    let denyut = null;
    let tundaan = null;
    let jalan = false;

    function mulai() {
        if (jalan) return;
        jalan = true;

        // Ditunda sebentar: perpindahan yang cepat tidak perlu dikabari, dan
        // bilah yang berkedip sekejap justru terasa seperti gangguan.
        tundaan = setTimeout(() => {
            bilah.classList.add('tampil');

            let maju = 0;
            denyut = setInterval(() => {
                // Melambat mendekati ujung. Tidak pernah sampai 100 persen
                // sebelum halamannya benar-benar berganti, supaya tidak
                // berbohong bahwa pemuatan sudah selesai.
                maju += Math.max(0.5, (92 - maju) / 12);
                isi.style.width = Math.min(maju, 92) + '%';
            }, 130);
        }, 140);
    }

    function selesai() {
        clearTimeout(tundaan);
        clearInterval(denyut);
        jalan = false;

        if (! bilah.classList.contains('tampil')) {
            isi.style.width = '0%';
            return;
        }

        isi.style.width = '100%';
        setTimeout(() => {
            bilah.classList.remove('tampil');
            setTimeout(() => { isi.style.width = '0%'; }, 220);
        }, 160);
    }

    // Tautan biasa: perpindahan sungguhan, halaman lama masih terlihat sampai
    // halaman baru siap, jadi bilahnya sempat terbaca.
    document.addEventListener('click', (e) => {
        const tautan = e.target.closest('a');

        if (! tautan || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey) return;
        if (tautan.target === '_blank' || tautan.hasAttribute('download') || tautan.dataset.unduh !== undefined) return;

        const alamat = tautan.getAttribute('href') || '';
        if (! alamat || alamat.startsWith('#') || alamat.startsWith('mailto:') || alamat.startsWith('tel:')) return;
        if (tautan.origin !== window.location.origin) return;

        mulai();
    });

    document.addEventListener('submit', (e) => {
        if (! e.defaultPrevented) mulai();
    });

    // Kembali lewat tombol back mengambil halaman dari cache peramban tanpa
    // memuat ulang, sehingga bilahnya harus dihentikan sendiri.
    window.addEventListener('pageshow', selesai);
    window.addEventListener('pagehide', selesai);

    return { mulai, selesai };
}

/* ---------- Tombol unduh: berkas tidak mengganti halaman ---------- */

function siapkanTombolUnduh() {
    // Mengunduh tidak memicu perpindahan halaman, sehingga bilah kemajuan tidak
    // akan pernah berhenti sendiri. Tombolnya diberi penanda tersendiri, dan
    // penanda itu berhenti ketika server menaruh kuki tanda berkasnya sudah
    // dikirim — bukan setelah waktu tertentu, supaya tidak berbohong pada
    // berkas yang lambat dibuat.
    document.querySelectorAll('[data-unduh]').forEach((tombol) => {
        tombol.addEventListener('click', () => {
            if (tombol.classList.contains('sedang-menyiapkan')) return;

            const tanda = 'unduhan-' + Date.now();
            document.cookie = 'unduhan_siap=; Max-Age=0; path=/';
            tombol.dataset.tanda = tanda;
            tombol.classList.add('sedang-menyiapkan');

            const mulai = Date.now();
            const periksa = setInterval(() => {
                const selesai = document.cookie.includes('unduhan_siap=');

                // Batas 30 detik hanya jaring pengaman bila kukinya gagal
                // sampai; tanpa itu tombolnya bisa terkunci selamanya.
                if (selesai || Date.now() - mulai > 30000) {
                    clearInterval(periksa);
                    tombol.classList.remove('sedang-menyiapkan');
                    document.cookie = 'unduhan_siap=; Max-Age=0; path=/';
                }
            }, 250);
        });
    });
}

/* ---------- Pesan sesaat ---------- */

function siapkanPesanSesaat() {
    document.querySelectorAll('[data-pesan-sesaat]').forEach((pesan) => {
        // Pesan berhasil menutup sendiri karena tugasnya sudah selesai begitu
        // terbaca. Pesan galat dibiarkan, sebab pengguna mungkin perlu
        // membacanya ulang sambil membetulkan isian.
        if (pesan.dataset.pesanSesaat !== 'sukses') return;

        setTimeout(() => {
            pesan.style.opacity = '0';
            pesan.style.transform = 'translateY(-6px)';
            setTimeout(() => pesan.remove(), 300);
        }, 5000);
    });
}

/* ---------- Peta: penanda memuat ---------- */

function siapkanPenandaPeta() {
    // Ubin peta datang dari internet dan bisa memakan beberapa detik. Tanpa
    // penanda, kotak abu-abu terbaca sebagai peta yang rusak.
    document.querySelectorAll('#peta-das, #peta-sebaran, #peta-laporan, #peta-pilih').forEach((wadah) => {
        wadah.classList.add('peta-memuat');

        const pengamat = new MutationObserver(() => {
            if (wadah.querySelector('img.leaflet-tile-loaded')) {
                wadah.classList.remove('peta-memuat');
                pengamat.disconnect();
            }
        });

        pengamat.observe(wadah, { childList: true, subtree: true });

        // Jaring pengaman bila peta gagal memuat sama sekali.
        setTimeout(() => {
            wadah.classList.remove('peta-memuat');
            pengamat.disconnect();
        }, 15000);
    });
}

if (! hematGerak.matches) {
    siapkanBilahKemajuan();
    siapkanPenandaPeta();
}

siapkanTombolUnduh();
siapkanPesanSesaat();
