/**
 * Penyaring langsung: hasil berubah begitu penyaringnya diubah, dan begitu
 * pengguna berhenti mengetik — tanpa memuat ulang halaman.
 *
 * Kenapa tidak menyaring di sisi peramban saja? Karena daftarnya berpaginasi:
 * yang ada di layar cuma sepuluh baris dari sekian puluh. Menyaring yang
 * sepuluh itu akan menjawab "tidak ada hasil" padahal ada, hanya di halaman
 * lain. Jadi penyaringannya tetap di server, tetapi yang dikirim ulang cuma
 * area hasilnya. Kotak pencarian tidak pernah kehilangan kursor karena tidak
 * pernah dibuat ulang.
 *
 * Tombol "Cari" sengaja dibiarkan. Ia jalan keluar bila JavaScript gagal
 * dimuat, dan sebagian orang memang menunggu ada tombol yang bisa ditekan.
 *
 * Dipasang pada tiap formulir yang menandai dirinya dengan data-saring,
 * menunjuk wadah hasilnya lewat data-hasil. Satu mesin untuk semua halaman
 * daftar; yang khas halaman Laporan OP — pemilih tabel/peta, tautan export,
 * kepala kolom yang bisa diurutkan — dicari secara opsional dan diabaikan
 * kalau tidak ada.
 */

