<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,cashier,veterinarian,manager,inventory_officer,back_office',
            'license_no' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:50',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'license_no' => $validated['license_no'] ?? null,
            'contact_number' => $validated['contact_number'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', "Staff user {$user->name} created successfully!");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,cashier,veterinarian,manager,inventory_officer,back_office',
            'license_no' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'license_no' => $validated['license_no'] ?? null,
            'contact_number' => $validated['contact_number'] ?? null,
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->back()->with('success', "Staff user {$user->name} updated successfully!");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', "You cannot delete your own account.");
        }

        $name = $user->name;
        $user->delete();

        return redirect()->back()->with('success', "User {$name} has been deleted.");
    }
}
