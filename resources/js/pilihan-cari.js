/**
 * Daftar pilihan yang bisa dicari dengan mengetik.
 *
 * Elemen select bawaan tidak bisa memuat kotak pencarian. Yang ia punya cuma
 * ketik-cepat: menekan huruf melompat ke pilihan yang DIAWALI huruf itu. Itu
 * sudah menolong, tetapi tidak menemukan "Batulicin" bila yang diingat cuma
 * penggalan "licin", dan tidak ada apa pun di layar yang memberi tahu bahwa
 * kemampuan itu ada.
 *
 * Jadi select-nya disembunyikan dan digantikan kotak ketik yang memakai
 * datalist — kotak cari bawaan peramban, yang mencocokkan penggalan di tengah
 * kata dan tampil sebagai daftar saran biasa di HP.
 *
 * Select-nya TIDAK dibuang, cuma disembunyikan: ia yang tetap mengirim id ke
 * server, dan bila skrip ini gagal dimuat, yang tampil adalah daftar pilihan
 * biasa yang tetap berfungsi penuh.
 */

function pasangPencarian(select) {
    if (select.dataset.pencarianTerpasang) return;
    select.dataset.pencarianTerpasang = '1';

    const daftar = document.createElement('datalist');
    daftar.id = 'daftar-' + (select.id || select.name || Math.random().toString(36).slice(2));

    // Tulisan pilihan dipetakan ke nilainya, sebab yang dikirim ke server
    // adalah id — sedangkan yang diketik pengguna adalah namanya.
    const nilaiDari = new Map();

    Array.from(select.options).forEach((pilihan) => {
        const teks = pilihan.textContent.trim();
        if (! pilihan.value || ! teks) return;

        nilaiDari.set(teks.toLowerCase(), pilihan.value);

        const baris = document.createElement('option');
        baris.value = teks;
        daftar.appendChild(baris);
    });

    if (nilaiDari.size === 0) return;

    const kotak = document.createElement('input');
    kotak.type = 'text';
    kotak.setAttribute('list', daftar.id);
    kotak.className = select.className;
    kotak.autocomplete = 'off';
    kotak.placeholder = select.dataset.pilihanCari || 'Ketik untuk mencari…';

    // Wajib dipindahkan ke kotak ketik. Peramban menolak memvalidasi kolom
    // yang tersembunyi dan justru menggagalkan pengiriman formulir dengan
    // galat "not focusable".
    if (select.required) {
        select.required = false;
        kotak.required = true;
    }

    const terpilih = select.selectedOptions[0];
    kotak.value = terpilih && terpilih.value ? terpilih.textContent.trim() : '';

    let terakhirSah = kotak.value;

    const cocokkan = () => {
        const nilai = nilaiDari.get(kotak.value.trim().toLowerCase());

        if (nilai === undefined) return false;

        select.value = nilai;
        terakhirSah = kotak.value.trim();

        // Diberitahukan, sebab penyaring langsung mendengarkan perubahan pada
        // select — dan mengubah nilainya lewat skrip tidak memicu apa pun.
        select.dispatchEvent(new Event('change', { bubbles: true }));

        return true;
    };

    kotak.addEventListener('input', () => {
        if (cocokkan()) kotak.setAttribute('aria-invalid', 'false');
    });

    // Ketikan yang tidak cocok dengan pilihan mana pun dikembalikan ke pilihan
    // sah terakhir. Membiarkannya berarti kotaknya menampilkan sesuatu yang
    // tidak pernah tersimpan.
    kotak.addEventListener('blur', () => {
        if (kotak.value.trim() === '') {
            if (! kotak.required) {
                select.value = '';
                terakhirSah = '';
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
            return;
        }

        if (! cocokkan()) kotak.value = terakhirSah;
    });

    // Sekali klik langsung membuka seluruh daftar, bukan menunggu diketik.
    kotak.addEventListener('focus', () => kotak.select());

    select.hidden = true;
    select.after(kotak, daftar);
}

document.querySelectorAll('select[data-pilihan-cari]').forEach(pasangPencarian);
