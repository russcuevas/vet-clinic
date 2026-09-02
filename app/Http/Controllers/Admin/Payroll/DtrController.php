<?php

namespace App\Http\Controllers\Admin\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\DtrRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DtrController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $selectedEmployeeId = $request->input('employee_id');

        // Automatic Timekeeping Attendance Sync:
        // Any active employee who has NOT logged time in for the selected date is AUTOMATICALLY marked as ABSENT,
        // UNLESS they have an approved Leave Application covering this date (marked as ON_LEAVE).
        $activeEmployees = Employee::where('status', 'active')->get();
        foreach ($activeEmployees as $emp) {
            $existingRecord = DtrRecord::where('employee_id', $emp->id)
                ->whereDate('record_date', $selectedDate)
                ->first();

            // Check if employee has an active approved leave application covering this date
            $leave = \App\Models\LeaveApplication::where('employee_id', $emp->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $selectedDate)
                ->whereDate('end_date', '>=', $selectedDate)
                ->first();

            if ($leave) {
                // If they have an approved leave, ensure status is 'on_leave', NOT absent
                if (!$existingRecord || $existingRecord->status === 'absent') {
                    DtrRecord::updateOrCreate(
                        ['employee_id' => $emp->id, 'record_date' => $selectedDate],
                        [
                            'status' => 'on_leave',
                            'time_in' => null,
                            'time_out' => null,
                            'regular_hours' => 0.00,
                            'ot_hours' => 0.00,
                            'late_minutes' => 0,
                            'undertime_minutes' => 0,
                            'notes' => 'Approved Leave: ' . ucfirst(str_replace('_', ' ', $leave->leave_type)),
                        ]
                    );
                }
            } elseif (!$existingRecord) {
                // No time-in entry logged and NO leave application filed -> AUTOMATIC ABSENT
                DtrRecord::create([
                    'employee_id' => $emp->id,
                    'record_date' => $selectedDate,
                    'status' => 'absent',
                    'time_in' => null,
                    'time_out' => null,
                    'regular_hours' => 0.00,
                    'ot_hours' => 0.00,
                    'late_minutes' => 0,
                    'undertime_minutes' => 0,
                    'notes' => 'Automatic Absent (No Time-In Entry / No Leave Filed)',
                ]);
            }
        }

        $query = DtrRecord::with('employee')->latest('record_date');

        if ($selectedDate) {
            $query->whereDate('record_date', $selectedDate);
        }

        if ($selectedEmployeeId) {
            $query->where('employee_id', $selectedEmployeeId);
        }

        $records = $query->paginate(20)->withQueryString();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        // Summary for selected date
        $summary = [
            'present' => DtrRecord::whereDate('record_date', $selectedDate)->where('status', 'present')->count(),
            'late' => DtrRecord::whereDate('record_date', $selectedDate)->where('status', 'late')->count(),
            'absent' => DtrRecord::whereDate('record_date', $selectedDate)->where('status', 'absent')->count(),
            'on_leave' => DtrRecord::whereDate('record_date', $selectedDate)->where('status', 'on_leave')->count(),
        ];

        return view('admin.payroll.dtr.index', compact('records', 'employees', 'selectedDate', 'selectedEmployeeId', 'summary'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'record_date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'ot_hours' => 'nullable|numeric|min:0',
            'status' => 'required|in:present,late,absent,on_leave,rest_day',
            'notes' => 'nullable|string|max:255',
        ]);

        $regularHours = 8.00;
        $lateMinutes = 0;
        $undertimeMinutes = 0;

        if ($validated['status'] === 'absent' || $validated['status'] === 'on_leave' || $validated['status'] === 'rest_day') {
            $regularHours = 0.00;
        } elseif (!empty($validated['time_in']) && !empty($validated['time_out'])) {
            $in = Carbon::createFromFormat('H:i', $validated['time_in']);
            $out = Carbon::createFromFormat('H:i', $validated['time_out']);
            
            // Standard shift start is 08:00
            $standardStart = Carbon::createFromFormat('H:i', '08:00');
            if ($in->gt($standardStart)) {
                $lateMinutes = $in->diffInMinutes($standardStart);
                if ($validated['status'] !== 'late') {
                    $validated['status'] = 'late';
                }
            }

            $diffMinutes = $in->diffInMinutes($out);
            if ($diffMinutes > 60) {
                // subtract 1 hour meal break
                $workedMinutes = $diffMinutes - 60;
                $regularHours = min(8.00, round($workedMinutes / 60, 2));
            } else {
                $regularHours = round($diffMinutes / 60, 2);
            }
        }

        $validated['regular_hours'] = $regularHours;
        $validated['late_minutes'] = $lateMinutes;
        $validated['undertime_minutes'] = $undertimeMinutes;
        $validated['ot_hours'] = $validated['ot_hours'] ?? 0.00;

        DtrRecord::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'record_date' => $validated['record_date']],
            $validated
        );

        return redirect()->back()->with('success', 'DTR log recorded successfully!');
    }

    public function batchGenerate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'default_status' => 'required|in:present,rest_day',
        ]);

        $date = $request->date;
        $status = $request->default_status;
        $employees = Employee::where('status', 'active')->get();

        $count = 0;
        foreach ($employees as $employee) {
            // Respect filed approved leaves: do not overwrite
            $leave = \App\Models\LeaveApplication::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->first();

            if ($leave) {
                continue;
            }

            $existing = DtrRecord::where('employee_id', $employee->id)->whereDate('record_date', $date)->first();
            if (!$existing || $existing->status === 'absent') {
                DtrRecord::updateOrCreate(
                    ['employee_id' => $employee->id, 'record_date' => $date],
                    [
                        'time_in' => $status === 'present' ? '08:00' : null,
                        'time_out' => $status === 'present' ? '17:00' : null,
                        'regular_hours' => $status === 'present' ? 8.00 : 0.00,
                        'late_minutes' => 0,
                        'undertime_minutes' => 0,
                        'ot_hours' => 0.00,
                        'status' => $status,
                        'notes' => 'Batch standard shift generated',
                    ]
                );
                $count++;
            }
        }

        return redirect()->back()->with('success', "Batch DTR records created for {$count} employees on {$date}.");
    }

    public function destroy(DtrRecord $dtr)
    {
        $dtr->delete();
        return redirect()->back()->with('success', 'DTR entry removed successfully.');
    }
}
