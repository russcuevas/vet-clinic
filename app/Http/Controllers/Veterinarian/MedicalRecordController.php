<?php

namespace App\Http\Controllers\Veterinarian;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MedicalRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = MedicalRecord::with(['owner', 'pet', 'prescription', 'veterinarian']);

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('record_code', 'LIKE', "%{$search}%")
                  ->orWhereHas('owner', fn($sub) => $sub->where('full_name', 'LIKE', "%{$search}%"))
                  ->orWhereHas('pet', fn($sub) => $sub->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $records = $query->latest()->get();
        $owners = Owner::with('pets')->where('status', 'active')->get();
        $staffMembers = \App\Models\Employee::where('status', 'active')->orderBy('full_name')->get();
        $generatedCode = MedicalRecord::generateRecordCode();

        return view('veterinarian.medical.index', compact('records', 'owners', 'staffMembers', 'generatedCode'));
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
                'birth_date' => 'nullable|date',
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
                'birth_date' => $clientData['birth_date'] ?? null,
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
            'visit_date' => 'nullable|date',
            'service_type' => 'required|in:consultation,follow_up,wellness',
            'body_weight' => 'nullable|string|max:50',
            'temperature' => 'nullable|string|max:50',
            'body_score' => 'nullable|string|max:100',
            'history_taking' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'medication_treatment' => 'nullable|string',
            'laboratory_notes' => 'nullable|string',
            'veterinarians_notes' => 'nullable|string',
            'service_fee' => 'nullable|numeric|min:0',
            'items' => 'nullable|array',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:0.01',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.remarks' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
            'follow_up_notes' => 'nullable|string',
            'lab_results' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf,doc,docx|max:10240',
            'prescribe_rx' => 'nullable|string',
            'rx_instructions' => 'nullable|string',
            // Optional Admission fields
            'is_admission' => 'nullable|boolean',
            'admission_days' => 'nullable|integer|min:1',
            'daily_rate' => 'nullable|numeric|min:0',
            'assigned_employee_id' => 'nullable|exists:employees,id',
            'admission_notes' => 'nullable|string',
        ]);

        $recordCode = MedicalRecord::generateRecordCode();
        $labPath = null;

        if ($request->hasFile('lab_results')) {
            $file = $request->file('lab_results');
            $filename = 'lab_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/lab_results'), $filename);
            $labPath = 'uploads/lab_results/' . $filename;
        }

        // Process Laboratory Tests & Medical Services
        $labAndServicesItems = [];
        $servicesSubtotal = 0;
        $baseServiceFee = $request->filled('service_fee') 
            ? floatval($request->input('service_fee')) 
            : 450.00;

        if (!empty($request->input('items')) && is_array($request->input('items'))) {
            foreach ($request->input('items') as $itemData) {
                $itemName = trim($itemData['name'] ?? '');
                if (!empty($itemName)) {
                    $qty = isset($itemData['quantity']) && is_numeric($itemData['quantity']) ? floatval($itemData['quantity']) : 1;
                    $price = isset($itemData['price']) && is_numeric($itemData['price']) ? floatval($itemData['price']) : 0;
                    $total = $qty * $price;
                    $servicesSubtotal += $total;
                    $labAndServicesItems[] = [
                        'name' => $itemName,
                        'quantity' => $qty,
                        'price' => $price,
                        'total' => $total,
                        'instructions' => $itemData['instructions'] ?? ($itemData['remarks'] ?? ''),
                    ];
                }
            }
        }

        $isAdmission = $request->boolean('is_admission');
        $admissionTotal = 0;
        if ($isAdmission) {
            $days = intval($request->input('admission_days', 1));
            $rate = floatval($request->input('daily_rate', 450.00));
            $admissionTotal = $days * $rate;
        }

        $totalBillAmount = $baseServiceFee + $servicesSubtotal + $admissionTotal;

        $vetUser = auth()->user();
        $visitDate = !empty($validated['visit_date']) ? Carbon::parse($validated['visit_date']) : Carbon::now();

        $isPaid = $request->boolean('is_already_paid');

        $record = MedicalRecord::create([
            'record_code' => $recordCode,
            'owner_id' => $ownerId,
            'pet_id' => $petId,
            'veterinarian_id' => $vetUser->id,
            'service_type' => $validated['service_type'],
            'visit_date' => $visitDate->format('Y-m-d'),
            'body_weight' => $validated['body_weight'] ?? null,
            'temperature' => $validated['temperature'] ?? null,
            'body_score' => $validated['body_score'] ?? null,
            'history_taking' => $validated['history_taking'] ?? null,
            'attached_lab_results' => $labPath,
            'laboratory_notes' => $validated['laboratory_notes'] ?? null,
            'diagnosis' => $validated['diagnosis'] ?? null,
            'medication_treatment' => $validated['medication_treatment'] ?? null,
            'veterinarians_notes' => $validated['veterinarians_notes'] ?? null,
            'prescribed_items' => $labAndServicesItems,
            'service_fee' => $baseServiceFee,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
            'follow_up_notes' => $validated['follow_up_notes'] ?? null,
            'status' => $isPaid ? 'completed' : 'ongoing',
            'created_at' => $visitDate,
        ]);

        if (!empty($validated['prescribe_rx'])) {
            $rxCode = Prescription::generatePrescriptionCode();
            Prescription::create([
                'prescription_code' => $rxCode,
                'medical_record_id' => $record->id,
                'owner_id' => $ownerId,
                'pet_id' => $petId,
                'veterinarian_id' => $vetUser->id,
                'veterinarian_name' => $vetUser->name,
                'license_no' => $vetUser->license_no ?? 'PRC-VET',
                'body_weight' => $validated['body_weight'] ?? null,
                'rx_details' => $validated['prescribe_rx'],
                'instructions' => $validated['rx_instructions'] ?? null,
                'date_issued' => $visitDate->format('Y-m-d'),
            ]);
        }

        // Handle Admission creation if toggled
        if ($isAdmission) {
            $days = intval($request->input('admission_days', 1));
            $rate = floatval($request->input('daily_rate', 450.00));
            Appointment::create([
                'appointment_code' => Appointment::generateAppointmentCode(),
                'service_category' => 'boarding',
                'service_type' => 'Pet Admission / Inpatient Care',
                'owner_id' => $ownerId,
                'pet_id' => $petId,
                'booked_by' => auth()->id(),
                'veterinarian_id' => auth()->id(),
                'assigned_employee_id' => $request->input('assigned_employee_id') ?: null,
                'appointment_date' => $visitDate->format('Y-m-d'),
                'appointment_time' => '08:00:00',
                'purpose_examination_notes' => $request->input('admission_notes') ?: ($validated['diagnosis'] ? "Admission for {$validated['diagnosis']}" : 'Inpatient Confinement & Monitoring'),
                'boarding_days' => $days,
                'daily_rate' => $rate,
                'total_price' => $days * $rate,
                'status' => 'checked_in',
            ]);
        }

        // Push Itemized Bill to Central Cashier Billing Queue
        $owner = Owner::find($ownerId);
        $invoiceNo = Bill::generateInvoiceNo();
        $bill = Bill::create([
            'invoice_no' => $invoiceNo,
            'owner_id' => $owner->id,
            'pet_id' => $petId,
            'medical_record_id' => $record->id,
            'client_name' => $owner->full_name,
            'service_type' => 'veterinary',
            'subtotal' => $totalBillAmount,
            'total_amount' => $totalBillAmount,
            'payment_status' => $isPaid ? 'paid' : 'unpaid',
            'paid_amount' => $isPaid ? $totalBillAmount : 0.00,
            'change_amount' => 0.00,
            'payment_method' => 'cash',
            'paid_at' => $isPaid ? $visitDate : null,
            'cashier_id' => $isPaid ? auth()->id() : null,
            'transaction_date' => $visitDate,
            'notes' => $isPaid 
                ? "Old/Historical record for {$recordCode} (Auto-marked as Paid)"
                : ucfirst(str_replace('_', ' ', $validated['service_type'])) . " for {$recordCode} on " . $visitDate->format('M d, Y'),
        ]);

        // 1. Base veterinary consultation service item
        BillItem::create([
            'bill_id' => $bill->id,
            'item_name' => 'Veterinary Service: ' . ucfirst(str_replace('_', ' ', $validated['service_type'])),
            'item_type' => 'service',
            'quantity' => 1,
            'unit_price' => $baseServiceFee,
            'total_price' => $baseServiceFee,
        ]);

        // 2. Laboratory Tests & Medical Services line items
        foreach ($labAndServicesItems as $lItem) {
            BillItem::create([
                'bill_id' => $bill->id,
                'item_name' => $lItem['name'],
                'item_type' => 'service',
                'quantity' => $lItem['quantity'],
                'unit_price' => $lItem['price'],
                'total_price' => $lItem['total'],
            ]);
        }

        // 3. Admission line item if admitted
        if ($isAdmission) {
            $days = intval($request->input('admission_days', 1));
            $rate = floatval($request->input('daily_rate', 450.00));
            BillItem::create([
                'bill_id' => $bill->id,
                'item_name' => 'Pet Admission / Inpatient Stay (' . $days . ' Days)',
                'item_type' => 'service',
                'quantity' => $days,
                'unit_price' => $rate,
                'total_price' => $days * $rate,
            ]);
        }

        // Complete any checked-in appointments for this pet
        Appointment::where('pet_id', $petId)
            ->where('service_category', '!=', 'boarding')
            ->where('status', 'checked_in')
            ->update(['status' => 'completed']);

        $msg = $isPaid 
            ? "Historical record saved ({$recordCode}) and automatically marked as PAID ({$invoiceNo})!"
            : ($isAdmission 
                ? "Patient checkup & admission saved ({$recordCode}) and queued to Cashier ({$invoiceNo} - ₱" . number_format($totalBillAmount, 2) . ")!"
                : "Patient examination recorded ({$recordCode}) and sent to Cashier Billing queue ({$invoiceNo} - ₱" . number_format($totalBillAmount, 2) . ")!");

        return redirect()->route('vet.medical.index')->with('success', $msg);
    }

    public function show(MedicalRecord $record)
    {
        $record->load(['owner', 'pet', 'prescription', 'veterinarian', 'bill.items']);
        return view('veterinarian.medical.show', compact('record'));
    }

    public function update(Request $request, MedicalRecord $record)
    {
        $validated = $request->validate([
            'visit_date' => 'nullable|date',
            'body_weight' => 'nullable|string|max:50',
            'temperature' => 'nullable|string|max:50',
            'body_score' => 'nullable|string|max:100',
            'history_taking' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'medication_treatment' => 'nullable|string',
            'laboratory_notes' => 'nullable|string',
            'veterinarians_notes' => 'nullable|string',
            'service_fee' => 'nullable|numeric|min:0',
            'items' => 'nullable|array',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:0.01',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.remarks' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
            'follow_up_notes' => 'nullable|string',
            'status' => 'required|in:ongoing,completed,billed',
            'lab_results' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf,doc,docx|max:10240',
            'prescribe_rx' => 'nullable|string',
            'rx_instructions' => 'nullable|string',
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

        $baseServiceFee = $request->filled('service_fee') 
            ? floatval($request->input('service_fee')) 
            : floatval($record->service_fee ?? 450.00);

        // Process Laboratory Tests & Medical Services
        $labAndServicesItems = [];
        $servicesSubtotal = 0;
        if ($request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $itemData) {
                $itemName = trim($itemData['name'] ?? '');
                if (!empty($itemName)) {
                    $qty = isset($itemData['quantity']) && is_numeric($itemData['quantity']) ? floatval($itemData['quantity']) : 1;
                    $price = isset($itemData['price']) && is_numeric($itemData['price']) ? floatval($itemData['price']) : 0;
                    $total = $qty * $price;
                    $servicesSubtotal += $total;
                    $labAndServicesItems[] = [
                        'name' => $itemName,
                        'quantity' => $qty,
                        'price' => $price,
                        'total' => $total,
                        'instructions' => $itemData['instructions'] ?? ($itemData['remarks'] ?? ''),
                    ];
                }
            }
        } elseif (!$request->has('items')) {
            $labAndServicesItems = $record->prescribed_items ?? [];
            foreach ($labAndServicesItems as $l) {
                $servicesSubtotal += floatval($l['total'] ?? (floatval($l['quantity'] ?? 1) * floatval($l['price'] ?? 0)));
            }
        }

        $totalBillAmount = $baseServiceFee + $servicesSubtotal;

        $record->update([
            'visit_date' => !empty($validated['visit_date']) ? Carbon::parse($validated['visit_date']) : ($record->visit_date ?: Carbon::now()),
            'body_weight' => $validated['body_weight'] ?? $record->body_weight,
            'temperature' => $validated['temperature'] ?? $record->temperature,
            'body_score' => $validated['body_score'] ?? $record->body_score,
            'history_taking' => $validated['history_taking'] ?? $record->history_taking,
            'diagnosis' => $validated['diagnosis'] ?? $record->diagnosis,
            'medication_treatment' => $validated['medication_treatment'] ?? $record->medication_treatment,
            'laboratory_notes' => $validated['laboratory_notes'] ?? $record->laboratory_notes,
            'veterinarians_notes' => $validated['veterinarians_notes'] ?? $record->veterinarians_notes,
            'prescribed_items' => $labAndServicesItems,
            'service_fee' => $baseServiceFee,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
            'follow_up_notes' => $validated['follow_up_notes'] ?? null,
            'status' => $validated['status'],
            'attached_lab_results' => $validated['attached_lab_results'] ?? $record->attached_lab_results,
        ]);

        // Prescription handling
        $vetUser = auth()->user();
        if (!empty($validated['prescribe_rx'])) {
            if ($record->prescription) {
                $record->prescription->update([
                    'body_weight' => $validated['body_weight'] ?? $record->prescription->body_weight,
                    'rx_details' => $validated['prescribe_rx'],
                    'instructions' => $validated['rx_instructions'] ?? $record->prescription->instructions,
                ]);
            } else {
                Prescription::create([
                    'prescription_code' => Prescription::generatePrescriptionCode(),
                    'medical_record_id' => $record->id,
                    'owner_id' => $record->owner_id,
                    'pet_id' => $record->pet_id,
                    'veterinarian_id' => $vetUser->id,
                    'veterinarian_name' => $vetUser->name,
                    'license_no' => $vetUser->license_no ?? 'PRC-VET',
                    'body_weight' => $validated['body_weight'] ?? null,
                    'rx_details' => $validated['prescribe_rx'],
                    'instructions' => $validated['rx_instructions'] ?? null,
                    'date_issued' => Carbon::now()->format('Y-m-d'),
                ]);
            }
        }

        // Central Billing Queue synchronization with Itemized Laboratory Tests & Services
        $bill = $record->bill ?: Bill::where('medical_record_id', $record->id)->first();
        $invoiceNo = null;

        if ($bill) {
            $invoiceNo = $bill->invoice_no;
            if ($bill->payment_status !== 'paid') {
                $bill->update([
                    'subtotal' => $totalBillAmount,
                    'total_amount' => $totalBillAmount,
                    'notes' => ucfirst(str_replace('_', ' ', $record->service_type)) . " for {$record->record_code} on " . Carbon::parse($record->visit_date ?? Carbon::now())->format('M d, Y'),
                ]);

                // Re-sync all bill line items
                $bill->items()->delete();

                // 1. Consultation line item
                BillItem::create([
                    'bill_id' => $bill->id,
                    'item_name' => 'Veterinary Service: ' . ucfirst(str_replace('_', ' ', $record->service_type)),
                    'item_type' => 'service',
                    'quantity' => 1,
                    'unit_price' => $baseServiceFee,
                    'total_price' => $baseServiceFee,
                ]);

                // 2. Laboratory Tests & Medical Services
                foreach ($labAndServicesItems as $lItem) {
                    BillItem::create([
                        'bill_id' => $bill->id,
                        'item_name' => $lItem['name'],
                        'item_type' => 'service',
                        'quantity' => $lItem['quantity'],
                        'unit_price' => $lItem['price'],
                        'total_price' => $lItem['total'],
                    ]);
                }
            }
        } else {
            // Generate Bill in Cashier queue
            $owner = $record->owner ?: Owner::find($record->owner_id);
            $invoiceNo = Bill::generateInvoiceNo();
            $bill = Bill::create([
                'invoice_no' => $invoiceNo,
                'owner_id' => $record->owner_id,
                'pet_id' => $record->pet_id,
                'medical_record_id' => $record->id,
                'client_name' => $owner ? $owner->full_name : 'Client',
                'service_type' => 'veterinary',
                'subtotal' => $totalBillAmount,
                'total_amount' => $totalBillAmount,
                'payment_status' => 'unpaid',
                'paid_amount' => 0.00,
                'change_amount' => 0.00,
                'payment_method' => 'cash',
                'transaction_date' => Carbon::now(),
                'notes' => ucfirst(str_replace('_', ' ', $record->service_type)) . " for {$record->record_code} on " . Carbon::parse($record->visit_date ?? Carbon::now())->format('M d, Y'),
            ]);

            BillItem::create([
                'bill_id' => $bill->id,
                'item_name' => 'Veterinary Service: ' . ucfirst(str_replace('_', ' ', $record->service_type)),
                'item_type' => 'service',
                'quantity' => 1,
                'unit_price' => $baseServiceFee,
                'total_price' => $baseServiceFee,
            ]);

            foreach ($labAndServicesItems as $lItem) {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'item_name' => $lItem['name'],
                    'item_type' => 'service',
                    'quantity' => $lItem['quantity'],
                    'unit_price' => $lItem['price'],
                    'total_price' => $lItem['total'],
                ]);
            }
        }

        // If marked completed, also complete any linked checked-in appointment
        if (in_array($validated['status'], ['completed', 'billed'])) {
            Appointment::where('pet_id', $record->pet_id)
                ->where('status', 'checked_in')
                ->update(['status' => 'completed']);
        }

        $petName = $record->pet ? $record->pet->name : 'Patient';
        $feeFormatted = number_format($totalBillAmount, 2);

        $msg = $validated['status'] === 'completed'
            ? "Doctor checkup completed for {$petName}! Case {$record->record_code} sent to Cashier Billing queue ({$invoiceNo} - ₱{$feeFormatted})."
            : "Record {$record->record_code} updated successfully and synced with Central Billing ({$invoiceNo} - ₱{$feeFormatted}).";

        return redirect()->back()->with('success', $msg);
    }

    public function followUps(Request $request)
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::today()->startOfWeek();
        $endOfWeek = Carbon::today()->endOfWeek();

        $query = MedicalRecord::with(['owner', 'pet.medicalRecords', 'veterinarian', 'prescription'])
            ->whereNotNull('follow_up_date');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('record_code', 'LIKE', "%{$search}%")
                  ->orWhere('follow_up_notes', 'LIKE', "%{$search}%")
                  ->orWhere('diagnosis', 'LIKE', "%{$search}%")
                  ->orWhereHas('owner', fn($sub) => $sub->where('full_name', 'LIKE', "%{$search}%")->orWhere('contact_number', 'LIKE', "%{$search}%"))
                  ->orWhereHas('pet', fn($sub) => $sub->where('name', 'LIKE', "%{$search}%")->orWhere('pet_code', 'LIKE', "%{$search}%"));
            });
        }

        $filter = $request->get('filter', 'all');

        if ($filter === 'today') {
            $query->whereDate('follow_up_date', $today);
        } elseif ($filter === 'upcoming') {
            $query->whereDate('follow_up_date', '>', $today);
        } elseif ($filter === 'this_week') {
            $query->whereBetween('follow_up_date', [$startOfWeek, $endOfWeek]);
        } elseif ($filter === 'overdue') {
            $query->whereDate('follow_up_date', '<', $today);
        }

        $followUps = $query->orderBy('follow_up_date', 'asc')->get();

        // Metrics count
        $todayCount = MedicalRecord::whereDate('follow_up_date', $today)->count();
        $upcomingCount = MedicalRecord::whereDate('follow_up_date', '>', $today)->count();
        $thisWeekCount = MedicalRecord::whereBetween('follow_up_date', [$startOfWeek, $endOfWeek])->count();
        $overdueCount = MedicalRecord::whereDate('follow_up_date', '<', $today)->count();
        $totalCount = MedicalRecord::whereNotNull('follow_up_date')->count();

        return view('veterinarian.medical.followups', compact(
            'followUps',
            'filter',
            'todayCount',
            'upcomingCount',
            'thisWeekCount',
            'overdueCount',
            'totalCount'
        ));
    }

    /**
     * Perform Follow-up Examination: Pulls up full history, performs follow-up checkup,
     * saves as a new medical record visit, schedules next follow-up, and sends itemized bill to Cashier.
     */
    public function performFollowUp(Request $request)
    {
        $validated = $request->validate([
            'origin_record_id' => 'required|exists:medical_records,id',
            'owner_id' => 'required|exists:owners,id',
            'pet_id' => 'required|exists:pets,id',
            'visit_date' => 'required|date',
            'temperature' => 'nullable|string|max:50',
            'body_weight' => 'nullable|string|max:50',
            'body_score' => 'nullable|string|max:100',
            'history_taking' => 'required|string', // Follow-up progress / exam notes
            'diagnosis' => 'required|string',
            'medication_treatment' => 'nullable|string',
            'laboratory_notes' => 'nullable|string',
            'veterinarians_notes' => 'nullable|string',
            'service_fee' => 'required|numeric|min:0',
            'items' => 'nullable|array',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.quantity' => 'nullable|numeric|min:0.01',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.remarks' => 'nullable|string',
            'next_follow_up_date' => 'nullable|date',
            'next_follow_up_notes' => 'nullable|string',
            'prescribe_rx' => 'nullable|string',
            'rx_instructions' => 'nullable|string',
            'lab_results' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf,doc,docx|max:10240',
        ]);

        $originRecord = MedicalRecord::findOrFail($validated['origin_record_id']);
        $recordCode = MedicalRecord::generateRecordCode();
        $vetUser = auth()->user();
        $visitDate = Carbon::parse($validated['visit_date']);

        $labPath = null;
        if ($request->hasFile('lab_results')) {
            $file = $request->file('lab_results');
            $filename = 'lab_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/lab_results'), $filename);
            $labPath = 'uploads/lab_results/' . $filename;
        }

        // Process Laboratory Tests & Medical Services
        $labAndServicesItems = [];
        $servicesSubtotal = 0;
        $baseServiceFee = floatval($validated['service_fee']);

        if (!empty($request->input('items')) && is_array($request->input('items'))) {
            foreach ($request->input('items') as $itemData) {
                $itemName = trim($itemData['name'] ?? '');
                if (!empty($itemName)) {
                    $qty = isset($itemData['quantity']) && is_numeric($itemData['quantity']) ? floatval($itemData['quantity']) : 1;
                    $price = isset($itemData['price']) && is_numeric($itemData['price']) ? floatval($itemData['price']) : 0;
                    $total = $qty * $price;
                    $servicesSubtotal += $total;
                    $labAndServicesItems[] = [
                        'name' => $itemName,
                        'quantity' => $qty,
                        'price' => $price,
                        'total' => $total,
                        'instructions' => $itemData['instructions'] ?? ($itemData['remarks'] ?? ''),
                    ];
                }
            }
        }

        $totalBillAmount = $baseServiceFee + $servicesSubtotal;

        // Save NEW Medical Record (Follow-up visit history preserved)
        $newRecord = MedicalRecord::create([
            'record_code' => $recordCode,
            'owner_id' => $validated['owner_id'],
            'pet_id' => $validated['pet_id'],
            'veterinarian_id' => $vetUser->id,
            'service_type' => 'follow_up',
            'visit_date' => $visitDate->format('Y-m-d'),
            'body_weight' => $validated['body_weight'] ?? null,
            'temperature' => $validated['temperature'] ?? null,
            'body_score' => $validated['body_score'] ?? null,
            'history_taking' => $validated['history_taking'],
            'attached_lab_results' => $labPath,
            'laboratory_notes' => $validated['laboratory_notes'] ?? null,
            'diagnosis' => $validated['diagnosis'],
            'medication_treatment' => $validated['medication_treatment'] ?? null,
            'veterinarians_notes' => $validated['veterinarians_notes'] ?? null,
            'prescribed_items' => $labAndServicesItems,
            'service_fee' => $baseServiceFee,
            'follow_up_date' => $validated['next_follow_up_date'] ?? null,
            'follow_up_notes' => $validated['next_follow_up_notes'] ?? null,
            'status' => 'completed',
            'created_at' => $visitDate,
        ]);

        // Prescription if issued
        if (!empty($validated['prescribe_rx'])) {
            Prescription::create([
                'prescription_code' => Prescription::generatePrescriptionCode(),
                'medical_record_id' => $newRecord->id,
                'owner_id' => $validated['owner_id'],
                'pet_id' => $validated['pet_id'],
                'veterinarian_id' => $vetUser->id,
                'veterinarian_name' => $vetUser->name,
                'license_no' => $vetUser->license_no ?? 'PRC-VET',
                'body_weight' => $validated['body_weight'] ?? null,
                'rx_details' => $validated['prescribe_rx'],
                'instructions' => $validated['rx_instructions'] ?? null,
                'date_issued' => $visitDate->format('Y-m-d'),
            ]);
        }

        // Push Itemized Bill to Central Cashier Billing Queue
        $owner = Owner::find($validated['owner_id']);
        $pet = Pet::find($validated['pet_id']);
        $invoiceNo = Bill::generateInvoiceNo();
        $bill = Bill::create([
            'invoice_no' => $invoiceNo,
            'owner_id' => $owner->id,
            'pet_id' => $pet->id,
            'medical_record_id' => $newRecord->id,
            'client_name' => $owner->full_name,
            'service_type' => 'veterinary',
            'subtotal' => $totalBillAmount,
            'total_amount' => $totalBillAmount,
            'payment_status' => 'unpaid',
            'paid_amount' => 0.00,
            'change_amount' => 0.00,
            'payment_method' => 'cash',
            'transaction_date' => $visitDate,
            'notes' => "Follow-up Examination for {$recordCode} (Origin: {$originRecord->record_code}) on " . $visitDate->format('M d, Y'),
        ]);

        // Consultation item
        BillItem::create([
            'bill_id' => $bill->id,
            'item_name' => 'Veterinary Service: Follow Up Examination',
            'item_type' => 'service',
            'quantity' => 1,
            'unit_price' => $baseServiceFee,
            'total_price' => $baseServiceFee,
        ]);

        // Itemized lab tests and medical services
        foreach ($labAndServicesItems as $lItem) {
            BillItem::create([
                'bill_id' => $bill->id,
                'item_name' => $lItem['name'],
                'item_type' => 'service',
                'quantity' => $lItem['quantity'],
                'unit_price' => $lItem['price'],
                'total_price' => $lItem['total'],
            ]);
        }

        // Clear the past follow-up marker on the origin record so it doesn't stay overdue
        $originRecord->update([
            'follow_up_notes' => ($originRecord->follow_up_notes ? $originRecord->follow_up_notes . ' ' : '') . "[Followed up on " . $visitDate->format('m/d/Y') . " -> {$recordCode}]",
            'follow_up_date' => null,
        ]);

        return redirect()->route('vet.followups.index')->with('success', "Follow-up examination completed for {$pet->name} ({$recordCode})! Medical record history saved and bill sent to Cashier ({$invoiceNo} - ₱" . number_format($totalBillAmount, 2) . ").");
    }

    /**
     * Get patient full medical record history (AJAX)
     */
    public function getFollowUpHistory(MedicalRecord $record)
    {
        $pet = Pet::with(['owner', 'medicalRecords.veterinarian', 'medicalRecords.prescription', 'prescriptions'])->findOrFail($record->pet_id);
        
        $history = $pet->medicalRecords()->with('veterinarian', 'prescription')->orderBy('visit_date', 'desc')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'pet' => $pet,
            'owner' => $pet->owner,
            'history' => $history,
            'current_record' => $record,
        ]);
    }

    public function updateFollowUp(Request $request, MedicalRecord $record)
    {
        $validated = $request->validate([
            'follow_up_date' => 'nullable|date',
            'follow_up_notes' => 'nullable|string|max:500',
        ]);

        $record->update($validated);

        return redirect()->back()->with('success', "Follow-up schedule for record {$record->record_code} updated successfully!");
    }
}
