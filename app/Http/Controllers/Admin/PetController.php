<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\Owner;
use Illuminate\Http\Request;

class PetController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_id' => 'required|exists:owners,id',
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:100',
            'breed' => 'required|string|max:100',
            'age' => 'required|string|max:50',
            'sex' => 'required|string|max:50',
            'color' => 'nullable|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $petCode = Pet::generatePetCode();
        $photoPath = null;

        // Store directly in public/uploads/pets (not storage symlink)
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'pet_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pets'), $filename);
            $photoPath = 'uploads/pets/' . $filename;
        }

        $pet = Pet::create([
            'pet_code' => $petCode,
            'owner_id' => $validated['owner_id'],
            'name' => $validated['name'],
            'species' => $validated['species'],
            'breed' => $validated['breed'],
            'age' => $validated['age'],
            'sex' => $validated['sex'],
            'color' => $validated['color'] ?? null,
            'photo' => $photoPath,
        ]);

        return redirect()->back()->with('success', "Pet {$pet->name} registered with Key Code: {$petCode}!");
    }

    public function update(Request $request, Pet $pet)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:100',
            'breed' => 'required|string|max:100',
            'age' => 'required|string|max:50',
            'sex' => 'required|string|max:50',
            'color' => 'nullable|string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old file from public/ if exists
            if ($pet->photo && file_exists(public_path($pet->photo))) {
                @unlink(public_path($pet->photo));
            }

            $file = $request->file('photo');
            $filename = 'pet_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pets'), $filename);
            $validated['photo'] = 'uploads/pets/' . $filename;
        }

        $pet->update($validated);

        return redirect()->back()->with('success', "Pet {$pet->pet_code} ({$pet->name}) updated successfully!");
    }

    public function destroy(Pet $pet)
    {
        $name = $pet->name;
        $code = $pet->pet_code;

        if ($pet->photo && file_exists(public_path($pet->photo))) {
            @unlink(public_path($pet->photo));
        }

        $pet->delete();

        return redirect()->back()->with('success', "Pet {$name} ({$code}) removed successfully.");
    }
}
