/**
 * Isian kabupaten berbentuk chip.
 *
 * Bentuk dasarnya adalah kolom teks berisi nama-nama dipisah koma, dan ITULAH
 * yang dikirim ke server. Berkas ini menyembunyikannya lalu memasang tampilan
 * chip di atasnya sambil terus menyelaraskan nilainya kembali ke kolom itu,
 * sehingga controller tidak perlu tahu apa-apa soal chip dan formulirnya tetap
 * bisa dipakai bila JavaScript mati.
 *
 * Kenapa tidak kolom koma saja: karena di situ salah ketik tidak pernah
 * ditegur. "Kotabru" tersimpan diam-diam, melahirkan kabupaten baru yang
 * kemudian muncul sebagai pilihan sah di formulir DAS selamanya — kegagalan
 * yang rupanya persis seperti keberhasilan.
 *
 * Maka aturan pokok berkas ini: MEMBUAT KABUPATEN BARU HARUS DISENGAJA. Nama
 * yang belum dikenal tidak pernah terpasang sendiri; barisnya harus benar-benar
 * dituju, dan chipnya diberi tanda "baru". Salah ketik berhenti sebagai "tidak
 * ketemu", bukan sebagai data.
 */

const KELAS_KOTAK = 'flex min-h-[46px] w-full flex-wrap items-center gap-1.5 rounded-xl bg-permukaan-lembut px-2 py-1.5 ring-1 ring-inset ring-garis transition focus-within:bg-permukaan focus-within:ring-2 focus-within:ring-sungai-500';
const KELAS_CHIP = 'inline-flex items-center gap-1.5 rounded-lg bg-permukaan-sedang py-1 pl-2.5 pr-1 text-[13px] font-medium text-teks-sedang';
const KELAS_KETIK = 'min-w-[9rem] flex-1 border-0 bg-transparent px-1.5 py-1 text-[13.5px] text-teks-tegas placeholder:text-teks-samar focus:outline-none focus:ring-0';
const KELAS_SARAN = 'scrollbar-halus absolute left-0 right-0 z-30 mt-2 max-h-60 overflow-auto rounded-2xl bg-permukaan p-1.5 shadow-lg shadow-sungai-950/10 ring-1 ring-garis';
const KELAS_BARIS = 'flex w-full cursor-pointer items-center gap-2 rounded-xl px-3 py-2.5 text-left text-[13.5px] text-teks-sedang transition hover:bg-permukaan-lembut';
const KELAS_SOROT = 'bg-permukaan-lembut';

/** Dua nama dianggap sama bila cuma beda huruf besar-kecil atau spasi. */
const samakan = (teks) => rapikan(teks).toLowerCase();
const rapikan = (teks) => teks.trim().replace(/\s+/gu, ' ');

