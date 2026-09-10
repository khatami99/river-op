/**
 * Isian banyak-nilai berbentuk chip.
 *
 * Bentuk dasarnya selalu kolom teks berisi nilai-nilai dipisah koma, dan
 * ITULAH yang dikirim ke server. Berkas ini menyembunyikannya lalu memasang
 * tampilan chip di atasnya sambil terus menyelaraskan nilainya kembali ke
 * kolom itu, sehingga controller tidak perlu tahu apa-apa soal chip dan
 * formulirnya tetap bisa dipakai bila JavaScript mati.
 *
 * Dipakai dua tempat dengan watak berbeda:
 *
 *   Kabupaten pada wilayah sungai — nilainya nama, dan nama yang belum
 *   dikenal BOLEH dibuat. Kenapa tidak kolom koma biasa: di situ salah ketik
 *   tidak pernah ditegur; "Kotabru" tersimpan diam-diam, melahirkan kabupaten
 *   baru yang muncul sebagai pilihan sah selamanya — kegagalan yang rupanya
 *   persis seperti keberhasilan. Maka membuat yang baru harus DISENGAJA:
 *   barisnya tidak pernah tersorot dan harus dituju sendiri.
 *
 *   Juru pada DAS — nilainya id, dan tidak ada yang boleh dibuat dari sini.
 *   Mencopot chip di sini punya akibat, jadi tersedia peringatan yang muncul
 *   begitu ada yang dicopot dan hilang lagi bila dikembalikan.
 *
 * Atribut yang dibaca:
 *   data-chip              menyalakan
 *   data-chip-pilihan      JSON [{nilai, label, catatan?}]
 *   data-chip-baru         bila ada, nilai yang belum dikenal boleh dibuat
 *   data-chip-petunjuk     tulisan pemandu di kolom ketik
 *   data-chip-peringatan   kalimat peringatan; {nama} diganti daftar yang dicopot
 *   data-chip-kosong       kalimat saat memang belum ada apa pun terdaftar
 */

const KELAS_KOTAK = 'flex min-h-[46px] w-full flex-wrap items-center gap-1.5 rounded-xl bg-permukaan-lembut px-2 py-1.5 ring-1 ring-inset ring-garis transition focus-within:bg-permukaan focus-within:ring-2 focus-within:ring-sungai-500';
const KELAS_CHIP = 'inline-flex items-center gap-1.5 rounded-lg bg-permukaan-sedang py-1 pl-2.5 pr-1 text-[13px] font-medium text-teks-sedang';
const KELAS_KETIK = 'min-w-[9rem] flex-1 border-0 bg-transparent px-1.5 py-1 text-[13.5px] text-teks-tegas placeholder:text-teks-samar focus:outline-none focus:ring-0';
const KELAS_SARAN = 'scrollbar-halus absolute left-0 right-0 z-30 mt-2 max-h-60 overflow-auto rounded-2xl bg-permukaan p-1.5 shadow-lg shadow-sungai-950/10 ring-1 ring-garis';
const KELAS_BARIS = 'flex w-full cursor-pointer items-center gap-2 rounded-xl px-3 py-2.5 text-left text-[13.5px] text-teks-sedang transition hover:bg-permukaan-lembut';
const KELAS_SOROT = 'bg-permukaan-lembut';
const KELAS_TANDA = 'rounded px-1 py-px text-[10px] font-bold uppercase tracking-wide text-peringatan-teks ring-1 ring-peringatan-teks/40';

const rapikan = (teks) => String(teks).trim().replace(/\s+/gu, ' ');

/** Dua nilai dianggap sama bila cuma beda huruf besar-kecil atau spasi. */
const samakan = (teks) => rapikan(teks).toLowerCase();

function ikon(d, kelas, tebal = '2.4') {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('class', kelas);
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', tebal);
    svg.setAttribute('viewBox', '0 0 24 24');

    const jalur = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    jalur.setAttribute('stroke-linecap', 'round');
    jalur.setAttribute('d', d);
    svg.append(jalur);

    return svg;
}

