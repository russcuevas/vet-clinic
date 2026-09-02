<?php

namespace App\Http\Controllers\Veterinarian;

use App\Http\Controllers\Controller;
use App\Models\GroomingRecord;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\Bill;
use App\Models\BillItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GroomingController extends Controller
{
    public function index(Request $request)
    {
        $query = GroomingRecord::with(['owner', 'pet']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('grooming_code', 'LIKE', "%{$search}%")
                  ->orWhere('style', 'LIKE', "%{$search}%")
                  ->orWhereHas('owner', fn($sub) => $sub->where('full_name', 'LIKE', "%{$search}%"))
                  ->orWhereHas('pet', fn($sub) => $sub->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $records = $query->latest()->get();
        $owners = Owner::with('pets')->where('status', 'active')->get();
        $generatedCode = GroomingRecord::generateGroomingCode();

        return view('veterinarian.grooming.index', compact('records', 'owners', 'generatedCode'));
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
            'body_weight' => 'nullable|string|max:50',
            'temperature' => 'nullable|string|max:50',
            'body_score' => 'nullable|string|max:100',
            'style' => 'required|string|max:255',
            'groomer_observation_notes' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        $code = GroomingRecord::generateGroomingCode();

        $grooming = GroomingRecord::create([
            'grooming_code' => $code,
            'owner_id' => $ownerId,
            'pet_id' => $petId,
            'body_weight' => $validated['body_weight'] ?? null,
            'temperature' => $validated['temperature'] ?? null,
            'body_score' => $validated['body_score'] ?? null,
            'style' => $validated['style'],
            'groomer_observation_notes' => $validated['groomer_observation_notes'] ?? null,
            'price' => $validated['price'],
            'status' => 'queued',
        ]);

        // Flowchart requirement: Automatically creates an unpaid bill queue for Cashier Desk
        $invoiceNo = Bill::generateInvoiceNo();
        $owner = Owner::find($ownerId);

        $bill = Bill::create([
            'invoice_no' => $invoiceNo,
            'owner_id' => $owner->id,
            'pet_id' => $petId,
            'client_name' => $owner->full_name,
            'service_type' => 'grooming',
            'total_amount' => $validated['price'],
            'payment_status' => 'unpaid',
            'transaction_date' => Carbon::now(),
        ]);

        BillItem::create([
            'bill_id' => $bill->id,
            'item_type' => 'grooming',
            'item_name' => 'Pet Grooming: ' . $validated['style'] . ' (' . $code . ')',
            'quantity' => 1,
            'unit_price' => $validated['price'],
            'total_price' => $validated['price'],
        ]);

        return redirect()->back()->with('success', "Grooming session {$code} booked! Sent to Cashier as Invoice {$invoiceNo}.");
    }

    public function update(Request $request, GroomingRecord $grooming)
    {
        $validated = $request->validate([
            'body_weight' => 'nullable|string|max:50',
            'temperature' => 'nullable|string|max:50',
            'body_score' => 'nullable|string|max:100',
            'style' => 'required|string|max:255',
            'groomer_observation_notes' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:queued,in_progress,completed,billed',
        ]);

        $grooming->update($validated);

        return redirect()->back()->with('success', "Grooming session {$grooming->grooming_code} updated successfully!");
    }

    public function destroy(GroomingRecord $grooming)
    {
        $code = $grooming->grooming_code;
        $grooming->delete();

        return redirect()->back()->with('success', "Grooming record {$code} removed successfully.");
    }
}
