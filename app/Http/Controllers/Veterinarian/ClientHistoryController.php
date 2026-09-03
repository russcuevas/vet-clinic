<?php

namespace App\Http\Controllers\Veterinarian;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\Pet;
use Illuminate\Http\Request;

class ClientHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Owner::with([
            'pets.medicalRecords' => function ($q) {
                $q->orderBy('visit_date', 'desc')->orderBy('created_at', 'desc');
            },
            'pets.prescriptions',
            'medicalRecords' => function ($q) {
                $q->orderBy('visit_date', 'desc')->orderBy('created_at', 'desc');
            }
        ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('client_code', 'LIKE', "%{$search}%")
                  ->orWhere('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('contact_number', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhereHas('pets', function ($pq) use ($search) {
                      $pq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('pet_code', 'LIKE', "%{$search}%")
                         ->orWhere('breed', 'LIKE', "%{$search}%");
                  });
            });
        }

        $owners = $query->latest()->get();

        return view('veterinarian.clients.index', compact('owners'));
    }

    public function show(Owner $owner)
    {
        $owner->load([
            'pets.medicalRecords.veterinarian',
            'pets.medicalRecords.prescription',
            'pets.prescriptions',
            'medicalRecords.veterinarian',
            'medicalRecords.pet',
        ]);

        return view('veterinarian.clients.show', compact('owner'));
    }

    public function petHistory(Pet $pet)
    {
        $pet->load([
            'owner',
            'medicalRecords.veterinarian',
            'medicalRecords.prescription',
            'prescriptions'
        ]);

        return view('veterinarian.clients.pet_history', compact('pet'));
    }
}
