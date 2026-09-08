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

        $dayRecords = DtrRecord::with('employee')
            ->whereDate('record_date', $selectedDate)
            ->get();

        $summary = [
            'present' => $dayRecords->whereIn('status', ['present', 'late'])->count(),
            'late' => $dayRecords->where('late_minutes', '>', 0)->count(),
            'absent' => $dayRecords->where('status', 'absent')->count(),
            'on_leave' => $dayRecords->where('status', 'on_leave')->count(),
        ];

        return view('admin.payroll.dtr.index', compact('records', 'dayRecords', 'employees', 'selectedDate', 'selectedEmployeeId', 'summary'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'record_date' => 'required|date',
            'time_in' => 'nullable|string',
            'time_out' => 'nullable|string',
            'ot_hours' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:present,late,absent,on_leave,rest_day',
            'undertime_reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:255',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $shiftStartStr = $employee->shift_start ?: '09:00';
        $shiftEndStr = $employee->shift_end ?: '18:00';

        $timeIn = !empty($validated['time_in']) ? Carbon::parse($validated['time_in'])->format('H:i') : null;
        $timeOut = !empty($validated['time_out']) ? Carbon::parse($validated['time_out'])->format('H:i') : null;

        $regularHours = 0.00;
        $lateMinutes = 0;
        $undertimeMinutes = 0;
        $otHours = !empty($validated['ot_hours']) ? floatval($validated['ot_hours']) : 0.00;
        $status = $validated['status'] ?? 'present';

        if ($status === 'absent' || $status === 'on_leave' || $status === 'rest_day') {
            $timeIn = null;
            $timeOut = null;
            $regularHours = 0.00;
            $lateMinutes = 0;
            $undertimeMinutes = 0;
            $otHours = 0.00;
        } elseif ($timeIn) {
            $in = Carbon::createFromFormat('H:i', $timeIn);
            $shiftStart = Carbon::createFromFormat('H:i', $shiftStartStr);
            $shiftEnd = Carbon::createFromFormat('H:i', $shiftEndStr);

            // 1. Calculate Tardiness (Late)
            if ($in->gt($shiftStart)) {
                $lateMinutes = (int) abs($shiftStart->diffInMinutes($in, true));
                $status = 'late';
            }

            // 2. If Time Out is provided, calculate duty hours, undertime, and overtime
            if ($timeOut) {
                $out = Carbon::createFromFormat('H:i', $timeOut);

                // Undertime calculation (left before shift end)
                if ($out->lt($shiftEnd)) {
                    $undertimeMinutes = (int) abs($shiftEnd->diffInMinutes($out, true));
                }

                // Overtime calculation (stayed past shift end)
                if ($out->gt($shiftEnd) && $otHours == 0) {
                    $otHours = round(abs($shiftEnd->diffInMinutes($out, true)) / 60, 2);
                }

                // Duty hours worked (minus 1 hour lunch break if shift is 5+ hours)
                $diffMinutes = (int) abs($in->diffInMinutes($out, true));
                if ($diffMinutes >= 300) {
                    $workedMinutes = max(0, $diffMinutes - 60);
                    $regularHours = min(8.00, round($workedMinutes / 60, 2));
                } else {
                    $regularHours = round($diffMinutes / 60, 2);
                }
            } else {
                // Time In logged, awaiting Time Out
                $regularHours = 0.00;
            }
        }

        $data = [
            'employee_id' => $employee->id,
            'record_date' => $validated['record_date'],
            'time_in' => $timeIn,
            'time_out' => $timeOut,
            'regular_hours' => $regularHours,
            'late_minutes' => $lateMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'undertime_reason' => $validated['undertime_reason'] ?? $request->input('undertime_reason', null),
            'ot_hours' => $otHours,
            'status' => $status,
            'notes' => $validated['notes'] ?? null,
        ];

        DtrRecord::updateOrCreate(
            ['employee_id' => $employee->id, 'record_date' => $validated['record_date']],
            $data
        );

        return redirect()->back()->with('success', "DTR log for {$employee->full_name} saved successfully!");
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
                $shiftStart = $employee->shift_start ?: '09:00';
                $shiftEnd = $employee->shift_end ?: '18:00';

                DtrRecord::updateOrCreate(
                    ['employee_id' => $employee->id, 'record_date' => $date],
                    [
                        'time_in' => $status === 'present' ? $shiftStart : null,
                        'time_out' => $status === 'present' ? $shiftEnd : null,
                        'regular_hours' => $status === 'present' ? 8.00 : 0.00,
                        'late_minutes' => 0,
                        'undertime_minutes' => 0,
                        'ot_hours' => 0.00,
                        'status' => $status,
                        'notes' => 'Batch schedule generated based on assigned shift (' . $shiftStart . ' - ' . $shiftEnd . ')',
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