function pasangChip(kolom) {
    let pilihan = [];

    try {
        pilihan = JSON.parse(kolom.dataset.chipPilihan || '[]');
    } catch (e) {
        // Atribut rusak bukan alasan mematikan formulir; sarannya saja yang hilang.
    }

    const bolehBaru = kolom.dataset.chipBaru !== undefined;
    const kalimatPeringatan = kolom.dataset.chipPeringatan || '';
    const perNilai = new Map(pilihan.map((p) => [samakan(p.nilai), p]));

    const labelUntuk = (nilai) => perNilai.get(samakan(nilai))?.label ?? nilai;

    // Sumber kebenarannya daftar ini, bukan DOM. Nilai kolom tersembunyi
    // selalu diturunkan dari sini, tidak pernah sebaliknya.
    let terpilih = kolom.value.split(',').map(rapikan).filter(Boolean);

    // Keadaan awal disimpan supaya bisa dibandingkan: peringatan hanya muncul
    // untuk yang tadinya ada lalu dicopot, bukan untuk yang memang belum ada.
    const semula = [...terpilih];

    const wadah = document.createElement('div');
    wadah.className = 'relative';

    const kotak = document.createElement('div');
    kotak.className = KELAS_KOTAK;

    const ketik = document.createElement('input');
    ketik.type = 'text';
    ketik.className = KELAS_KETIK;
    ketik.placeholder = kolom.dataset.chipPetunjuk || 'Ketik untuk mencari…';
    ketik.autocomplete = 'off';
    ketik.setAttribute('aria-label', ketik.placeholder);

    const saran = document.createElement('div');
    saran.className = KELAS_SARAN;
    saran.hidden = true;

    const peringatan = document.createElement('p');
    peringatan.className = 'mt-2 rounded-xl bg-peringatan-latar px-3.5 py-2.5 text-[12.5px] leading-relaxed text-peringatan-teks-pekat';
    peringatan.hidden = true;

    const simpan = () => {
        kolom.value = terpilih.join(', ');
    };

    const tutupSaran = () => {
        saran.hidden = true;
        saran.replaceChildren();
    };

    const gambarPeringatan = () => {
        if (kalimatPeringatan === '') return;

        const dicopot = semula.filter((n) => ! terpilih.some((t) => samakan(t) === samakan(n)));

        peringatan.hidden = dicopot.length === 0;
        peringatan.textContent = kalimatPeringatan.replace(
            '{nama}',
            dicopot.map(labelUntuk).join(', ')
        );
    };

    const gambarChip = () => {
        kotak.querySelectorAll('[data-chip-item]').forEach((c) => c.remove());

        terpilih.forEach((nilai, urutan) => {
            const chip = document.createElement('span');
            chip.dataset.chipItem = '';
            chip.className = KELAS_CHIP;
            chip.append(labelUntuk(nilai));

            // Nilai yang belum ada di sistem ditandai SEBELUM disimpan, supaya
            // "aku baru saja membuat data" tidak pernah jadi kejutan.
            if (bolehBaru && ! perNilai.has(samakan(nilai))) {
                const tanda = document.createElement('span');
                tanda.className = KELAS_TANDA;
                tanda.textContent = 'baru';
                chip.append(tanda);
            }

            const buang = document.createElement('button');
            buang.type = 'button';
            buang.className = 'flex h-5 w-5 items-center justify-center rounded text-teks-samar transition hover:bg-bahaya-latar hover:text-bahaya-teks';
            buang.setAttribute('aria-label', `Hapus ${labelUntuk(nilai)}`);
            buang.append(ikon('M6 6l12 12M18 6L6 18', 'h-3 w-3', '2.5'));
            buang.addEventListener('click', () => {
                terpilih.splice(urutan, 1);
                simpan();
                gambarChip();
                ketik.focus();
            });

            chip.append(buang);
            kotak.insertBefore(chip, ketik);
        });

        gambarPeringatan();
    };

    const tambah = (nilai) => {
        const bersih = rapikan(nilai);

        if (bersih !== '' && ! terpilih.some((n) => samakan(n) === samakan(bersih))) {
            terpilih.push(bersih);
            simpan();
            gambarChip();
        }

        ketik.value = '';
        tutupSaran();
    };

    const buatBaris = (nilai, sorot) => {
        const baris = document.createElement('button');
        baris.type = 'button';
        baris.className = sorot ? `${KELAS_BARIS} ${KELAS_SOROT}` : KELAS_BARIS;
        baris.dataset.pilih = nilai;
        return baris;
    };

    const gambarSaran = () => {
        const dicari = samakan(ketik.value);
        saran.replaceChildren();

        pilihan
            .filter((p) => dicari === '' || samakan(p.label).includes(dicari))
            .filter((p) => ! terpilih.some((t) => samakan(t) === samakan(p.nilai)))
            .forEach((p, urutan) => {
                // Baris pertama disorot supaya Enter langsung memilih yang
                // sudah ada — jalan tercepat justru menuju data yang benar.
                //
                // Kecuali saat kolomnya masih kosong: di situ panelnya terbuka
                // karena diklik, bukan karena dicari, dan menyorot baris teratas
                // berarti Enter yang tidak disengaja memilih nama pertama
                // menurut abjad.
                const baris = buatBaris(p.nilai, dicari !== '' && urutan === 0);

                const label = document.createElement('span');
                label.textContent = p.label;
                baris.append(label);

                // Keterangan seperti "kini di DAS Kusan": akibat memilihnya
                // harus terbaca sebelum diklik, bukan sesudah tersimpan.
                if (p.catatan) {
                    const catatan = document.createElement('span');
                    catatan.className = 'ml-auto shrink-0 pl-3 text-[12px] text-teks-samar';
                    catatan.textContent = p.catatan;
                    baris.append(catatan);
                }

                saran.append(baris);
            });

        if (bolehBaru && dicari !== '') {
            const persis = perNilai.has(dicari);
            const sudahDipakai = terpilih.some((t) => samakan(t) === dicari);

            if (! persis && ! sudahDipakai) {
                // Sengaja TIDAK disorot walaupun ia satu-satunya baris. Enter
                // tidak boleh melahirkan data; barisnya harus dituju dengan
                // panah atau diklik, supaya salah ketik berhenti sebagai
                // "tidak ketemu".
                const baru = buatBaris(rapikan(ketik.value), false);
                baru.append(ikon('M12 5v14M5 12h14', 'h-4 w-4 shrink-0 text-peringatan-teks'));

                const label = document.createElement('span');
                label.append(`${kolom.dataset.chipBaru || 'Tambah baru'}: `);
                const tebal = document.createElement('strong');
                tebal.className = 'font-bold text-teks-tegas';
                tebal.textContent = rapikan(ketik.value);
                label.append(tebal);

                baru.append(label);
                saran.append(baru);
            }
        }

        if (saran.childElementCount === 0) {
            // Tiga sebab kosong yang berbeda, dan menyamakannya jadi "tidak
            // ada yang cocok" akan menyesatkan: yang satu berarti salah ketik,
            // yang lain berarti sudah selesai, yang lain lagi berarti datanya
            // memang belum pernah dibuat.
            const kosong = document.createElement('p');
            kosong.className = 'px-3 py-2.5 text-[13px] text-teks-samar';

            if (dicari !== '') {
                kosong.textContent = 'Tidak ada yang cocok.';
            } else if (pilihan.length === 0) {
                kosong.textContent = kolom.dataset.chipKosong || 'Belum ada yang terdaftar.';
            } else {
                kosong.textContent = 'Semuanya sudah dipilih.';
            }

            saran.append(kosong);
        }

        saran.hidden = false;
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

    // Panel terbuka begitu kolomnya disentuh, tanpa perlu mengetik apa pun.
    // Tanpa ini isi daftarnya harus ditebak lebih dulu — pengguna tidak bisa
    // tahu siapa saja yang terdaftar sebelum kebetulan mengetik huruf yang
    // benar.
    ketik.addEventListener('focus', gambarSaran);

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
    wadah.after(peringatan);

    // Daftar ejaan hanya berguna pada bentuk dasar; sarannya kini muncul
    // sambil mengetik.
    kolom.parentElement?.querySelector('[data-daftar-ejaan]')?.remove();

    gambarChip();
    simpan();
}

document.querySelectorAll('[data-chip]').forEach(pasangChip);
