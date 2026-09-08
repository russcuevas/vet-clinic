<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\DtrRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index()
    {
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        // Recent leave applications filed recently (last 14 days)
        $recentLeaves = LeaveApplication::with('employee')
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('receptionist.leaves.index', compact('employees', 'recentLeaves'));
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

        $employee = Employee::findOrFail($validated['employee_id']);
        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $daysCount = $start->diffInDays($end) + 1;

        $leaveType = $validated['leave_type'];
        $typeShort = LeaveApplication::LEAVE_SHORT_LABELS[$leaveType] ?? 'Leave';

        $leave = LeaveApplication::create([
            'employee_id' => $employee->id,
            'leave_type' => $leaveType,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days_count' => $daysCount,
            'reason' => $validated['reason'],
            'status' => 'approved',
            'is_paid' => true,
            'exceeded_limit' => false,
            'approved_by' => auth()->id(),
            'admin_remarks' => "Filed by Reception Desk (" . auth()->user()->name . ") on " . now()->format('M d, Y h:i A'),
        ]);

        // Auto-mark corresponding DTR dates as 'on_leave'
        $curDate = $start->copy();
        while ($curDate->lte($end)) {
            DtrRecord::updateOrCreate(
                ['employee_id' => $employee->id, 'record_date' => $curDate->toDateString()],
                [
                    'status' => 'on_leave',
                    'time_in' => null,
                    'time_out' => null,
                    'regular_hours' => 0.00,
                    'notes' => (LeaveApplication::LEAVE_LABELS[$leaveType] ?? ucfirst(str_replace('_', ' ', $leaveType))) . ' (Filed by Reception)',
                ]
            );
            $curDate->addDay();
        }

        return redirect()->route('receptionist.leaves.index')->with('success', "🎉 Leave Application ({$daysCount} day" . ($daysCount > 1 ? 's' : '') . " of {$typeShort}) for {$employee->full_name} successfully recorded!");
    }
}
