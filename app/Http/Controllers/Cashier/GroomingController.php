<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\GroomingRecord;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GroomingController extends Controller
{
    public function index(Request $request)
    {
        $query = GroomingRecord::with(['owner', 'pet', 'groomer', 'bill']);

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

        // Grooming staff / groomers
        $groomers = Employee::where('status', 'active')
            ->where(function ($q) {
                $q->where('position', 'LIKE', '%groom%')
                  ->orWhere('department', 'LIKE', '%groom%');
            })
            ->get();

        return view('cashier.grooming.index', compact('records', 'owners', 'generatedCode', 'groomers'));
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
            'groomer_id' => 'nullable|exists:employees,id',
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
            'groomer_id' => $validated['groomer_id'] ?? null,
            'body_weight' => $validated['body_weight'] ?? null,
            'temperature' => $validated['temperature'] ?? null,
            'body_score' => $validated['body_score'] ?? null,
            'style' => $validated['style'],
            'groomer_observation_notes' => $validated['groomer_observation_notes'] ?? null,
            'price' => $validated['price'],
            'status' => 'queued',
        ]);

        // Push directly to Central Billing Data Base
        $owner = Owner::find($ownerId);
        $invoiceNo = Bill::generateInvoiceNo();
        $bill = Bill::create([
            'invoice_no' => $invoiceNo,
            'owner_id' => $owner->id,
            'pet_id' => $petId,
            'cashier_id' => auth()->id(),
            'grooming_record_id' => $grooming->id,
            'client_name' => $owner->full_name,
            'service_type' => 'grooming',
            'subtotal' => $validated['price'],
            'total_amount' => $validated['price'],
            'payment_status' => 'unpaid',
            'transaction_date' => Carbon::now(),
            'notes' => "Grooming Service ({$validated['style']}) for {$code}",
        ]);

        BillItem::create([
            'bill_id' => $bill->id,
            'item_name' => 'Grooming Service: ' . $validated['style'],
            'item_type' => 'grooming',
            'quantity' => 1,
            'unit_price' => $validated['price'],
            'total_price' => $validated['price'],
        ]);

        return redirect()->back()->with('success', "Grooming session booked ({$code}) and queued in Checkout / Billing ({$invoiceNo})!");
    }

    public function update(Request $request, GroomingRecord $grooming)
    {
        $validated = $request->validate([
            'groomer_id' => 'nullable|exists:employees,id',
            'body_weight' => 'nullable|string|max:50',
            'temperature' => 'nullable|string|max:50',
            'body_score' => 'nullable|string|max:100',
            'style' => 'required|string|max:255',
            'groomer_observation_notes' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:queued,in_progress,completed,billed',
        ]);

        $grooming->update($validated);

        // Sync with Central Billing Database
        $bill = Bill::where('grooming_record_id', $grooming->id)->first();
        if ($bill) {
            // Update grooming bill item
            $billItem = BillItem::where('bill_id', $bill->id)->where('item_type', 'grooming')->first();
            if ($billItem) {
                $billItem->update([
                    'item_name' => 'Grooming Service: ' . $validated['style'],
                    'unit_price' => $validated['price'],
                    'total_price' => $validated['price'] * ($billItem->quantity ?: 1),
                ]);
            } else {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'item_name' => 'Grooming Service: ' . $validated['style'],
                    'item_type' => 'grooming',
                    'quantity' => 1,
                    'unit_price' => $validated['price'],
                    'total_price' => $validated['price'],
                ]);
            }

            // If bill is unpaid, recalculate subtotal and total_amount
            if ($bill->payment_status === 'unpaid') {
                $subtotal = $bill->items()->sum('total_price');
                $discount = $bill->discount ?? 0;
                $tax = $bill->tax ?? 0;
                $total = max(0, $subtotal - $discount + $tax);

                $bill->update([
                    'subtotal' => $subtotal,
                    'total_amount' => $total,
                    'notes' => "Grooming Service ({$validated['style']}) for {$grooming->grooming_code}",
                ]);
            }
        } else {
            // Auto-create bill if one doesn't exist yet
            $owner = $grooming->owner ?: Owner::find($grooming->owner_id);
            $invoiceNo = Bill::generateInvoiceNo();
            $bill = Bill::create([
                'invoice_no' => $invoiceNo,
                'owner_id' => $grooming->owner_id,
                'pet_id' => $grooming->pet_id,
                'cashier_id' => auth()->id(),
                'grooming_record_id' => $grooming->id,
                'client_name' => $owner ? $owner->full_name : 'Client',
                'service_type' => 'grooming',
                'subtotal' => $validated['price'],
                'total_amount' => $validated['price'],
                'payment_status' => ($validated['status'] === 'billed') ? 'paid' : 'unpaid',
                'transaction_date' => Carbon::now(),
                'notes' => "Grooming Service ({$validated['style']}) for {$grooming->grooming_code}",
            ]);

            BillItem::create([
                'bill_id' => $bill->id,
                'item_name' => 'Grooming Service: ' . $validated['style'],
                'item_type' => 'grooming',
                'quantity' => 1,
                'unit_price' => $validated['price'],
                'total_price' => $validated['price'],
            ]);
        }

        return redirect()->back()->with('success', "Grooming record {$grooming->grooming_code} and Billing checkout updated successfully!");
    }

    public function destroy(GroomingRecord $grooming)
    {
        $code = $grooming->grooming_code;

        // Clean up unpaid bill if exists
        $bill = Bill::where('grooming_record_id', $grooming->id)->first();
        if ($bill && $bill->payment_status === 'unpaid') {
            $bill->items()->delete();
            $bill->delete();
        }

        $grooming->delete();

        return redirect()->back()->with('success', "Grooming record {$code} removed successfully.");
    }
}
