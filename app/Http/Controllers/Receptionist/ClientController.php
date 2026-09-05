<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\Pet;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Owner::with('pets')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('client_code', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('pets', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('pet_code', 'like', "%{$search}%")
                         ->orWhere('breed', 'like', "%{$search}%");
                  });
            });
        }

        $clients = $query->paginate(15)->withQueryString();
        $totalClients = Owner::count();
        $totalPets = Pet::count();

        return view('receptionist.clients.index', compact('clients', 'totalClients', 'totalPets', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'contact_number' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'email' => 'nullable|email|max:150',
        ]);

        $validated['client_code'] = Owner::generateClientCode();
        $validated['status'] = 'active';

        $owner = Owner::create($validated);

        return redirect()->back()->with('success', "New client {$owner->full_name} ({$owner->client_code}) registered successfully!");
    }

    public function storePet(Request $request)
    {
        $validated = $request->validate([
            'owner_id' => 'required|exists:owners,id',
            'name' => 'required|string|max:100',
            'species' => 'required|string|max:50',
            'breed' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'age' => 'nullable|string|max:50',
            'sex' => 'nullable|string|max:20',
            'color' => 'nullable|string|max:100',
        ]);

        $validated['pet_code'] = Pet::generatePetCode();

        $pet = Pet::create($validated);

        return redirect()->back()->with('success', "New pet {$pet->name} ({$pet->pet_code}) registered for {$pet->owner->full_name}!");
    }
}
