<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\DtrRecord;
use App\Models\LeaveApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DtrController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        // Check if any employee has approved leave today to auto-sync status
        foreach ($employees as $emp) {
            $leave = LeaveApplication::where('employee_id', $emp->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->first();

            if ($leave) {
                $existingRecord = DtrRecord::where('employee_id', $emp->id)
                    ->whereDate('record_date', $today)
                    ->first();

                if (!$existingRecord) {
                    DtrRecord::create([
                        'employee_id' => $emp->id,
                        'record_date' => $today,
                        'status' => 'on_leave',
                        'time_in' => null,
                        'time_out' => null,
                        'regular_hours' => 0.00,
                        'notes' => 'Approved Leave: ' . ucfirst(str_replace('_', ' ', $leave->leave_type)),
                    ]);
                }
            }
        }

        $todayRecords = DtrRecord::with('employee')
            ->whereDate('record_date', $today)
            ->get()
            ->keyBy('employee_id');

        $stats = [
            'total' => $employees->count(),
            'present' => $todayRecords->whereIn('status', ['present', 'late'])->count(),
            'on_duty' => $todayRecords->filter(fn($r) => !empty($r->time_in) && empty($r->time_out))->count(),
            'completed' => $todayRecords->filter(fn($r) => !empty($r->time_in) && !empty($r->time_out))->count(),
            'late' => $todayRecords->where('late_minutes', '>', 0)->count(),
            'on_leave' => $todayRecords->where('status', 'on_leave')->count(),
            'rest_day' => $todayRecords->where('status', 'rest_day')->count(),
        ];

        return view('receptionist.dtr.index', compact('employees', 'todayRecords', 'today', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'action_type' => 'required|in:clock_in,clock_out,rest_day',
            'ot_approved' => 'nullable|boolean',
            'notes' => 'nullable|string|max:255',
        ]);

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();
        $currentTimeStr = $now->format('H:i');
        $employee = Employee::findOrFail($validated['employee_id']);

        $shiftStartStr = $employee->shift_start ?: '09:00';
        $shiftEndStr = $employee->shift_end ?: '18:00';

        $existing = DtrRecord::where('employee_id', $employee->id)
            ->whereDate('record_date', $today)
            ->first();

        // -------------------------------------------------------------
        // ACTION: CLOCK IN (TIME IN)
        // -------------------------------------------------------------
        if ($validated['action_type'] === 'clock_in') {
            $shiftStart = Carbon::createFromFormat('H:i', $shiftStartStr);
            $in = Carbon::createFromFormat('H:i', $currentTimeStr);

            $lateMinutes = 0;
            $status = 'present';

            if ($in->gt($shiftStart)) {
                $lateMinutes = (int) abs($shiftStart->diffInMinutes($in, true));
                $status = 'late';
            }

            $data = [
                'employee_id' => $employee->id,
                'record_date' => $today,
                'time_in' => $currentTimeStr,
                'time_out' => $existing ? $existing->time_out : null,
                'late_minutes' => $lateMinutes,
                'status' => $status,
                'notes' => $validated['notes'] ?? ($existing ? $existing->notes : null),
            ];

            DtrRecord::updateOrCreate(
                ['employee_id' => $employee->id, 'record_date' => $today],
                $data
            );

            $formattedTime = $now->format('g:i A');
            $lateText = $lateMinutes > 0 ? " (Late by {$lateMinutes} mins)" : " (On Time)";
            return redirect()->back()->with('success', "✅ Time In recorded for {$employee->full_name} at {$formattedTime}{$lateText}!");
        }

        // -------------------------------------------------------------
        // ACTION: CLOCK OUT (TIME OUT)
        // -------------------------------------------------------------
        if ($validated['action_type'] === 'clock_out') {
            $timeInStr = $existing && $existing->time_in ? $existing->time_in : null;

            if (!$timeInStr) {
                return redirect()->back()->with('error', "❌ Cannot Clock Out: {$employee->full_name} has not Clocked In yet today.");
            }

            $timeInCarbon = Carbon::createFromFormat('H:i', Carbon::parse($timeInStr)->format('H:i'));
            $out = Carbon::createFromFormat('H:i', $currentTimeStr);

            // Rule 1: 2-Hour Time Out Lockout check
            $minutesSinceIn = (int) $timeInCarbon->diffInMinutes($out, false);
            if ($minutesSinceIn < 120 && $minutesSinceIn >= 0) {
                $unlockTime = $timeInCarbon->copy()->addHours(2);
                $minsRemaining = 120 - $minutesSinceIn;
                return redirect()->back()->with('error', "⏳ Time Out is locked for 2 hours after Time In. Available at " . $unlockTime->format('g:i A') . " ({$minsRemaining} minutes remaining).");
            }

            $shiftEnd = Carbon::createFromFormat('H:i', $shiftEndStr);

            $undertimeMinutes = 0;
            if ($out->lt($shiftEnd)) {
                $undertimeMinutes = (int) abs($shiftEnd->diffInMinutes($out, true));
            }

            // Rule 2: Overtime Calculation (30+ mins past shift end AND approved by Admin/Manager)
            $otHours = 0.00;
            $otApproved = $request->boolean('ot_approved');
            $otNote = '';

            if ($out->gt($shiftEnd)) {
                $pastShiftMins = (int) abs($shiftEnd->diffInMinutes($out, true));
                // Only qualify for OT if worked 30 mins or more past shift
                if ($pastShiftMins >= 30) {
                    if ($otApproved) {
                        $otHours = round($pastShiftMins / 60, 2);
                        $otNote = " • OT Approved by Admin/Manager ({$otHours} hrs)";
                    } else {
                        $otNote = " • OT Unapproved ({$pastShiftMins} mins beyond shift not credited)";
                    }
                }
            }

            $diffMinutes = (int) abs($timeInCarbon->diffInMinutes($out, true));
            if ($diffMinutes >= 300) {
                $workedMinutes = max(0, $diffMinutes - 60);
                $regularHours = min(8.00, round($workedMinutes / 60, 2));
            } else {
                $regularHours = round($diffMinutes / 60, 2);
            }

            $status = $existing && $existing->status === 'late' ? 'late' : 'present';
            $finalNotes = ($validated['notes'] ? $validated['notes'] : ($existing ? $existing->notes : '')) . $otNote;

            DtrRecord::updateOrCreate(
                ['employee_id' => $employee->id, 'record_date' => $today],
                [
                    'time_in' => $timeInStr,
                    'time_out' => $currentTimeStr,
                    'regular_hours' => $regularHours,
                    'undertime_minutes' => $undertimeMinutes,
                    'ot_hours' => $otHours,
                    'status' => $status,
                    'notes' => trim($finalNotes),
                ]
            );

            $formattedTime = $now->format('g:i A');
            $otMsg = $otHours > 0 ? " with {$otHours} hrs Overtime (Approved)" : "";
            return redirect()->back()->with('success', "🏁 Time Out recorded for {$employee->full_name} at {$formattedTime}! Worked: {$regularHours} hrs{$otMsg}.");
        }

        // -------------------------------------------------------------
        // ACTION: REST DAY
        // -------------------------------------------------------------
        if ($validated['action_type'] === 'rest_day') {
            DtrRecord::updateOrCreate(
                ['employee_id' => $employee->id, 'record_date' => $today],
                [
                    'status' => 'rest_day',
                    'time_in' => null,
                    'time_out' => null,
                    'regular_hours' => 0.00,
                    'late_minutes' => 0,
                    'undertime_minutes' => 0,
                    'ot_hours' => 0.00,
                    'notes' => 'Scheduled Rest Day',
                ]
            );

            return redirect()->back()->with('success', "☕ {$employee->full_name} marked as Scheduled Rest Day for today.");
        }

        return redirect()->back();
    }
}