function pasangChip(kolom) {
    let dikenal = [];

    try {
        dikenal = JSON.parse(kolom.dataset.saran || '[]');
    } catch (e) {
        // Atribut rusak bukan alasan mematikan formulir; sarannya saja yang hilang.
    }

    const dikenalTerkecil = new Set(dikenal.map(samakan));

    // Sumber kebenarannya daftar ini, bukan DOM. Nilai kolom tersembunyi selalu
    // diturunkan dari sini, tidak pernah sebaliknya.
    let terpilih = kolom.value.split(',').map(rapikan).filter(Boolean);

    const wadah = document.createElement('div');
    wadah.className = 'relative';

    const kotak = document.createElement('div');
    kotak.className = KELAS_KOTAK;

    const ketik = document.createElement('input');
    ketik.type = 'text';
    ketik.className = KELAS_KETIK;
    ketik.placeholder = 'Ketik nama kabupaten…';
    ketik.autocomplete = 'off';
    ketik.setAttribute('aria-label', 'Tambah kabupaten');

    const saran = document.createElement('div');
    saran.className = KELAS_SARAN;
    saran.hidden = true;

    const simpan = () => {
        kolom.value = terpilih.join(', ');
    };

    const tutupSaran = () => {
        saran.hidden = true;
        saran.replaceChildren();
    };

    const gambarChip = () => {
        kotak.querySelectorAll('[data-chip]').forEach((c) => c.remove());

        terpilih.forEach((nama, urutan) => {
            const chip = document.createElement('span');
            chip.dataset.chip = '';
            chip.className = KELAS_CHIP;
            chip.append(nama);

            // Nama yang belum ada di sistem ditandai SEBELUM disimpan, supaya
            // "aku baru saja membuat kabupaten" tidak pernah jadi kejutan.
            if (! dikenalTerkecil.has(samakan(nama))) {
                const tanda = document.createElement('span');
                tanda.className = 'rounded px-1 py-px text-[10px] font-bold uppercase tracking-wide text-peringatan-teks ring-1 ring-peringatan-teks/40';
                tanda.textContent = 'baru';
                chip.append(tanda);
            }

            const buang = document.createElement('button');
            buang.type = 'button';
            buang.className = 'flex h-5 w-5 items-center justify-center rounded text-teks-samar transition hover:bg-bahaya-latar hover:text-bahaya-teks';
            buang.setAttribute('aria-label', `Hapus ${nama}`);

            const silang = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            silang.setAttribute('class', 'h-3 w-3');
            silang.setAttribute('fill', 'none');
            silang.setAttribute('stroke', 'currentColor');
            silang.setAttribute('stroke-width', '2.5');
            silang.setAttribute('viewBox', '0 0 24 24');
            const garis = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            garis.setAttribute('stroke-linecap', 'round');
            garis.setAttribute('d', 'M6 6l12 12M18 6L6 18');
            silang.append(garis);
            buang.append(silang);

            buang.addEventListener('click', () => {
                terpilih.splice(urutan, 1);
                simpan();
                gambarChip();
                ketik.focus();
            });

            chip.append(buang);
            kotak.insertBefore(chip, ketik);
        });
    };

    const tambah = (nama) => {
        const bersih = rapikan(nama);

        if (bersih !== '' && ! terpilih.some((n) => samakan(n) === samakan(bersih))) {
            terpilih.push(bersih);
            simpan();
            gambarChip();
        }

        ketik.value = '';
        tutupSaran();
    };

    const buatBaris = (nama, sorot) => {
        const baris = document.createElement('button');
        baris.type = 'button';
        baris.className = sorot ? `${KELAS_BARIS} ${KELAS_SOROT}` : KELAS_BARIS;
        baris.dataset.pilih = nama;
        return baris;
    };

    const gambarSaran = () => {
        const dicari = samakan(ketik.value);
        saran.replaceChildren();

        if (dicari === '') {
            tutupSaran();
            return;
        }

        dikenal
            .filter((n) => samakan(n).includes(dicari))
            .filter((n) => ! terpilih.some((t) => samakan(t) === samakan(n)))
            .forEach((nama, urutan) => {
                // Baris pertama disorot supaya Enter langsung memilih yang
                // sudah ada — jalan tercepat justru menuju data yang benar.
                const baris = buatBaris(nama, urutan === 0);
                baris.textContent = nama;
                saran.append(baris);
            });

        const persis = dikenalTerkecil.has(dicari);
        const sudahDipakai = terpilih.some((t) => samakan(t) === dicari);

        if (! persis && ! sudahDipakai) {
            // Sengaja TIDAK disorot walaupun ia satu-satunya baris. Enter tidak
            // boleh melahirkan kabupaten; barisnya harus dituju dengan panah
            // atau diklik, supaya salah ketik berhenti sebagai "tidak ketemu".
            const baru = buatBaris(rapikan(ketik.value), false);

            const tambahIkon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            tambahIkon.setAttribute('class', 'h-4 w-4 shrink-0 text-peringatan-teks');
            tambahIkon.setAttribute('fill', 'none');
            tambahIkon.setAttribute('stroke', 'currentColor');
            tambahIkon.setAttribute('stroke-width', '2.4');
            tambahIkon.setAttribute('viewBox', '0 0 24 24');
            const salib = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            salib.setAttribute('stroke-linecap', 'round');
            salib.setAttribute('d', 'M12 5v14M5 12h14');
            tambahIkon.append(salib);

            const label = document.createElement('span');
            label.append('Tambah kabupaten baru: ');
            const tebal = document.createElement('strong');
            tebal.className = 'font-bold text-teks-tegas';
            tebal.textContent = rapikan(ketik.value);
            label.append(tebal);

            baru.append(tambahIkon, label);
            saran.append(baru);
        }

        saran.hidden = saran.childElementCount === 0;
    };

    const pindahSorotan = (arah) => {
        const baris = [...saran.querySelectorAll('[data-pilih]')];
        if (baris.length === 0) return;

        const kini = baris.findIndex((b) => b.classList.contains(KELAS_SOROT));
        const tuju = Math.max(0, Math.min(baris.length - 1, (kini < 0 ? -1 : kini) + arah));

        baris.forEach((b) => b.classList.remove(KELAS_SOROT));
        baris[tuju].classList.add(KELAS_SOROT);
        baris[tuju].scrollIntoView({ block: 'nearest' });
    };

    ketik.addEventListener('input', gambarSaran);

    ketik.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            pindahSorotan(e.key === 'ArrowDown' ? 1 : -1);
            return;
        }

        if (e.key === 'Enter') {
            // Ditahan walau tidak ada yang tersorot: kolom ini berada di dalam
            // formulir, dan Enter di sini berarti "pilih", bukan "simpan".
            e.preventDefault();
            const tersorot = saran.querySelector(`[data-pilih].${KELAS_SOROT}`);
            if (tersorot) tambah(tersorot.dataset.pilih);
            return;
        }

        if (e.key === 'Escape') {
            tutupSaran();
            return;
        }

        // Backspace pada kolom kosong membuang chip terakhir — kebiasaan yang
        // sudah dibawa orang dari kolom penerima surel.
        if (e.key === 'Backspace' && ketik.value === '' && terpilih.length > 0) {
            terpilih.pop();
            simpan();
            gambarChip();
        }
    });

    // mousedown, bukan click: blur mendahului click dan akan menutup panelnya
    // sebelum pilihannya sempat terbaca.
    saran.addEventListener('mousedown', (e) => {
        const baris = e.target.closest('[data-pilih]');
        if (! baris) return;

        e.preventDefault();
        tambah(baris.dataset.pilih);
    });

    kotak.addEventListener('mousedown', (e) => {
        if (e.target === kotak) {
            e.preventDefault();
            ketik.focus();
        }
    });

    ketik.addEventListener('blur', tutupSaran);

    kotak.append(ketik);
    wadah.append(kotak, saran);

    kolom.hidden = true;
    kolom.after(wadah);

    // Daftar ejaan hanya berguna pada bentuk dasar; sarannya kini muncul
    // sambil mengetik.
    document.querySelector('[data-daftar-ejaan]')?.remove();

    gambarChip();
    simpan();
}

document.querySelectorAll('[data-chip-kabupaten]').forEach(pasangChip);
