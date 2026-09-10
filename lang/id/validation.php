<?php

/*
|--------------------------------------------------------------------------
| Pesan Kesalahan Validasi
|--------------------------------------------------------------------------
|
| Seluruh sistem ini dipakai pegawai Dinas PUPR, dan tidak seorang pun di
| antara mereka wajib memahami bahasa Inggris untuk mengisi formulir. Sebelum
| berkas ini ada, kesalahan pengisian muncul sebagai kalimat semacam
| "The kode DAS has already been taken." — separuh Indonesia, separuh Inggris,
| dan justru bagian yang menjelaskan sebabnya yang berbahasa asing.
|
| Nada seluruh kalimat di sini formal, sebagaimana lazimnya perangkat
| pemerintahan. Nama kolom diterjemahkan pada bagian 'attributes' di bawah,
| sehingga tidak ada lagi nama kolom basis data yang bocor ke layar pengguna.
|
*/

return [

    'accepted' => 'Kolom :attribute harus disetujui.',
    'accepted_if' => 'Kolom :attribute harus disetujui apabila :other bernilai :value.',
    'active_url' => 'Kolom :attribute bukan alamat URL yang sah.',
    'after' => 'Kolom :attribute harus berisi tanggal sesudah :date.',
    'after_or_equal' => 'Kolom :attribute harus berisi tanggal sesudah atau sama dengan :date.',
    'alpha' => 'Kolom :attribute hanya boleh berisi huruf.',
    'alpha_dash' => 'Kolom :attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => 'Kolom :attribute hanya boleh berisi huruf dan angka.',
    'any_of' => 'Isi kolom :attribute tidak sah.',
    'array' => 'Kolom :attribute harus berupa larik.',
    'ascii' => 'Kolom :attribute hanya boleh berisi karakter dan lambang satu bita.',
    'before' => 'Kolom :attribute harus berisi tanggal sebelum :date.',
    'before_or_equal' => 'Kolom :attribute harus berisi tanggal sebelum atau sama dengan :date.',

    'between' => [
        'array' => 'Kolom :attribute harus berisi antara :min sampai :max butir.',
        'file' => 'Berkas :attribute harus berukuran antara :min sampai :max kilobita.',
        'numeric' => 'Kolom :attribute harus bernilai antara :min sampai :max.',
        'string' => 'Kolom :attribute harus terdiri atas :min sampai :max karakter.',
    ],

    'boolean' => 'Kolom :attribute hanya boleh bernilai benar atau salah.',
    'can' => 'Kolom :attribute memuat nilai yang tidak diizinkan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'contains' => 'Kolom :attribute tidak memuat nilai yang diperlukan.',
    'current_password' => 'Kata sandi tidak sesuai.',
    'date' => 'Kolom :attribute bukan tanggal yang sah.',
    'date_equals' => 'Kolom :attribute harus berisi tanggal yang sama dengan :date.',
    'date_format' => 'Kolom :attribute tidak sesuai dengan format :format.',
    'decimal' => 'Kolom :attribute harus memiliki :decimal angka di belakang koma.',
    'declined' => 'Kolom :attribute harus ditolak.',
    'declined_if' => 'Kolom :attribute harus ditolak apabila :other bernilai :value.',
    'different' => 'Kolom :attribute dan :other harus berbeda.',
    'digits' => 'Kolom :attribute harus terdiri atas :digits angka.',
    'digits_between' => 'Kolom :attribute harus terdiri atas :min sampai :max angka.',
    'dimensions' => 'Ukuran gambar pada kolom :attribute tidak sesuai.',
    'distinct' => 'Kolom :attribute memuat nilai yang berulang.',
    'doesnt_contain' => 'Kolom :attribute tidak boleh memuat satu pun dari: :values.',
    'doesnt_end_with' => 'Kolom :attribute tidak boleh diakhiri salah satu dari: :values.',
    'doesnt_start_with' => 'Kolom :attribute tidak boleh diawali salah satu dari: :values.',
    'email' => 'Kolom :attribute harus berupa alamat surel yang sah.',
    'encoding' => 'Kolom :attribute harus memakai pengodean :encoding.',
    'ends_with' => 'Kolom :attribute harus diakhiri salah satu dari: :values.',
    'enum' => 'Pilihan pada kolom :attribute tidak sah.',
    'exists' => 'Pilihan pada kolom :attribute tidak sah.',
    'extensions' => 'Kolom :attribute harus berupa berkas berekstensi: :values.',
    'file' => 'Kolom :attribute harus berupa berkas.',
    'filled' => 'Kolom :attribute wajib diisi.',

    'gt' => [
        'array' => 'Kolom :attribute harus berisi lebih dari :value butir.',
        'file' => 'Berkas :attribute harus berukuran lebih dari :value kilobita.',
        'numeric' => 'Kolom :attribute harus bernilai lebih dari :value.',
        'string' => 'Kolom :attribute harus terdiri atas lebih dari :value karakter.',
    ],

    'gte' => [
        'array' => 'Kolom :attribute harus berisi :value butir atau lebih.',
        'file' => 'Berkas :attribute harus berukuran :value kilobita atau lebih.',
        'numeric' => 'Kolom :attribute harus bernilai :value atau lebih.',
        'string' => 'Kolom :attribute harus terdiri atas :value karakter atau lebih.',
    ],

    'hex_color' => 'Kolom :attribute harus berupa kode warna heksadesimal yang sah.',
    'image' => 'Kolom :attribute harus berupa gambar.',
    'in' => 'Pilihan pada kolom :attribute tidak sah.',
    'in_array' => 'Kolom :attribute tidak terdapat di dalam :other.',
    'in_array_keys' => 'Kolom :attribute harus memuat sekurang-kurangnya satu dari kunci berikut: :values.',
    'integer' => 'Kolom :attribute harus berupa bilangan bulat.',
    'ip' => 'Kolom :attribute harus berupa alamat IP yang sah.',
    'ipv4' => 'Kolom :attribute harus berupa alamat IPv4 yang sah.',
    'ipv6' => 'Kolom :attribute harus berupa alamat IPv6 yang sah.',
    'json' => 'Kolom :attribute harus berupa teks JSON yang sah.',
    'list' => 'Kolom :attribute harus berupa daftar.',
    'lowercase' => 'Kolom :attribute harus ditulis dengan huruf kecil.',

    'lt' => [
        'array' => 'Kolom :attribute harus berisi kurang dari :value butir.',
        'file' => 'Berkas :attribute harus berukuran kurang dari :value kilobita.',
        'numeric' => 'Kolom :attribute harus bernilai kurang dari :value.',
        'string' => 'Kolom :attribute harus terdiri atas kurang dari :value karakter.',
    ],

    'lte' => [
        'array' => 'Kolom :attribute tidak boleh berisi lebih dari :value butir.',
        'file' => 'Berkas :attribute harus berukuran :value kilobita atau kurang.',
        'numeric' => 'Kolom :attribute harus bernilai :value atau kurang.',
        'string' => 'Kolom :attribute harus terdiri atas :value karakter atau kurang.',
    ],

    'mac_address' => 'Kolom :attribute harus berupa alamat MAC yang sah.',

    'max' => [
        'array' => 'Kolom :attribute tidak boleh berisi lebih dari :max butir.',
        'file' => 'Berkas :attribute tidak boleh lebih besar dari :max kilobita.',
        'numeric' => 'Kolom :attribute tidak boleh bernilai lebih dari :max.',
        'string' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
    ],

    'max_digits' => 'Kolom :attribute tidak boleh terdiri atas lebih dari :max angka.',
    'mimes' => 'Kolom :attribute harus berupa berkas berjenis: :values.',
    'mimetypes' => 'Kolom :attribute harus berupa berkas berjenis: :values.',

    'min' => [
        'array' => 'Kolom :attribute harus berisi sekurang-kurangnya :min butir.',
        'file' => 'Berkas :attribute harus berukuran sekurang-kurangnya :min kilobita.',
        'numeric' => 'Kolom :attribute harus bernilai sekurang-kurangnya :min.',
        'string' => 'Kolom :attribute harus terdiri atas sekurang-kurangnya :min karakter.',
    ],

    'min_digits' => 'Kolom :attribute harus terdiri atas sekurang-kurangnya :min angka.',
    'missing' => 'Kolom :attribute tidak boleh disertakan.',
    'missing_if' => 'Kolom :attribute tidak boleh disertakan apabila :other bernilai :value.',
    'missing_unless' => 'Kolom :attribute tidak boleh disertakan kecuali :other bernilai :value.',
    'missing_with' => 'Kolom :attribute tidak boleh disertakan apabila :values ada.',
    'missing_with_all' => 'Kolom :attribute tidak boleh disertakan apabila seluruh :values ada.',
    'multiple_of' => 'Kolom :attribute harus merupakan kelipatan :value.',
    'not_in' => 'Pilihan pada kolom :attribute tidak sah.',
    'not_regex' => 'Format kolom :attribute tidak sah.',
    'numeric' => 'Kolom :attribute harus berupa angka.',

    'password' => [
        'letters' => 'Kata sandi harus memuat sekurang-kurangnya satu huruf.',
        'mixed' => 'Kata sandi harus memuat huruf besar dan huruf kecil.',
        'numbers' => 'Kata sandi harus memuat sekurang-kurangnya satu angka.',
        'symbols' => 'Kata sandi harus memuat sekurang-kurangnya satu lambang.',
        'uncompromised' => 'Kata sandi ini pernah muncul pada kebocoran data. Silakan pilih kata sandi lain.',
    ],

    'present' => 'Kolom :attribute harus disertakan.',
    'present_if' => 'Kolom :attribute harus disertakan apabila :other bernilai :value.',
    'present_unless' => 'Kolom :attribute harus disertakan kecuali :other bernilai :value.',
    'present_with' => 'Kolom :attribute harus disertakan apabila :values ada.',
    'present_with_all' => 'Kolom :attribute harus disertakan apabila seluruh :values ada.',
    'prohibited' => 'Kolom :attribute tidak boleh diisi.',
    'prohibited_if' => 'Kolom :attribute tidak boleh diisi apabila :other bernilai :value.',
    'prohibited_if_accepted' => 'Kolom :attribute tidak boleh diisi apabila :other disetujui.',
    'prohibited_if_declined' => 'Kolom :attribute tidak boleh diisi apabila :other ditolak.',
    'prohibited_unless' => 'Kolom :attribute tidak boleh diisi kecuali :other termasuk :values.',
    'prohibits' => 'Kolom :attribute menyebabkan :other tidak boleh diisi.',
    'regex' => 'Format kolom :attribute tidak sah.',
    'required' => 'Kolom :attribute wajib diisi.',
    'required_array_keys' => 'Kolom :attribute harus memuat kunci: :values.',
    'required_if' => 'Kolom :attribute wajib diisi apabila :other bernilai :value.',
    'required_if_accepted' => 'Kolom :attribute wajib diisi apabila :other disetujui.',
    'required_if_declined' => 'Kolom :attribute wajib diisi apabila :other ditolak.',
    'required_unless' => 'Kolom :attribute wajib diisi kecuali :other termasuk :values.',
    'required_with' => 'Kolom :attribute wajib diisi apabila :values ada.',
    'required_with_all' => 'Kolom :attribute wajib diisi apabila seluruh :values ada.',
    'required_without' => 'Kolom :attribute wajib diisi apabila :values tidak ada.',
    'required_without_all' => 'Kolom :attribute wajib diisi apabila tidak satu pun dari :values ada.',
    'same' => 'Kolom :attribute dan :other harus sama.',

    'size' => [
        'array' => 'Kolom :attribute harus berisi :size butir.',
        'file' => 'Berkas :attribute harus berukuran :size kilobita.',
        'numeric' => 'Kolom :attribute harus bernilai :size.',
        'string' => 'Kolom :attribute harus terdiri atas :size karakter.',
    ],

    'starts_with' => 'Kolom :attribute harus diawali salah satu dari: :values.',
    'string' => 'Kolom :attribute harus berupa teks.',
    'timezone' => 'Kolom :attribute harus berupa zona waktu yang sah.',
    'ulid' => 'Kolom :attribute harus berupa ULID yang sah.',
    'unique' => ':attribute sudah digunakan.',
    'uploaded' => 'Berkas :attribute gagal diunggah.',
    'uppercase' => 'Kolom :attribute harus ditulis dengan huruf besar.',
    'url' => 'Kolom :attribute harus berupa alamat URL yang sah.',
    'uuid' => 'Kolom :attribute harus berupa UUID yang sah.',

    /*
    |--------------------------------------------------------------------------
    | Pesan Khusus per Kolom
    |--------------------------------------------------------------------------
    |
    | Dipakai bila kalimat umum di atas terasa kaku pada kolom tertentu, atau
    | bila pengguna memerlukan petunjuk yang lebih tegas daripada sekadar
    | pernyataan bahwa isiannya keliru.
    |
    */

    'custom' => [
        'password' => [
            'min' => 'Kata sandi harus terdiri atas sekurang-kurangnya :min karakter.',
        ],
        'berkas' => [
            'mimes' => 'Berkas harus berupa .zip yang memuat shapefile, atau berkas .geojson.',
            'max' => 'Ukuran berkas tidak boleh lebih dari 50 MB.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nama Kolom
    |--------------------------------------------------------------------------
    |
    | Tanpa daftar ini, nama kolom basis data muncul apa adanya di layar —
    | "kode_das", "wilayah_sungai_id" — dan pegawai yang membacanya tidak
    | punya cara menghubungkannya dengan kotak isian mana yang keliru.
    |
    */

    'attributes' => [
        'nama' => 'Nama',
        'nama_das' => 'Nama DAS',
        'kode_das' => 'Kode DAS',
        'nama_sungai' => 'Nama wilayah sungai',
        'kode_sungai' => 'Kode wilayah sungai',
        'wilayah_sungai_id' => 'Wilayah sungai',
        'das_id' => 'DAS',
        'juru_ids' => 'Juru yang bertugas',
        'kabupaten_ids' => 'Kabupaten',
        'kabupaten_nama' => 'Kabupaten',
        'deskripsi' => 'Keterangan',
        'koordinat_lat' => 'Lintang',
        'koordinat_lng' => 'Bujur',
        'no_telepon' => 'Nomor telepon',
        'nomor_sk' => 'Nomor SK',
        'username' => 'Nama pengguna',
        'password' => 'Kata sandi',
        'role' => 'Peran',
        'aktif' => 'Status aktif',
        'berkas' => 'Berkas',
        'file' => 'Berkas',
        'jenis' => 'Jenis',
        'versi_aplikasi' => 'Versi aplikasi',
        'pesan_terakhir' => 'Pesan terakhir',
        'jumlah_tertunda' => 'Jumlah tertunda',
        'jumlah_gagal' => 'Jumlah gagal',
        'jumlah_terkirim' => 'Jumlah terkirim',
    ],

];
