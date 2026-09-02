<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\User;
use App\Models\Bill;
use App\Models\BillItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VeterinaryController extends Controller
{
    public function index(Request $request)
    {
        $query = MedicalRecord::with(['owner', 'pet', 'veterinarian', 'prescription']);

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('record_code', 'LIKE', "%{$search}%")
                  ->orWhereHas('owner', fn($sub) => $sub->where('full_name', 'LIKE', "%{$search}%")->orWhere('client_code', 'LIKE', "%{$search}%"))
                  ->orWhereHas('pet', fn($sub) => $sub->where('name', 'LIKE', "%{$search}%")->orWhere('pet_code', 'LIKE', "%{$search}%"));
            });
        }

        $records = $query->latest()->get();
        $owners = Owner::with('pets')->where('status', 'active')->get();
        $veterinarians = User::where('role', 'veterinarian')->orWhere('role', 'admin')->get();
        $generatedCode = MedicalRecord::generateRecordCode();
        $generatedRxCode = Prescription::generatePrescriptionCode();

        return view('admin.veterinary.index', compact('records', 'owners', 'veterinarians', 'generatedCode', 'generatedRxCode'));
    }

    public function store(Request $request)
    {
        $clientType = $request->input('client_type', 'existing');

        if ($clientType === 'new') {
            $clientData = $request->validate([
                'owner_name' => 'required|string|max:255',
                'contact_number' => 'required|string|max:50',
                'address' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'pet_name' => 'required|string|max:255',
                'species' => 'required|string|max:100',
                'breed' => 'nullable|string|max:100',
                'age' => 'nullable|string|max:50',
                'sex' => 'nullable|string|max:50',
                'color_markings' => 'nullable|string|max:255',
            ]);

            $owner = Owner::create([
                'client_code' => Owner::generateClientCode(),
                'full_name' => $clientData['owner_name'],
                'contact_number' => $clientData['contact_number'],
                'address' => $clientData['address'],
                'email' => $clientData['email'] ?? null,
                'status' => 'active',
            ]);

            $pet = Pet::create([
                'pet_code' => Pet::generatePetCode(),
                'owner_id' => $owner->id,
                'name' => $clientData['pet_name'],
                'species' => $clientData['species'],
                'breed' => $clientData['breed'] ?? null,
                'age' => $clientData['age'] ?? null,
                'sex' => $clientData['sex'] ?? null,
                'color' => $clientData['color_markings'] ?? null,
                'status' => 'active',
            ]);

            $ownerId = $owner->id;
            $petId = $pet->id;
        } else {
            $clientData = $request->validate([
                'owner_id' => 'required|exists:owners,id',
                'pet_id' => 'required|exists:pets,id',
            ]);
            $ownerId = $clientData['owner_id'];
            $petId = $clientData['pet_id'];
        }

        $validated = $request->validate([
            'veterinarian_id' => 'nullable|exists:users,id',
            'service_type' => 'required|in:consultation,follow_up,wellness',
            'body_weight' => 'nullable|string|max:50',
            'temperature' => 'nullable|string|max:50',
            'body_score' => 'nullable|string|max:100',
            'history_taking' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'veterinarians_notes' => 'nullable|string',
            'service_fee' => 'required|numeric|min:0',
            'lab_results' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            // Prescription optional fields
            'prescribe_rx' => 'nullable|string',
            'rx_instructions' => 'nullable|string',
        ]);

        $recordCode = MedicalRecord::generateRecordCode();
        $labPath = null;

        // Store picture in public/uploads/lab_results/
        if ($request->hasFile('lab_results')) {
            $file = $request->file('lab_results');
            $filename = 'lab_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/lab_results'), $filename);
            $labPath = 'uploads/lab_results/' . $filename;
        }

        $vetUser = $validated['veterinarian_id'] ? User::find($validated['veterinarian_id']) : auth()->user();

        $record = MedicalRecord::create([
            'record_code' => $recordCode,
            'owner_id' => $ownerId,
            'pet_id' => $petId,
            'veterinarian_id' => $vetUser->id ?? null,
            'service_type' => $validated['service_type'],
            'body_weight' => $validated['body_weight'] ?? null,
            'temperature' => $validated['temperature'] ?? null,
            'body_score' => $validated['body_score'] ?? null,
            'history_taking' => $validated['history_taking'] ?? null,
            'attached_lab_results' => $labPath,
            'diagnosis' => $validated['diagnosis'] ?? null,
            'veterinarians_notes' => $validated['veterinarians_notes'] ?? null,
            'service_fee' => $validated['service_fee'],
            'status' => 'ongoing',
        ]);

        // Generate Prescription if prescribed
        if (!empty($validated['prescribe_rx'])) {
            $rxCode = Prescription::generatePrescriptionCode();
            Prescription::create([
                'prescription_code' => $rxCode,
                'medical_record_id' => $record->id,
                'owner_id' => $ownerId,
                'pet_id' => $petId,
                'veterinarian_id' => $vetUser->id ?? null,
                'veterinarian_name' => $vetUser ? $vetUser->name : 'Attending Veterinarian',
                'license_no' => $vetUser ? $vetUser->license_no : 'PRC-VET',
                'body_weight' => $validated['body_weight'] ?? null,
                'rx_details' => $validated['prescribe_rx'],
                'instructions' => $validated['rx_instructions'] ?? null,
                'date_issued' => Carbon::now()->format('Y-m-d'),
            ]);
        }

        // Push directly to Central Billing Data Base
        $owner = Owner::find($ownerId);
        $invoiceNo = Bill::generateInvoiceNo();
        $bill = Bill::create([
            'invoice_no' => $invoiceNo,
            'owner_id' => $owner->id,
            'pet_id' => $petId,
            'medical_record_id' => $record->id,
            'client_name' => $owner->full_name,
            'service_type' => 'veterinary',
            'subtotal' => $validated['service_fee'],
            'total_amount' => $validated['service_fee'],
            'payment_status' => 'unpaid',
            'transaction_date' => Carbon::now(),
            'notes' => ucfirst($validated['service_type']) . " for record {$recordCode}",
        ]);

        BillItem::create([
            'bill_id' => $bill->id,
            'item_name' => 'Veterinary Service: ' . ucfirst($validated['service_type']),
            'item_type' => 'service',
            'quantity' => 1,
            'unit_price' => $validated['service_fee'],
            'total_price' => $validated['service_fee'],
        ]);

        return redirect()->back()->with('success', "Veterinary examination saved ({$recordCode}) and sent to Billing queue ({$invoiceNo})!");
    }

    public function update(Request $request, MedicalRecord $record)
    {
        $validated = $request->validate([
            'body_weight' => 'nullable|string|max:50',
            'temperature' => 'nullable|string|max:50',
            'body_score' => 'nullable|string|max:100',
            'history_taking' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'veterinarians_notes' => 'nullable|string',
            'service_fee' => 'required|numeric|min:0',
            'status' => 'required|in:ongoing,completed,billed',
            'lab_results' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($request->hasFile('lab_results')) {
            if ($record->attached_lab_results && file_exists(public_path($record->attached_lab_results))) {
                @unlink(public_path($record->attached_lab_results));
            }
            $file = $request->file('lab_results');
            $filename = 'lab_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/lab_results'), $filename);
            $validated['attached_lab_results'] = 'uploads/lab_results/' . $filename;
        }

        $record->update($validated);

        return redirect()->back()->with('success', "Medical record {$record->record_code} updated successfully!");
    }

    public function destroy(MedicalRecord $record)
    {
        $code = $record->record_code;
        if ($record->attached_lab_results && file_exists(public_path($record->attached_lab_results))) {
            @unlink(public_path($record->attached_lab_results));
        }
        $record->delete();

        return redirect()->back()->with('success', "Medical record {$code} removed successfully.");
    }
}
