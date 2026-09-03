<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\MedicalRecord;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClientController extends Controller
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
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        $owners = $query->latest()->get();
        $veterinarians = User::where('role', 'veterinarian')->orderBy('name')->get();
        $generatedCode = Owner::generateClientCode();
        $generatedPetCode = Pet::generatePetCode();

        return view('admin.clients.index', compact('owners', 'veterinarians', 'generatedCode', 'generatedPetCode'));
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
            'birth_date' => 'nullable|date',
            'sex' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:100',
            // Optional Old History Medical Record details
            'include_medical_record' => 'nullable',
            'med_visit_date' => 'nullable|date',
            'med_service_type' => 'nullable|in:consultation,follow_up,wellness',
            'med_veterinarian_id' => 'nullable|exists:users,id',
            'med_temperature' => 'nullable|string|max:50',
            'med_body_weight' => 'nullable|string|max:50',
            'med_body_score' => 'nullable|string|max:100',
            'med_history_taking' => 'nullable|string',
            'med_medication_treatment' => 'nullable|string',
            'med_laboratory_notes' => 'nullable|string',
            'med_diagnosis' => 'nullable|string',
            'med_service_fee' => 'nullable|numeric|min:0',
            'med_is_paid' => 'nullable',
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

        $pet = null;
        if (!empty($validated['pet_name'])) {
            $petCode = Pet::generatePetCode();
            $pet = Pet::create([
                'pet_code' => $petCode,
                'owner_id' => $owner->id,
                'name' => $validated['pet_name'],
                'species' => $validated['species'] ?? 'Dog',
                'breed' => $validated['breed'] ?? 'Mixed',
                'age' => $validated['age'] ?? 'Not specified',
                'birth_date' => $validated['birth_date'] ?? null,
                'sex' => $validated['sex'] ?? 'Male',
                'color' => $validated['color'] ?? null,
            ]);
        }

        // Check if user chose to encode a past/old medical record for this new client and pet
        if ($pet && $request->boolean('include_medical_record')) {
            $visitDate = !empty($validated['med_visit_date']) ? Carbon::parse($validated['med_visit_date']) : Carbon::now();
            $serviceFee = $validated['med_service_fee'] ?? 450.00;
            $serviceType = $validated['med_service_type'] ?? 'consultation';
            $isPaid = true; // Auto-paid for old/historical data

            $recordCode = MedicalRecord::generateRecordCode();
            $vetUser = !empty($validated['med_veterinarian_id']) ? User::find($validated['med_veterinarian_id']) : auth()->user();

            $record = MedicalRecord::create([
                'record_code' => $recordCode,
                'owner_id' => $owner->id,
                'pet_id' => $pet->id,
                'veterinarian_id' => $vetUser->id ?? null,
                'service_type' => $serviceType,
                'visit_date' => $visitDate->format('Y-m-d'),
                'temperature' => $validated['med_temperature'] ?? null,
                'body_weight' => $validated['med_body_weight'] ?? null,
                'body_score' => $validated['med_body_score'] ?? null,
                'history_taking' => $validated['med_history_taking'] ?? null,
                'medication_treatment' => $validated['med_medication_treatment'] ?? null,
                'laboratory_notes' => $validated['med_laboratory_notes'] ?? null,
                'diagnosis' => $validated['med_diagnosis'] ?? null,
                'service_fee' => $serviceFee,
                'status' => 'completed',
                'created_at' => $visitDate,
            ]);

            // Create Auto-Paid Billing Invoice
            $invoiceNo = Bill::generateInvoiceNo();
            $bill = Bill::create([
                'invoice_no' => $invoiceNo,
                'owner_id' => $owner->id,
                'pet_id' => $pet->id,
                'medical_record_id' => $record->id,
                'client_name' => $owner->full_name,
                'service_type' => 'veterinary',
                'subtotal' => $serviceFee,
                'total_amount' => $serviceFee,
                'payment_status' => 'paid',
                'paid_amount' => $serviceFee,
                'change_amount' => 0.00,
                'payment_method' => 'cash',
                'paid_at' => $visitDate,
                'cashier_id' => auth()->id(),
                'transaction_date' => $visitDate,
                'notes' => "Old/Historical record for {$recordCode} (Auto-marked as Paid)",
            ]);

            BillItem::create([
                'bill_id' => $bill->id,
                'item_name' => 'Veterinary Service: ' . ucfirst($serviceType),
                'item_type' => 'service',
                'quantity' => 1,
                'unit_price' => $serviceFee,
                'total_price' => $serviceFee,
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
