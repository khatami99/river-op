/**
 * Panel kabupaten pada formulir DAS: isinya mengikuti wilayah sungai yang
 * dipilih, dan ringkasannya mengikuti apa yang dicentang.
 *
 * Seluruh kemungkinannya sudah digambar di halaman, satu panel per wilayah
 * sungai, dan tugas berkas ini cuma menentukan mana yang terbuka. Panel yang
 * benar sudah terbuka sejak dari server berikut ringkasannya, jadi tanpa
 * berkas ini formulirnya tetap benar — yang hilang hanya kemampuan berganti
 * daftar dan memperbarui ringkasan tanpa memuat ulang halaman.
 *
 * Yang tersembunyi ikut DIMATIKAN, bukan sekadar disembunyikan. Kotak centang
 * yang disabled tidak ikut terkirim, dan tanpa itu memindahkan DAS ke wilayah
 * sungai lain akan menyeret kabupaten lamanya ikut tersimpan — kabupaten yang
 * bahkan tidak disinggung wilayah sungai barunya.
 */

const pilihanWilayah = document.getElementById('wilayah_sungai_id');
const blok = document.querySelectorAll('[data-kabupaten-ws]');

if (pilihanWilayah && blok.length > 0) {
    const ringkas = (wadah) => {
        const label = wadah.querySelector('[data-ringkasan-kabupaten]');
        if (! label) return;

        const nama = [...wadah.querySelectorAll('input[type="checkbox"]:checked')]
            .map((kotak) => kotak.closest('label').textContent.trim());

        label.textContent = nama.length > 0 ? nama.join(', ') : 'Pilih kabupaten';

        // Teks pengganti dibedakan dari isian sungguhan, sama seperti
        // placeholder pada kolom ketik di sekitarnya.
        label.classList.toggle('text-teks-samar', nama.length === 0);
        label.classList.toggle('text-teks-tegas', nama.length > 0);
    };

    const segarkan = () => {
        blok.forEach((wadah) => {
            const cocok = wadah.dataset.kabupatenWs === pilihanWilayah.value;

            wadah.hidden = ! cocok;
            wadah.querySelectorAll('input[type="checkbox"]').forEach((kotak) => {
                kotak.disabled = ! cocok;
            });

            ringkas(wadah);
        });
    };

    pilihanWilayah.addEventListener('change', segarkan);

    blok.forEach((wadah) => {
        wadah.addEventListener('change', (e) => {
            if (e.target.matches('input[type="checkbox"]')) ringkas(wadah);
        });
    });

    segarkan();
}
