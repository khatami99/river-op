/**
 * Panel pilih banyak: ringkasan pada kepalanya, dan penyaring di dalamnya.
 *
 * Isi panelnya kotak centang sungguhan yang digambar server, jadi tanpa
 * berkas ini panel masih bisa dibuka, dicentang, dan nilainya tetap terkirim.
 * Yang ditambahkan di sini cuma dua kenyamanan: kepala panel menyebutkan apa
 * saja yang sudah terpilih tanpa perlu dibuka, dan daftar yang panjang bisa
 * disaring sambil mengetik.
 *
 * Panelnya sengaja tidak menutup saat sesuatu dicentang. Itu memang sifat
 * <details>, dan di sini itulah yang benar: orang jarang memilih tepat satu
 * lalu selesai, dan panel yang menutup sendiri sesudah pilihan pertama tidak
 * memberi tanda apa pun bahwa masih boleh menambah.
 */

function pasangPanel(panel) {
    const ringkasan = panel.querySelector('[data-ringkasan]');
    const daftar = panel.querySelector('[data-daftar]');
    const cari = panel.querySelector('[data-cari-panel]');
    const kosongCari = panel.querySelector('[data-kosong-cari]');

    const ringkas = () => {
        if (! ringkasan) return;

        const nama = [...daftar.querySelectorAll('input[type="checkbox"]:checked')]
            .map((kotak) => kotak.closest('label').dataset.baris);

        ringkasan.textContent = nama.length > 0 ? nama.join(', ') : ringkasan.dataset.kosong;

        // Teks pengganti dibedakan dari isian sungguhan, sama seperti
        // placeholder pada kolom ketik di sekitarnya.
        ringkasan.classList.toggle('text-teks-samar', nama.length === 0);
        ringkasan.classList.toggle('text-teks-tegas', nama.length > 0);
    };

    const saring = () => {
        const dicari = cari.value.trim().toLowerCase();
        let tampak = 0;

        daftar.querySelectorAll('[data-baris]').forEach((baris) => {
            // Yang tersaring cuma disembunyikan, tidak dimatikan. Pilihan yang
            // sudah tercentang lalu tidak lagi cocok dengan ketikan tetap harus
            // ikut terkirim — menyaring itu melihat, bukan membatalkan.
            const cocok = baris.dataset.baris.toLowerCase().includes(dicari);
            baris.hidden = ! cocok;
            if (cocok) tampak++;
        });

        if (kosongCari) kosongCari.hidden = tampak > 0;
    };

    panel.addEventListener('change', (e) => {
        if (e.target.matches('input[type="checkbox"]')) ringkas();
    });

    if (cari) {
        cari.addEventListener('input', saring);

        // Membuka panel langsung menaruh kursor di penyaringnya: kalau panel
        // ini cukup panjang sampai perlu disaring, menyaring itulah yang
        // hampir selalu jadi tindakan berikutnya.
        panel.addEventListener('toggle', () => {
            if (panel.open) {
                cari.focus();
            } else {
                cari.value = '';
                saring();
            }
        });
    }

    ringkas();
}

document.querySelectorAll('[data-panel-pilih]').forEach(pasangPanel);
