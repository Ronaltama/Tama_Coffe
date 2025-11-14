<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Username atau password salah'], 401);
        }

        /**
         * ♻️ PENTING!
         * Jangan hapus semua token user → bikin Postman & browser saling “tendang”.
         * Buat token baru per device berdasarkan userAgent.
         */
        $token = $user->createToken($request->userAgent())->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'role_id' => $user->role_id,
                'role' => $user->role_id === 'RL001' ? 'superadmin' : 'admin',
                'token' => $token,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        /**
         * Hapus hanya token device saat ini (Chrome atau Postman)
         * → ini yang benar untuk API Sanctum
         */
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
