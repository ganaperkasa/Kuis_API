<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    // GET /admin/users
    public function index()
    {
        $users = User::where('role', '!=', 'admin')->get();
        return response()->json($users);
    }

    // GET /admin/users/{id}
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    // PUT /admin/users/{id}
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'string',
            'email' => 'email|unique:users,email,' . $id,
            'phone' => 'string',
            'role' => 'in:user,admin',
        ]);

        $user = User::findOrFail($id);
        $user->update($request->only('name', 'email', 'phone', 'role'));

        return response()->json(['message' => 'User updated successfully.']);
    }

    // DELETE /admin/users/{id}
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }
}
