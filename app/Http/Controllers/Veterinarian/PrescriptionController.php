<?php

namespace App\Http\Controllers\Veterinarian;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\MedicalRecord;
use App\Models\Owner;
use App\Models\Pet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = Prescription::with(['owner', 'pet', 'veterinarian', 'medicalRecord']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('prescription_code', 'LIKE', "%{$search}%")
                  ->orWhereHas('owner', fn($sub) => $sub->where('full_name', 'LIKE', "%{$search}%"))
                  ->orWhereHas('pet', fn($sub) => $sub->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $prescriptions = $query->latest()->get();
        $owners = Owner::with('pets')->where('status', 'active')->get();
        $generatedCode = Prescription::generatePrescriptionCode();

        return view('veterinarian.prescriptions.index', compact('prescriptions', 'owners', 'generatedCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'owner_id' => 'required|exists:owners,id',
            'pet_id' => 'required|exists:pets,id',
            'body_weight' => 'nullable|string|max:50',
            'rx_details' => 'required|string',
            'instructions' => 'nullable|string',
        ]);

        $code = Prescription::generatePrescriptionCode();
        $vet = auth()->user();

        $prescription = Prescription::create([
            'prescription_code' => $code,
            'medical_record_id' => $validated['medical_record_id'] ?? null,
            'owner_id' => $validated['owner_id'],
            'pet_id' => $validated['pet_id'],
            'veterinarian_id' => $vet->id,
            'veterinarian_name' => $vet->name,
            'license_no' => $vet->license_no ?? 'PRC-VET-009821',
            'body_weight' => $validated['body_weight'] ?? null,
            'rx_details' => $validated['rx_details'],
            'instructions' => $validated['instructions'] ?? null,
            'date_issued' => Carbon::now()->format('Y-m-d'),
        ]);

        // If created from inside a Medical Case File, return directly to that case!
        if (!empty($validated['medical_record_id'])) {
            return redirect()->route('vet.medical.show', $validated['medical_record_id'])
                ->with('success', "Prescription {$code} issued and attached to this medical case successfully!");
        }

        return redirect()->route('vet.prescriptions.print', $prescription->id)
            ->with('success', "Prescription {$code} generated successfully!");
    }

    public function update(Request $request, Prescription $prescription)
    {
        $validated = $request->validate([
            'rx_details' => 'required|string',
            'instructions' => 'nullable|string',
            'body_weight' => 'nullable|string|max:50',
        ]);

        $prescription->update($validated);

        return back()->with('success', "Prescription {$prescription->prescription_code} updated successfully!");
    }

    public function print(Prescription $prescription)
    {
        $prescription->load(['owner', 'pet', 'veterinarian', 'medicalRecord']);
        return view('veterinarian.prescriptions.print', compact('prescription'));
    }
}
