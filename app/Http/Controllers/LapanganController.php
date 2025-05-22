<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lapangan;
use Illuminate\Support\Facades\Storage;

class LapanganController extends Controller
{
    // ✅ 1. Get All Lapangan
    public function index()
    {
        return response()->json(Lapangan::all());
    }

    // ✅ 2. Create Lapangan
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer',
            'type' => 'required|in:Futsal,Badminton,Basket,Tennis,Voli',
            'status' => 'in:Available,Booked',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('lapangan_photos', 'public');
        }

        $lapangan = Lapangan::create($data);

        return response()->json(['message' => 'Lapangan created successfully', 'data' => $lapangan], 201);
    }

    // ✅ 3. Show Detail Lapangan
    public function show($id)
    {
        $lapangan = Lapangan::find($id);
        if (!$lapangan) {
            return response()->json(['message' => 'Lapangan not found'], 404);
        }
        return response()->json($lapangan);
    }

    // ✅ 4. Update Lapangan
    public function update(Request $request, $id)
    {
        $lapangan = Lapangan::find($id);
        if (!$lapangan) {
            return response()->json(['message' => 'Lapangan not found'], 404);
        }

        $request->validate([
            'name' => 'string|max:255',
            'price' => 'numeric',
            'location' => 'string|max:255',
            'capacity' => 'integer',
            'type' => 'in:Futsal,Badminton,Basket,Tennis,Voli',
            'status' => 'in:available,booked',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            if ($lapangan->photo) {
                Storage::delete('public/' . $lapangan->photo);
            }
            $data['photo'] = $request->file('photo')->store('lapangan_photos', 'public');
        }

        $lapangan->update($data);

        return response()->json(['message' => 'Lapangan updated successfully', 'data' => $lapangan]);
    }

    // ✅ 5. Delete Lapangan
    public function destroy($id)
    {
        $lapangan = Lapangan::find($id);
        if (!$lapangan) {
            return response()->json(['message' => 'Lapangan not found'], 404);
        }

        if ($lapangan->photo) {
            Storage::delete('public/' . $lapangan->photo);
        }

        $lapangan->delete();

        return response()->json(['message' => 'Lapangan deleted successfully']);
    }
}