function pasangSaringan(formulir, wadahHasil) {
    const kotakJumlah = document.getElementById(formulir.dataset.jumlah || 'jumlah-hasil');
    const tautanExport = document.getElementById('tautan-export');
    const kolomTampilan = document.getElementById('tampilan-aktif');

    let tundaan = null;
    let permintaanBerjalan = null;

    const alamatSekarang = () => {
        const data = new FormData(formulir);
        const parameter = new URLSearchParams();

        for (const [kunci, nilai] of data.entries()) {
            if (nilai !== '') parameter.append(kunci, nilai);
        }

        return parameter;
    };

    const segarkanHasil = async () => {
        const parameter = alamatSekarang();

        // Alamat di bilah peramban ikut berubah supaya halaman ini bisa
        // ditandai atau dibagikan lengkap dengan penyaringnya. Dipakai
        // replaceState, bukan pushState, agar satu kata yang diketik tidak
        // meninggalkan sepuluh langkah di tombol kembali.
        const alamatBaru = window.location.pathname + (parameter.toString() ? '?' + parameter : '');
        window.history.replaceState({}, '', alamatBaru);

        if (tautanExport) {
            tautanExport.href = tautanExport.dataset.dasar + (parameter.toString() ? '?' + parameter : '');
        }

        // Permintaan sebelumnya dibatalkan supaya ketikan cepat tidak membuat
        // jawaban lama datang belakangan dan menimpa yang baru.
        permintaanBerjalan?.abort();
        permintaanBerjalan = new AbortController();

        wadahHasil.classList.add('sedang-menyaring');

        try {
            const jawaban = await fetch(
                window.location.pathname + '?' + parameter + (parameter.toString() ? '&' : '') + 'fragmen=1',
                { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: permintaanBerjalan.signal }
            );

            if (! jawaban.ok) throw new Error('Gagal memuat hasil');

            const isi = await jawaban.json();
            wadahHasil.innerHTML = isi.hasil;
            if (kotakJumlah) kotakJumlah.textContent = isi.jumlah;

            // Peta perlu dibangun ulang, sebab elemennya baru saja diganti.
            window.siapkanSemuaPeta?.();
        } catch (galat) {
            if (galat.name !== 'AbortError') {
                // Bila gagal, halaman dimuat penuh sebagai cadangan supaya
                // pengguna tetap mendapat hasilnya.
                formulir.submit();
            }
        } finally {
            wadahHasil.classList.remove('sedang-menyaring');
        }
    };

    if (tautanExport) {
        tautanExport.dataset.dasar = tautanExport.href.split('?')[0];
    }

    // Dropdown dan centang: berubah sekali dan disengaja, jadi langsung disaring.
    formulir.querySelectorAll('select, input[type="checkbox"]').forEach((kolom) => {
        kolom.addEventListener('change', segarkanHasil);
    });

    // Ketikan: ditunggu sejenak setelah berhenti. Tanpa jeda, satu kata akan
    // memicu lima permintaan yang lima-limanya segera basi.
    formulir.querySelectorAll('input[type="search"], input[type="text"]').forEach((kolom) => {
        kolom.addEventListener('input', () => {
            clearTimeout(tundaan);
            tundaan = setTimeout(segarkanHasil, 260);
        });

        // Enter tidak perlu menunggu jeda, dan tidak boleh mengirim formulir.
        kolom.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(tundaan);
                segarkanHasil();
            }
        });
    });

    // Berpindah antara tabel dan peta memakai jalur yang sama, sehingga
    // penyaring yang sedang aktif tidak hilang saat bentuknya berganti.
    document.getElementById('pemilih-tampilan')?.addEventListener('click', (e) => {
        const tombol = e.target.closest('[data-tampilan]');
        if (! tombol || ! kolomTampilan) return;

        kolomTampilan.value = tombol.dataset.tampilan;

        document.querySelectorAll('#pemilih-tampilan [data-tampilan]').forEach((t) => {
            const aktif = t === tombol;
            t.classList.toggle('bg-permukaan', aktif);
            t.classList.toggle('text-judul', aktif);
            t.classList.toggle('shadow-sm', aktif);
            t.classList.toggle('text-teks-samar', ! aktif);
        });

        segarkanHasil();
    });

    // Kepala kolom yang bisa diurutkan berada di dalam area hasil, yang
    // diganti tiap penyaringan — jadi pendengarnya dipasang pada wadahnya,
    // bukan pada tautannya sendiri.
    wadahHasil.addEventListener('click', (e) => {
        const tautan = e.target.closest('[data-urut]');
        if (! tautan) return;

        e.preventDefault();

        const kolomUrut = document.getElementById('urut-aktif');
        const kolomArah = document.getElementById('arah-aktif');
        if (! kolomUrut || ! kolomArah) return;

        kolomUrut.value = tautan.dataset.urut;
        kolomArah.value = tautan.dataset.arah;

        // Mengganti urutan selalu mengembalikan pembaca ke halaman pertama.
        // Tetap di halaman tiga setelah urutannya berubah menyodorkan baris
        // yang seolah acak, sebab yang di situ sekarang bukan lagi yang tadi.
        formulir.querySelector('input[name="page"]')?.remove();

        segarkanHasil();
    });

    // Tautan halaman berikutnya ikut ditangani agar paginasi tidak memuat
    // ulang seluruh halaman dan membuat penyaring berkedip.
    wadahHasil.addEventListener('click', (e) => {
        const tautan = e.target.closest('a[href]');
        if (! tautan || ! tautan.href.includes('page=')) return;

        e.preventDefault();

        const halaman = new URL(tautan.href).searchParams.get('page');
        let kolomHalaman = formulir.querySelector('input[name="page"]');

        if (! kolomHalaman) {
            kolomHalaman = document.createElement('input');
            kolomHalaman.type = 'hidden';
            kolomHalaman.name = 'page';
            formulir.appendChild(kolomHalaman);
        }

        kolomHalaman.value = halaman;
        segarkanHasil().then(() => kolomHalaman.remove());
    });

    // Mengubah penyaring selalu mengembalikan pembaca ke halaman pertama;
    // tetap di halaman tujuh setelah menyaring hampir selalu menampilkan kosong.
    formulir.addEventListener('change', () => {
        formulir.querySelector('input[name="page"]')?.remove();
    });
}

document.querySelectorAll('form[data-saring]').forEach((formulir) => {
    const wadah = document.getElementById(formulir.dataset.hasil);
    if (wadah) pasangSaringan(formulir, wadah);
});

// Panel pilihan mengambang menutup sendiri ketika pengguna mengklik di luarnya,
// seperti dropdown biasa. Elemen <details> tidak melakukan itu sendiri.
document.addEventListener('click', (e) => {
    document.querySelectorAll('[data-panel-pilihan][open]').forEach((panel) => {
        if (! panel.contains(e.target)) panel.removeAttribute('open');
    });
});

// Tombol Escape juga menutupnya, kebiasaan yang sudah melekat pada dropdown.
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('[data-panel-pilihan][open]').forEach((p) => p.removeAttribute('open'));
});
