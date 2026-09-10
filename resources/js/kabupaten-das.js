/**
 * Panel kabupaten pada formulir DAS: isinya mengikuti wilayah sungai yang
 * dipilih.
 *
 * Seluruh kemungkinannya sudah digambar di halaman, satu panel per wilayah
 * sungai, dan tugas berkas ini cuma menentukan mana yang terbuka. Panel yang
 * benar sudah terbuka sejak dari server, jadi tanpa berkas ini formulirnya
 * tetap benar — yang hilang hanya kemampuan berganti daftar saat pilihan
 * wilayah sungai diubah, tanpa memuat ulang halaman.
 *
 * Ringkasan pada kepala panel dan penyaring di dalamnya bukan urusan berkas
 * ini; itu dikerjakan panel-pilih.js, yang melayani semua panel semacam itu
 * termasuk panel juru di formulir yang sama.
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
