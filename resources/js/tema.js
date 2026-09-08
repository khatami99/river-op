/**
 * Tema terang dan gelap.
 *
 * Bawaannya mengikuti setelan perangkat: selama pengguna belum memilih apa
 * pun, tidak ada atribut apa pun di <html> dan CSS yang menentukan lewat
 * prefers-color-scheme. Begitu pengguna memilih sendiri, pilihannya menimpa
 * setelan sistem dan diingat — tetapi hanya di peramban itu, sebab ini
 * kenyamanan pribadi, bukan data dinas yang perlu disimpan di server.
 *
 * Pemasangan awalnya TIDAK di berkas ini, melainkan pada skrip kecil di
 * <head>. Modul ini dimuat setelah halaman digambar, dan menunggu sampai ke
 * sini berarti halaman sempat berkedip putih sepersekian detik sebelum
 * berubah gelap.
 */

const KUNCI = 'tema';

function pilihanTersimpan() {
    try {
        const nilai = localStorage.getItem(KUNCI);
        return nilai === 'gelap' || nilai === 'terang' ? nilai : 'sistem';
    } catch (e) {
        // Peramban yang memblokir penyimpanan situs melempar di sini.
        return 'sistem';
    }
}

function tandaiTombol(pilihan) {
    document.querySelectorAll('[data-pengalih-tema] [data-tema-pilihan]').forEach((tombol) => {
        const aktif = tombol.dataset.temaPilihan === pilihan;
        tombol.setAttribute('aria-pressed', aktif ? 'true' : 'false');

        // Rupa pilihan aktif diserahkan ke CSS: tombol yang sama dipakai di
        // atas rel navigasi yang gelap dan di kepala halaman yang mengikuti
        // tema, dan keduanya butuh warna berbeda.
        tombol.classList.toggle('tema-aktif', aktif);
    });
}

function terapkan(pilihan) {
    const akar = document.documentElement;

    if (pilihan === 'gelap' || pilihan === 'terang') {
        akar.dataset.tema = pilihan;
    } else {
        delete akar.dataset.tema;
    }

    try {
        pilihan === 'sistem'
            ? localStorage.removeItem(KUNCI)
            : localStorage.setItem(KUNCI, pilihan);
    } catch (e) {
        // Tidak apa-apa: temanya tetap berubah, cuma tidak diingat.
    }

    tandaiTombol(pilihan);
}

document.addEventListener('click', (e) => {
    const tombol = e.target.closest('[data-tema-pilihan]');
    if (tombol) terapkan(tombol.dataset.temaPilihan);
});

// Perubahan setelan perangkat saat pengguna memakai "ikut sistem" ditangani
// CSS sendiri lewat prefers-color-scheme; tidak ada yang perlu dikerjakan
// JavaScript di situ.
tandaiTombol(pilihanTersimpan());
