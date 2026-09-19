<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\Employee;
use App\Models\Bill;
use App\Models\BillItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoardingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $dateFilter = $request->query('date_filter', 'today');
        $search = $request->query('search');

        $query = Appointment::where('service_category', 'boarding')
            ->with(['owner', 'pet', 'assignedEmployee', 'bookedBy'])
            ->latest('appointment_date')
            ->latest('appointment_time');

        if ($status) {
            $query->where('status', $status);
        }

        $today = Carbon::today();
        if ($dateFilter === 'today') {
            $query->whereDate('appointment_date', $today);
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

        $boardings = $query->paginate(15)->withQueryString();

        // Statistics
        $todayCount = Appointment::where('service_category', 'boarding')->whereDate('appointment_date', $today)->count();
        $activeCount = Appointment::where('service_category', 'boarding')->whereIn('status', ['confirmed', 'checked_in'])->count();
        $completedCount = Appointment::where('service_category', 'boarding')->where('status', 'completed')->count();
        $totalSales = Appointment::where('service_category', 'boarding')->whereIn('status', ['checked_in', 'completed'])->sum('total_price');

        // Owners, Pets, Janitor / Kennel Staff for Modal
        $owners = Owner::with('pets')->orderBy('full_name')->get();
        $kennelStaff = Employee::where('status', 'active')
            ->where(function ($q) {
                $q->where('position', 'like', '%janitor%')
                  ->orWhere('position', 'like', '%kennel%')
                  ->orWhere('position', 'like', '%utility%');
            })
            ->orderBy('first_name')
            ->get();

        if ($kennelStaff->isEmpty()) {
            $kennelStaff = Employee::where('status', 'active')->orderBy('first_name')->get();
        }

        return view('cashier.boarding.index', compact(
            'boardings',
            'todayCount',
            'activeCount',
            'completedCount',
            'totalSales',
            'owners',
            'kennelStaff',
            'status',
            'dateFilter',
            'search'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_id' => 'required|exists:owners,id',
            'pet_id' => 'required|exists:pets,id',
            'assigned_employee_id' => 'nullable|exists:employees,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'boarding_days' => 'required|integer|min:1',
            'daily_rate' => 'required|numeric|min:0',
            'purpose_examination_notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $days = intval($validated['boarding_days']);
            $rate = floatval($validated['daily_rate']);
            $total = $days * $rate;

            $appointment = Appointment::create([
                'appointment_code' => Appointment::generateAppointmentCode(),
                'service_category' => 'boarding',
                'service_type' => 'Pet Boarding & Day Care',
                'owner_id' => $validated['owner_id'],
                'pet_id' => $validated['pet_id'],
                'booked_by' => auth()->id(),
                'assigned_employee_id' => $validated['assigned_employee_id'] ?? null,
                'boarding_days' => $days,
                'daily_rate' => $rate,
                'total_price' => $total,
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'],
                'purpose_examination_notes' => $validated['purpose_examination_notes'] ?? null,
                'status' => 'confirmed',
            ]);

            // Automatically create unpaid bill so cashier can directly process checkout
            $owner = Owner::find($validated['owner_id']);
            $assignedStaff = $appointment->assignedEmployee ? $appointment->assignedEmployee->full_name : 'Janitor / Kennel Staff';

            $bill = Bill::create([
                'invoice_no' => Bill::generateInvoiceNo(),
                'owner_id' => $validated['owner_id'],
                'pet_id' => $validated['pet_id'],
                'client_name' => $owner ? $owner->full_name : 'Client',
                'service_type' => 'veterinary',
                'subtotal' => $total,
                'total_amount' => $total,
                'payment_status' => 'unpaid',
                'transaction_date' => Carbon::now(),
                'notes' => "Pet Boarding ({$days} Days @ ₱" . number_format($rate, 2) . "/day) for {$appointment->appointment_code}. Caregiver: {$assignedStaff}",
            ]);

            BillItem::create([
                'bill_id' => $bill->id,
                'item_name' => "Pet Boarding: {$days} Day(s) @ ₱" . number_format($rate, 2) . "/day",
                'item_type' => 'service',
                'quantity' => $days,
                'unit_price' => $rate,
                'total_price' => $total,
            ]);

            DB::commit();
            return redirect()->back()->with('success', "Pet Boarding {$appointment->appointment_code} created successfully with Invoice {$bill->invoice_no}!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to save boarding: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,checked_in,completed,cancelled',
            'assigned_employee_id' => 'nullable|exists:employees,id',
            'boarding_days' => 'required|integer|min:1',
            'daily_rate' => 'required|numeric|min:0',
            'purpose_examination_notes' => 'nullable|string|max:1000',
        ]);

        $days = intval($validated['boarding_days']);
        $rate = floatval($validated['daily_rate']);
        $total = $days * $rate;

        $appointment->update([
            'status' => $validated['status'],
            'assigned_employee_id' => $validated['assigned_employee_id'] ?? null,
            'boarding_days' => $days,
            'daily_rate' => $rate,
            'total_price' => $total,
            'purpose_examination_notes' => $validated['purpose_examination_notes'] ?? null,
        ]);

        return redirect()->back()->with('success', "Boarding appointment {$appointment->appointment_code} updated successfully!");
    }

    public function destroy(Appointment $appointment)
    {
        $code = $appointment->appointment_code;
        $appointment->delete();
        return redirect()->back()->with('success', "Boarding appointment {$code} has been deleted.");
    }
}
