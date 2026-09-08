<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StafDinas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StafDinasWebController extends Controller
{
    public function index(Request $request)
    {
        $staf = StafDinas::query()
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = $request->string('cari')->toString();
                $q->where(fn ($sub) => $sub->where('nama', 'like', "%{$cari}%")
                    ->orWhere('username', 'like', "%{$cari}%"));
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('staf.index', [
            'staf' => $staf,
            'filter' => ['cari' => $request->input('cari')],
        ]);
    }

    public function create()
    {
        return view('staf.form', ['staf' => new StafDinas(['aktif' => true, 'role' => 'viewer'])]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $data['aktif'] = $request->boolean('aktif');

        StafDinas::create($data);

        return redirect()->route('staf.index')->with('sukses', 'Akun staf berhasil ditambahkan.');
    }

    public function edit(StafDinas $staf)
    {
        return view('staf.form', ['staf' => $staf]);
    }

    public function update(Request $request, StafDinas $staf)
    {
        $data = $this->validasi($request, $staf);
        $data['aktif'] = $request->boolean('aktif');

        // Admin tidak boleh menurunkan atau menonaktifkan dirinya sendiri,
        // karena bisa terkunci dari sistem tanpa cara masuk kembali.
        if ($staf->id === $request->user('staf')->id) {
            if ($data['role'] !== 'admin' || ! $data['aktif']) {
                return back()->withErrors([
                    'role' => 'Anda tidak dapat menurunkan peran atau menonaktifkan akun Anda sendiri.',
                ])->withInput();
            }
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $staf->update($data);

        return redirect()->route('staf.index')->with('sukses', 'Data staf diperbarui.');
    }

    public function destroy(Request $request, StafDinas $staf)
    {
        if ($staf->id === $request->user('staf')->id) {
            return back()->withErrors(['hapus' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        // Sistem harus selalu menyisakan minimal satu admin aktif.
        $adminAktifLain = StafDinas::where('role', 'admin')
            ->where('aktif', true)
            ->where('id', '!=', $staf->id)
            ->exists();

        if ($staf->isAdmin() && ! $adminAktifLain) {
            return back()->withErrors(['hapus' => 'Ini satu-satunya administrator aktif dan tidak dapat dihapus.']);
        }

        $staf->delete();

        return redirect()->route('staf.index')->with('sukses', 'Akun staf dihapus.');
    }

    private function validasi(Request $request, ?StafDinas $staf = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => [
                'required', 'string', 'max:50',
                Rule::unique('staf_dinas', 'username')->ignore($staf?->id)->whereNull('deleted_at'),
            ],
            'password' => [$staf ? 'nullable' : 'required', 'string', 'min:6', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'viewer'])],
        ], [], [
            'role' => 'peran',
        ]);
    }
}
