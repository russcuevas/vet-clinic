<?php

namespace App\Http\Controllers\Veterinarian;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\Employee;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmissionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $dateFilter = $request->query('date_filter', 'all');
        $search = $request->query('search');

        $query = Appointment::where('service_category', 'boarding')
            ->with(['owner', 'pet', 'assignedEmployee', 'veterinarian', 'bookedBy'])
            ->latest('appointment_date')
            ->latest('appointment_time');

        if ($status) {
            $query->where('status', $status);
        }

        $today = Carbon::today();
        if ($dateFilter === 'today') {
            $query->whereDate('appointment_date', $today);
        } elseif ($dateFilter === 'active') {
            $query->whereIn('status', ['confirmed', 'checked_in']);
        } elseif ($dateFilter === 'upcoming') {
            $query->whereDate('appointment_date', '>=', $today);
        } elseif ($dateFilter === 'past') {
            $query->whereDate('appointment_date', '<', $today);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'like', "%{$search}%")
                  ->orWhere('purpose_examination_notes', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($oq) use ($search) {
                      $oq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('contact_number', 'like', "%{$search}%")
                         ->orWhere('client_code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('pet', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $admissions = $query->paginate(15)->withQueryString();

        // Statistics
        $todayAdmitted = Appointment::where('service_category', 'boarding')->whereDate('appointment_date', $today)->count();
        $activeAdmitted = Appointment::where('service_category', 'boarding')->whereIn('status', ['confirmed', 'checked_in'])->count();
        $dischargedCount = Appointment::where('service_category', 'boarding')->where('status', 'completed')->count();
        $totalAdmissions = Appointment::where('service_category', 'boarding')->count();

        // Owners & Staff for Modal
        $owners = Owner::with('pets')->orderBy('full_name')->get();
        $staffMembers = Employee::where('status', 'active')->orderBy('full_name')->get();
        $veterinarians = User::where('role', 'veterinarian')->orderBy('name')->get();

        return view('veterinarian.admission.index', compact(
            'admissions',
            'todayAdmitted',
            'activeAdmitted',
            'dischargedCount',
            'totalAdmissions',
            'owners',
            'staffMembers',
            'veterinarians'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_type' => 'nullable|string',
            'owner_id' => 'required_if:client_type,existing|nullable|exists:owners,id',
            'pet_id' => 'required_if:client_type,existing|nullable|exists:pets,id',
            'owner_name' => 'required_if:client_type,new|nullable|string|max:255',
            'contact_number' => 'required_if:client_type,new|nullable|string|max:50',
            'pet_name' => 'required_if:client_type,new|nullable|string|max:255',
            'species' => 'required_if:client_type,new|nullable|string|max:100',
            'breed' => 'nullable|string|max:100',
            'admission_date' => 'required|date',
            'admission_time' => 'nullable',
            'boarding_days' => 'required|integer|min:1',
            'daily_rate' => 'required|numeric|min:0',
            'assigned_employee_id' => 'nullable|exists:employees,id',
            'purpose_examination_notes' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,checked_in,completed,cancelled',
            'send_to_billing' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // If new client
            if ($request->input('client_type') === 'new') {
                $owner = Owner::create([
                    'client_code' => Owner::generateClientCode(),
                    'full_name' => $validated['owner_name'],
                    'contact_number' => $validated['contact_number'],
                    'status' => 'active',
                ]);

                $pet = Pet::create([
                    'pet_code' => Pet::generatePetCode(),
                    'owner_id' => $owner->id,
                    'name' => $validated['pet_name'],
                    'species' => $validated['species'],
                    'breed' => $validated['breed'] ?? null,
                    'status' => 'active',
                ]);

                $ownerId = $owner->id;
                $petId = $pet->id;
                $isNewClient = true;
                $isNewPet = true;
            } else {
                $ownerId = $validated['owner_id'];
                $petId = $validated['pet_id'];
                $isNewClient = false;
                $isNewPet = false;
            }

            $totalPrice = floatval($validated['boarding_days']) * floatval($validated['daily_rate']);

            $admission = Appointment::create([
                'appointment_code' => Appointment::generateAppointmentCode(),
                'service_category' => 'boarding',
                'service_type' => 'Pet Admission / Inpatient Care',
                'owner_id' => $ownerId,
                'pet_id' => $petId,
                'booked_by' => auth()->id(),
                'veterinarian_id' => auth()->id(),
                'assigned_employee_id' => $validated['assigned_employee_id'] ?? null,
                'appointment_date' => $validated['admission_date'],
                'appointment_time' => $validated['admission_time'] ?? '08:00:00',
                'purpose_examination_notes' => $validated['purpose_examination_notes'] ?? 'Pet Admission & Inpatient Monitoring',
                'boarding_days' => $validated['boarding_days'],
                'daily_rate' => $validated['daily_rate'],
                'total_price' => $totalPrice,
                'status' => $validated['status'],
                'is_new_client' => $isNewClient,
                'is_new_pet' => $isNewPet,
            ]);

            // Optional or automatic bill generation for Cashier
            if ($request->boolean('send_to_billing') || in_array($validated['status'], ['checked_in', 'completed'])) {
                $ownerObj = Owner::find($ownerId);
                $petObj = Pet::find($petId);
                
                $invoiceNo = Bill::generateInvoiceNo();
                $bill = Bill::create([
                    'invoice_no' => $invoiceNo,
                    'owner_id' => $ownerId,
                    'pet_id' => $petId,
                    'client_name' => $ownerObj ? $ownerObj->full_name : 'Client',
                    'service_type' => 'boarding',
                    'subtotal' => $totalPrice,
                    'total_amount' => $totalPrice,
                    'payment_status' => 'unpaid',
                    'paid_amount' => 0.00,
                    'change_amount' => 0.00,
                    'payment_method' => 'cash',
                    'transaction_date' => Carbon::now(),
                    'notes' => "Pet Admission ({$validated['boarding_days']} days @ ₱" . number_format($validated['daily_rate'], 2) . "/day) for " . ($petObj ? $petObj->name : 'Pet') . " [{$admission->appointment_code}]",
                ]);

                BillItem::create([
                    'bill_id' => $bill->id,
                    'item_name' => 'Pet Admission / Inpatient Stay (' . $validated['boarding_days'] . ' Days)',
                    'item_type' => 'service',
                    'quantity' => $validated['boarding_days'],
                    'unit_price' => $validated['daily_rate'],
                    'total_price' => $totalPrice,
                ]);
            }

            DB::commit();
            return redirect()->route('vet.admission.index')->with('success', "Patient successfully admitted [{$admission->appointment_code}]!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save admission: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date',
            'boarding_days' => 'required|integer|min:1',
            'daily_rate' => 'required|numeric|min:0',
            'assigned_employee_id' => 'nullable|exists:employees,id',
            'purpose_examination_notes' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,checked_in,completed,cancelled',
        ]);

        $totalPrice = floatval($validated['boarding_days']) * floatval($validated['daily_rate']);
        $validated['total_price'] = $totalPrice;

        $appointment->update($validated);

        return redirect()->route('vet.admission.index')->with('success', "Admission record [{$appointment->appointment_code}] updated successfully!");
    }

    public function destroy(Appointment $appointment)
    {
        $code = $appointment->appointment_code;
        $appointment->delete();

        return redirect()->route('vet.admission.index')->with('success', "Admission record {$code} has been deleted.");
    }
}
