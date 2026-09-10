/**
 * Daftar kabupaten pada formulir DAS mengikuti wilayah sungai yang dipilih.
 *
 * Seluruh kemungkinannya sudah digambar di halaman, satu blok per wilayah
 * sungai, dan tugas berkas ini cuma menentukan mana yang terbuka. Blok yang
 * benar sudah terbuka sejak dari server, jadi tanpa berkas ini formulirnya
 * tetap benar — yang hilang hanya kemampuan berganti daftar saat pilihan
 * wilayah sungai diubah, tanpa memuat ulang halaman.
 *
 * Yang tersembunyi ikut DIMATIKAN, bukan sekadar disembunyikan. Kotak centang
 * yang disabled tidak ikut terkirim, dan tanpa itu memindahkan DAS ke wilayah
 * sungai lain akan menyeret kabupaten lamanya ikut tersimpan — kabupaten yang
 * bahkan tidak disinggung wilayah sungai barunya.
 */

const pilihanWilayah = document.getElementById('wilayah_sungai_id');
const blok = document.querySelectorAll('[data-kabupaten-ws]');

if (pilihanWilayah && blok.length > 0) {
    const segarkan = () => {
        blok.forEach((wadah) => {
            const cocok = wadah.dataset.kabupatenWs === pilihanWilayah.value;

            wadah.hidden = ! cocok;
            wadah.querySelectorAll('input[type="checkbox"]').forEach((kotak) => {
                kotak.disabled = ! cocok;
            });
        });
    };

    pilihanWilayah.addEventListener('change', segarkan);
    segarkan();
}
