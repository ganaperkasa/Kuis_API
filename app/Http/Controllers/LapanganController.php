<?php

namespace App\Http\Controllers;

use App\Models\Lapangan;
use Illuminate\Http\Request;

class LapanganController extends Controller
{
    // Melihat daftar lapangan
    public function index()
    {
        return response()->json(Lapangan::all(), 200);
    }

    // Menambah lapangan
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga_per_jam' => 'required|numeric|min:0',
            'tersedia' => 'required|boolean'
        ]);

        $lapangan = Lapangan::create($request->all());

        return response()->json([
            'message' => 'Lapangan berhasil ditambahkan',
            'data' => $lapangan
        ], 201);
    }

    // Mengupdate lapangan
    public function update(Request $request, $id)
    {
        $lapangan = Lapangan::find($id);

        // Jika lapangan tidak ditemukan, kembalikan error 404
        if (!$lapangan) {
            return response()->json(['message' => 'Lapangan tidak ditemukan'], 404);
        }

        $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga_per_jam' => 'sometimes|required|numeric|min:0',
            'tersedia' => 'required|boolean'
        ]);

        $lapangan->update($request->only(['nama', 'deskripsi', 'harga_per_jam', 'tersedia']));

        return response()->json([
            'message' => 'Lapangan berhasil diperbarui',
            'data' => $lapangan
        ], 200);
    }

    // Menghapus lapangan
    public function destroy($id)
    {
        $lapangan = Lapangan::find($id);

        if (!$lapangan) {
            return response()->json(['message' => 'Lapangan tidak ditemukan'], 404);
        }

        $lapangan->delete();

        return response()->json(['message' => 'Lapangan berhasil dihapus'], 200);
    }
}
