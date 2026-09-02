<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\MedicalRecord;
use App\Models\Owner;
use App\Models\Pet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PrescriptionController extends Controller
{
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
        $user = auth()->user();

        $prescription = Prescription::create([
            'prescription_code' => $code,
            'medical_record_id' => $validated['medical_record_id'] ?? null,
            'owner_id' => $validated['owner_id'],
            'pet_id' => $validated['pet_id'],
            'veterinarian_id' => $user->id,
            'veterinarian_name' => $user->name,
            'license_no' => $user->license_no ?? 'PRC-VET-009821',
            'body_weight' => $validated['body_weight'] ?? null,
            'rx_details' => $validated['rx_details'],
            'instructions' => $validated['instructions'] ?? null,
            'date_issued' => Carbon::now()->format('Y-m-d'),
        ]);

        return redirect()->back()->with('success', "Prescription {$code} generated and attached successfully!");
    }

    public function update(Request $request, Prescription $prescription)
    {
        $validated = $request->validate([
            'rx_details' => 'required|string',
            'instructions' => 'nullable|string',
            'body_weight' => 'nullable|string|max:50',
        ]);

        $prescription->update($validated);

        return redirect()->back()->with('success', "Prescription {$prescription->prescription_code} updated successfully!");
    }

    public function print(Prescription $prescription)
    {
        $prescription->load(['owner', 'pet', 'veterinarian', 'medicalRecord']);
        return view('veterinarian.prescriptions.print', compact('prescription'));
    }
}
