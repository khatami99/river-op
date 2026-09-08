<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Das;
use App\Models\JuruSungai;
use App\Models\LaporanOp;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JuruSungaiWebController extends Controller
{
    public function index(Request $request)
    {
        $juru = JuruSungai::with('das')
            ->withCount('laporanOp')
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = $request->string('cari')->toString();
                $q->where(fn ($sub) => $sub->where('nama', 'like', "%{$cari}%")
                    ->orWhere('no_telepon', 'like', "%{$cari}%")
                    ->orWhere('nomor_sk', 'like', "%{$cari}%")
                    ->orWhere('username', 'like', "%{$cari}%"));
            })
            ->when($request->filled('das_id'), fn ($q) => $q->where('das_id', $request->integer('das_id')))
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        $data = [
            'juru' => $juru,
            'daftarDas' => Das::orderBy('nama_das')->get(),
            'filter' => [
                'cari' => $request->input('cari'),
                'das_id' => $request->input('das_id'),
            ],
        ];

        // Permintaan potongan datang dari penyaring yang berubah di layar.
        // Hanya area hasilnya yang dikirim ulang, sehingga kursor pengguna
        // tidak lepas dari kotak pencarian dan halaman tidak berkedip.
        if ($request->boolean('fragmen')) {
            return response()->json([
                'hasil' => view('juru._hasil', $data)->render(),
                'jumlah' => $data['juru']->total() . ' juru terdaftar',
            ]);
        }

        return view('juru.index', $data);
    }

    public function show(Request $request, JuruSungai $juru)
    {
        $juru->load('das.wilayahSungai', 'das.kabupaten');

        $tahun = (int) $request->input('tahun', now()->year);

        $laporan = LaporanOp::where('juru_sungai_id', $juru->id)
            ->with('das')
            ->withCount('itemInspeksi')
            ->orderByDesc('tanggal_inspeksi')
            ->paginate(10)
            ->withQueryString();

        // Bulan mana saja yang sudah dilaporkan pada tahun terpilih.
        $bulanTerlapor = LaporanOp::where('juru_sungai_id', $juru->id)
            ->where('tahun', $tahun)
            ->distinct()
            ->pluck('bulan')
            ->all();

        return view('juru.show', [
            'juru' => $juru,
            'laporan' => $laporan,
            'tahun' => $tahun,
            'bulanTerlapor' => $bulanTerlapor,
            'daftarTahun' => $this->daftarTahun(),
        ]);
    }

    public function create(Request $request)
    {
        // Bila dibuka dari halaman DAS, DAS-nya langsung terpilih dan
        // setelah disimpan admin dikembalikan ke halaman DAS tersebut.
        $juru = new JuruSungai(['aktif' => true]);
        $juru->das_id = $request->integer('das_id') ?: null;

        return view('juru.form', [
            'juru' => $juru,
            'daftarDas' => Das::where('aktif', true)->orderBy('nama_das')->get(),
            'kembaliKeDas' => $request->integer('das_id') ?: null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['aktif'] = $request->boolean('aktif');

        $juru = JuruSungai::create($data);

        $tujuan = $request->filled('kembali_ke_das')
            ? route('das.show', $request->integer('kembali_ke_das'))
            : route('juru.index');

        return redirect()->to($tujuan)->with('sukses', "Juru sungai {$juru->nama} berhasil ditambahkan.");
    }

    public function edit(JuruSungai $juru)
    {
        return view('juru.form', [
            'juru' => $juru,
            'daftarDas' => Das::where('aktif', true)->orderBy('nama_das')->get(),
            'kembaliKeDas' => null,
        ]);
    }

    public function update(Request $request, JuruSungai $juru)
    {
        $data = $this->validasi($request, $juru);
        $data['aktif'] = $request->boolean('aktif');

        // Password hanya diganti bila diisi, supaya mengubah data lain
        // tidak memaksa admin mengetik ulang password juru.
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $juru->update($data);

        return redirect()->route('juru.index')->with('sukses', 'Data juru sungai diperbarui.');
    }

    public function destroy(JuruSungai $juru)
    {
        if ($juru->laporanOp()->exists()) {
            return back()->withErrors([
                'hapus' => 'Juru ini sudah memiliki laporan sehingga tidak dapat dihapus. Nonaktifkan saja agar riwayatnya tetap utuh.',
            ]);
        }

        $juru->delete();

        return redirect()->route('juru.index')->with('sukses', 'Juru sungai dihapus.');
    }

    private function validasi(Request $request, ?JuruSungai $juru = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'no_telepon' => ['nullable', 'string', 'max:25'],
            'nomor_sk' => ['nullable', 'string', 'max:100'],
            'username' => [
                'required', 'string', 'max:50',
                Rule::unique('juru_sungai', 'username')->ignore($juru?->id)->whereNull('deleted_at'),
            ],
            'password' => [$juru ? 'nullable' : 'required', 'string', 'min:6', 'max:255'],
            'das_id' => ['required', 'exists:das,id'],
        ], [], [
            'no_telepon' => 'nomor telepon',
            'nomor_sk' => 'nomor SK',
            'das_id' => 'DAS',
        ]);
    }

    private function daftarTahun(): array
    {
        $paling_awal = (int) (LaporanOp::min('tahun') ?: now()->year);
        $sekarang = now()->year;

        return range($sekarang, min($paling_awal, $sekarang));
    }
}
