<?php

namespace App\Http\Controllers;

use App\Models\JuruSungai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $juruSungai = JuruSungai::where('username', $request->username)
            ->where('aktif', true)
            ->first();

        if (!$juruSungai || !Hash::check($request->password, $juruSungai->password)) {
            return response()->json([
                'message' => 'Username atau password salah.'
            ], 401);
        }

        $token = $juruSungai->createToken('juru_sungai_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => [
                'id' => $juruSungai->id,
                'nama' => $juruSungai->nama,
                'nip' => $juruSungai->nip,
                'username' => $juruSungai->username,
                'wilayah_sungai_id' => $juruSungai->wilayah_sungai_id,
            ]
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.'
        ]);
    }
}
