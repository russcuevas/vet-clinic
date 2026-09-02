<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\Pet;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Owner::with('pets');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('client_code', 'LIKE', "%{$search}%")
                  ->orWhere('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('contact_number', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        $owners = $query->latest()->get();
        $generatedCode = Owner::generateClientCode();
        $generatedPetCode = Pet::generatePetCode();

        return view('admin.clients.index', compact('owners', 'generatedCode', 'generatedPetCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:50',
            'address' => 'required|string',
            'email' => 'nullable|email|max:255',
            // Pet details if adding pet simultaneously
            'pet_name' => 'nullable|string|max:255',
            'species' => 'nullable|string|max:100',
            'breed' => 'nullable|string|max:100',
            'age' => 'nullable|string|max:50',
            'sex' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:100',
        ]);

        $clientCode = Owner::generateClientCode();

        $owner = Owner::create([
            'client_code' => $clientCode,
            'full_name' => $validated['full_name'],
            'contact_number' => $validated['contact_number'],
            'address' => $validated['address'],
            'email' => $validated['email'] ?? null,
            'status' => 'active',
        ]);

        if (!empty($validated['pet_name'])) {
            $petCode = Pet::generatePetCode();
            Pet::create([
                'pet_code' => $petCode,
                'owner_id' => $owner->id,
                'name' => $validated['pet_name'],
                'species' => $validated['species'] ?? 'Dog',
                'breed' => $validated['breed'] ?? 'Mixed',
                'age' => $validated['age'] ?? 'Not specified',
                'sex' => $validated['sex'] ?? 'Male',
                'color' => $validated['color'] ?? null,
            ]);
        }

        return redirect()->back()->with('success', "Client {$owner->full_name} registered successfully with Key Code: {$clientCode}!");
    }

    public function update(Request $request, Owner $owner)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:50',
            'address' => 'required|string',
            'email' => 'nullable|email|max:255',
        ]);

        $owner->update($validated);

        return redirect()->back()->with('success', "Client {$owner->client_code} information updated successfully!");
    }

    public function destroy(Owner $owner)
    {
        $code = $owner->client_code;
        $name = $owner->full_name;
        $owner->delete();

        return redirect()->back()->with('success', "Client record {$name} ({$code}) deleted successfully.");
    }
}
