<?php

namespace App\Http\Controllers\Admin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveAnnualLedger;
use App\Models\Deduction;
use App\Models\DtrRecord;
use App\Models\EmployeeIncentive;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $employeeId = $request->query('employee_id');
        $year = (int) $request->query('year', Carbon::now()->year);

        $query = LeaveApplication::with(['employee', 'approver'])
            ->whereYear('start_date', $year)
            ->latest('start_date');

        if ($status) {
            $query->where('status', $status);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $leaves = $query->paginate(15)->withQueryString();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        // Calculate leave stats for active employees for the selected year (5 VL, 5 SL, 2 SPL = 12 Annual Days)
        $leaveStats = [];
        $slMonetization = [];

        foreach ($employees as $emp) {
            $balances = LeaveApplication::getEmployeeLeaveBalances($emp->id, $year);
            $leaveStats[$emp->id] = $balances;

            $dailyRate = floatval($emp->daily_rate) ?: (floatval($emp->basic_salary) / 22);
            $unusedSl = $balances['sl']['remaining'];
            $slCashAmount = round($unusedSl * $dailyRate, 2);

            // Check if SL monetization has already been credited as an incentive for this year
            $alreadyCredited = EmployeeIncentive::where('employee_id', $emp->id)
                ->where('title', 'like', "%Sick Leave (SL) Conversion - {$year}%")
                ->exists();

            $slMonetization[$emp->id] = [
                'employee' => $emp,
                'sl_used' => $balances['sl']['used'],
                'sl_unused' => $unusedSl,
                'daily_rate' => $dailyRate,
                'cash_payout' => $slCashAmount,
                'is_credited' => $alreadyCredited,
            ];
        }

        // Fetch any saved permanent annual ledger archives for file keeping
        $archivedLedgers = LeaveAnnualLedger::where('year', $year)->get();
        $allArchivedYears = LeaveAnnualLedger::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');

        return view('admin.payroll.leaves.index', compact('leaves', 'employees', 'leaveStats', 'slMonetization', 'year', 'archivedLedgers', 'allArchivedYears'));
    }

    /**
     * Archive/snapshot all employee leave records for permanent file keeping.
     */
    public function archiveYear(Request $request)
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $notes = $request->input('notes', "Manual Annual Leave archive for Year {$year} created on " . now()->format('M d, Y h:i A'));

        $count = LeaveAnnualLedger::archiveYear($year, $notes);

        return redirect()->back()->with('success', "Annual leave ledger for Year {$year} successfully saved and archived for {$count} staff members!");
    }

    /**
     * Printable Annual Leave Ledger & File Keeping Audit Sheet.
     */
    public function printLedger(int $year)
    {
        // Auto-archive if not yet archived so data is present
        if (!LeaveAnnualLedger::where('year', $year)->exists()) {
            LeaveAnnualLedger::archiveYear($year);
        }

        $ledgers = LeaveAnnualLedger::where('year', $year)->orderBy('employee_name')->get();

        return view('admin.payroll.leaves.ledger_print', compact('ledgers', 'year'));
    }

    /**
     * Credit / Monetize unconsumed Sick Leave (SL) into Employee Incentives for year-end payroll.
     */
    public function convertSlToIncentive(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'year' => 'required|integer|min:2020|max:2099',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $year = $validated['year'];

        // Check if already credited
        $existing = EmployeeIncentive::where('employee_id', $employee->id)
            ->where('title', 'like', "%Sick Leave (SL) Conversion - {$year}%")
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', "SL Monetization for {$employee->full_name} ({$year}) has already been credited.");
        }

        $balances = LeaveApplication::getEmployeeLeaveBalances($employee->id, $year);
        $unusedSl = $balances['sl']['remaining'];

        if ($unusedSl <= 0) {
            return redirect()->back()->with('error', "{$employee->full_name} has no unused Sick Leave days to monetize in {$year}.");
        }

        $dailyRate = floatval($employee->daily_rate) ?: (floatval($employee->basic_salary) / 22);
        $totalPayout = round($unusedSl * $dailyRate, 2);

        EmployeeIncentive::create([
            'employee_id' => $employee->id,
            'title' => "Year-End Sick Leave (SL) Conversion - {$year} ({$unusedSl}d @ ₱" . number_format($dailyRate, 2) . "/day)",
            'calculation_basis' => 'fixed_per_unit',
            'rate_applied' => $dailyRate,
            'base_amount_or_count' => $unusedSl,
            'total_incentive' => $totalPayout,
            'date_earned' => Carbon::createFromDate($year, 12, 31)->toDateString(),
            'notes' => "Annual monetization of {$unusedSl} unconsumed Sick Leave days for year {$year}.",
        ]);

        return redirect()->back()->with('success', "Successfully credited ₱" . number_format($totalPayout, 2) . " SL Monetization ({$unusedSl} days) to {$employee->full_name}'s payroll incentives!");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:vacation_leave,sick_leave,special_leave',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $daysCount = $start->diffInDays($end) + 1;

        $currentYear = $start->year;
        $leaveType = $validated['leave_type'];
        $typeLimit = LeaveApplication::LEAVE_LIMITS[$leaveType] ?? 5;
        $typeShort = LeaveApplication::LEAVE_SHORT_LABELS[$leaveType] ?? 'Leave';

        $usedForType = LeaveApplication::getPaidLeaveDaysUsedByType($validated['employee_id'], $leaveType, $currentYear);
        $totalUsed = LeaveApplication::getPaidLeaveDaysUsed($validated['employee_id'], $currentYear);

        $availableForType = max(0, $typeLimit - $usedForType);
        $availableTotal = max(0, LeaveApplication::TOTAL_ANNUAL_LIMIT - $totalUsed);
        $effectiveAvailable = min($availableForType, $availableTotal);

        $isPaid = true;
        $exceededLimit = false;

        if ($daysCount > $effectiveAvailable) {
            $exceededLimit = true;
            if ($effectiveAvailable === 0) {
                $isPaid = false;
            }
        }

        $excessDays = max(0, $daysCount - $effectiveAvailable);

        if ($exceededLimit) {
            $adminRemarks = "Exceeded {$typeShort} quota (Used: {$usedForType}/{$typeLimit} {$typeShort}, Total: {$totalUsed}/" . LeaveApplication::TOTAL_ANNUAL_LIMIT . " Annual). {$excessDays} day(s) marked unpaid.";
        } else {
            $newTypeUsed = $usedForType + $daysCount;
            $newTotalUsed = $totalUsed + $daysCount;
            $adminRemarks = "Within {$typeShort} quota (Used: {$newTypeUsed}/{$typeLimit} {$typeShort}, Total: {$newTotalUsed}/" . LeaveApplication::TOTAL_ANNUAL_LIMIT . " Annual).";
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
            'admin_remarks' => $adminRemarks,
        ]);

        // Mark DTR dates as 'on_leave' or 'absent'
        $curDate = $start->copy();
        while ($curDate->lte($end)) {
            DtrRecord::updateOrCreate(
                ['employee_id' => $validated['employee_id'], 'record_date' => $curDate->toDateString()],
                [
                    'status' => $isPaid ? 'on_leave' : 'absent',
                    'regular_hours' => 0.00,
                    'notes' => (LeaveApplication::LEAVE_LABELS[$validated['leave_type']] ?? ucfirst(str_replace('_', ' ', $validated['leave_type']))) . ($isPaid ? ' (Paid)' : ' (Unpaid)'),
                ]
            );
            $curDate->addDay();
        }

        // If unpaid due to exceeded limit, log into deductions database
        if (!$isPaid || $exceededLimit) {
            $employee = Employee::find($validated['employee_id']);
            $dailyRate = $employee->daily_rate ?: ($employee->basic_salary / 22);
            if ($excessDays > 0) {
                $deductionAmount = round($dailyRate * $excessDays, 2);
                Deduction::create([
                    'employee_id' => $employee->id,
                    'deduction_type' => 'tardiness_absence',
                    'title' => "Unpaid {$typeShort} Deduction ({$excessDays} excess days)",
                    'total_amount' => $deductionAmount,
                    'monthly_amortization' => $deductionAmount,
                    'remaining_balance' => $deductionAmount,
                    'effective_date' => $validated['start_date'],
                    'status' => 'active',
                    'remarks' => "Exceeded {$typeShort} quota ({$typeLimit}d max) / 12d annual quota. {$excessDays} days unpaid @ ₱" . number_format($dailyRate, 2) . "/day.",
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
