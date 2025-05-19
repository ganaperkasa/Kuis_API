<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Exception;

class AuthController extends Controller
{
    /**
     * Login User
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah',
            ], 401);
        }

        try {
            $user = Auth::user();

            if ($user instanceof User) {
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Login berhasil',
                    'data' => [
                        'token' => $token,
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'role' => $user->role,
                        ]
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Autentikasi gagal, user tidak ditemukan',
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat login',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout User
     */
    public function logout(Request $request)
    {
        try {
            if ($request->user() instanceof User) {
                $request->user()->tokens()->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Logout berhasil'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan atau belum login'
            ], 400);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat logout',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mendapatkan Data User yang Sedang Login
     */
public function me(Request $request)
{
    try {
        $user = $request->user();

        if ($user instanceof User) {
            $user->photo_url = $user->photo ? asset('storage/' . $user->photo) : null;

            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil diambil',
                'data' => $user,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'User tidak ditemukan',
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mendapatkan data user',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}
