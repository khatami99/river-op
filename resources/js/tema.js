/**
 * Tema terang dan gelap.
 *
 * Bawaannya mengikuti setelan perangkat: selama pengguna belum memilih apa
 * pun, tidak ada atribut apa pun di <html> dan CSS yang menentukan lewat
 * prefers-color-scheme. Begitu pengguna memencet sakelar, pilihannya menimpa
 * setelan sistem dan diingat — tetapi hanya di peramban itu, sebab ini
 * kenyamanan pribadi, bukan data dinas yang perlu disimpan di server.
 *
 * Pemasangan awalnya TIDAK di berkas ini, melainkan pada skrip kecil di
 * <head>. Modul ini dimuat setelah halaman digambar, dan menunggu sampai ke
 * sini berarti halaman sempat berkedip putih sepersekian detik sebelum
 * berubah gelap. Rupa ikon sakelarnya pun bukan urusan berkas ini melainkan
 * CSS, dengan alasan yang sama.
 */

const KUNCI = 'tema';

/**
 * Tema yang sedang TERLIHAT, bukan yang tersimpan.
 *
 * Keduanya berbeda selama pengguna belum pernah memilih: yang tersimpan
 * kosong, yang terlihat ditentukan perangkat. Sakelar harus bekerja atas
 * yang terlihat — kalau tidak, pencetan pertama di perangkat bermode gelap
 * malah menyalakan mode gelap lagi dan seolah-olah tombolnya rusak.
 */
function temaTerlihat() {
    const dipaksa = document.documentElement.dataset.tema;

    if (dipaksa === 'gelap' || dipaksa === 'terang') {
        return dipaksa;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'gelap' : 'terang';
}

function tandaiSakelar() {
    const berikutnya = temaTerlihat() === 'gelap' ? 'terang' : 'gelap';
    const label = `Ganti ke mode ${berikutnya}`;

    document.querySelectorAll('[data-sakelar-tema]').forEach((tombol) => {
        tombol.title = label;
        tombol.setAttribute('aria-label', label);
    });
}

function terapkan(pilihan) {
    document.documentElement.dataset.tema = pilihan;

    try {
        localStorage.setItem(KUNCI, pilihan);
    } catch (e) {
        // Peramban yang memblokir penyimpanan situs melempar di sini. Tidak
        // apa-apa: temanya tetap berubah, cuma tidak diingat.
    }

    tandaiSakelar();
}

document.addEventListener('click', (e) => {
    if (e.target.closest('[data-sakelar-tema]')) {
        terapkan(temaTerlihat() === 'gelap' ? 'terang' : 'gelap');
    }
});

// Selama pengguna masih ikut setelan perangkat, temanya sendiri sudah diurus
// CSS lewat prefers-color-scheme. Yang tidak ikut berubah cuma tulisan pada
// tombolnya, dan itulah satu-satunya alasan pendengar ini ada.
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', tandaiSakelar);

tandaiSakelar();
