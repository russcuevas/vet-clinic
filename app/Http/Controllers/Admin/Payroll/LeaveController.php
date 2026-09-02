<?php

namespace App\Http\Controllers\Admin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\Deduction;
use App\Models\DtrRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public const ANNUAL_PAID_LEAVE_LIMIT = 5;

    public function index(Request $request)
    {
        $status = $request->query('status');
        $employeeId = $request->query('employee_id');

        $query = LeaveApplication::with(['employee', 'approver'])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $leaves = $query->paginate(15)->withQueryString();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        // Calculate leave stats for active employees
        $leaveStats = [];
        $currentYear = Carbon::now()->year;
        foreach ($employees as $emp) {
            $paidDaysUsed = LeaveApplication::getPaidLeaveDaysUsed($emp->id, $currentYear);
            $leaveStats[$emp->id] = [
                'used' => $paidDaysUsed,
                'remaining' => max(0, self::ANNUAL_PAID_LEAVE_LIMIT - $paidDaysUsed),
                'limit' => self::ANNUAL_PAID_LEAVE_LIMIT,
            ];
        }

        return view('admin.payroll.leaves.index', compact('leaves', 'employees', 'leaveStats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:sick_leave,vacation_leave,special_leave,emergency_leave',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $daysCount = $start->diffInDays($end) + 1;

        $currentYear = $start->year;
        $paidDaysUsed = LeaveApplication::getPaidLeaveDaysUsed($validated['employee_id'], $currentYear);
        $availablePaidDays = max(0, self::ANNUAL_PAID_LEAVE_LIMIT - $paidDaysUsed);

        $isPaid = true;
        $exceededLimit = false;

        if ($daysCount > $availablePaidDays) {
            $exceededLimit = true;
            // If they already exhausted their 5 days, entire leave is unpaid.
            // If partial, admin can decide or excess is flagged.
            if ($availablePaidDays === 0) {
                $isPaid = false;
            }
        }

        $leave = LeaveApplication::create([
            'employee_id' => $validated['employee_id'],
            'leave_type' => $validated['leave_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days_count' => $daysCount,
            'reason' => $validated['reason'],
            'status' => 'approved', // Direct filing by Admin defaults to approved
            'is_paid' => $isPaid,
            'exceeded_limit' => $exceededLimit,
            'approved_by' => auth()->id(),
            'admin_remarks' => $exceededLimit ? "Exceeded 5-day limit (Used: {$paidDaysUsed}/5 days)." : "Within 5-day limit.",
        ]);

        // Mark DTR dates as 'on_leave' or 'absent'
        $curDate = $start->copy();
        while ($curDate->lte($end)) {
            DtrRecord::updateOrCreate(
                ['employee_id' => $validated['employee_id'], 'record_date' => $curDate->toDateString()],
                [
                    'status' => $isPaid ? 'on_leave' : 'absent',
                    'regular_hours' => 0.00,
                    'notes' => ucfirst(str_replace('_', ' ', $validated['leave_type'])) . ($isPaid ? ' (Paid)' : ' (Unpaid)'),
                ]
            );
            $curDate->addDay();
        }

        // If unpaid due to exceeded limit, log into deductions database
        if (!$isPaid || $exceededLimit) {
            $employee = Employee::find($validated['employee_id']);
            $dailyRate = $employee->daily_rate ?: ($employee->basic_salary / 22);
            $excessDays = $daysCount - $availablePaidDays;
            if ($excessDays > 0) {
                $deductionAmount = round($dailyRate * $excessDays, 2);
                Deduction::create([
                    'employee_id' => $employee->id,
                    'deduction_type' => 'tardiness_absence',
                    'title' => "Unpaid Leave Deduction ({$excessDays} excess days)",
                    'total_amount' => $deductionAmount,
                    'monthly_amortization' => $deductionAmount,
                    'remaining_balance' => $deductionAmount,
                    'effective_date' => $validated['start_date'],
                    'status' => 'active',
                    'remarks' => "Exceeded 5-day annual leave limit. {$excessDays} days unpaid @ ₱" . number_format($dailyRate, 2) . "/day.",
                ]);
            }
        }

        return redirect()->route('admin.payroll.leaves.index')->with('success', 'Leave application recorded and processed successfully!');
    }

    public function updateStatus(Request $request, LeaveApplication $leave)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'is_paid' => 'nullable|boolean',
            'admin_remarks' => 'nullable|string|max:500',
        ]);

        $leave->status = $validated['status'];
        $leave->approved_by = auth()->id();
        if (isset($validated['is_paid'])) {
            $leave->is_paid = (bool) $validated['is_paid'];
        }
        if (isset($validated['admin_remarks'])) {
            $leave->admin_remarks = $validated['admin_remarks'];
        }
        $leave->save();

        return redirect()->back()->with('success', "Leave application status updated to {$leave->status}.");
    }

    public function destroy(LeaveApplication $leave)
    {
        $leave->delete();
        return redirect()->back()->with('success', 'Leave record deleted.');
    }
}
