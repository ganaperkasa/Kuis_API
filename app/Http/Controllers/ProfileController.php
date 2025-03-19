<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Update user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6'
        ]);

        $user->name = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Upload profile photo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadPhoto(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Validasi file upload
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Hapus foto lama jika ada
        if ($user->photo) {
            Storage::delete('public/' . $user->photo);
        }

        // Simpan foto baru
        $filePath = $request->file('photo')->store('profile_photos', 'public');

        // Update user dengan path foto
        $user->update(['photo' => $filePath]);

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photo_url' => asset('storage/' . $filePath)
        ]);
    }
}
