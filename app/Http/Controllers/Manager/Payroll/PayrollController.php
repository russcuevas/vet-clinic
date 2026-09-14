<?php

namespace App\Http\Controllers\Manager\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\DtrRecord;
use App\Models\Deduction;
use App\Models\EmployeeIncentive;
use App\Models\PayrollPeriod;
use App\Models\PayrollRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type'); // '15_days', '30_days', 'annual'
        $query = PayrollPeriod::withCount('records')->latest('start_date');

        if ($type && in_array($type, ['15_days', '30_days'])) {
            $query->where('period_type', $type);
        }

        $periods = $query->paginate(15)->withQueryString();

        return view('manager.payroll.records.index', compact('periods', 'type'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'period_name' => 'required|string|max:150',
            'period_type' => 'required|in:15_days,30_days',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'payout_date' => 'required|date',
            'remarks' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $period = PayrollPeriod::create($validated);

            $employees = Employee::where('status', 'active')->get();
            $factor = ($validated['period_type'] === '15_days') ? 0.5 : 1.0;

            foreach ($employees as $emp) {
                // 1. Basic & Regular Pay
                $monthlySalary = floatval($emp->basic_salary);
                $isVet = stripos($emp->position, 'vet') !== false || stripos($emp->department ?? '', 'vet') !== false;
                $divisor = $emp->divisor_days ?: ($isVet ? 22 : 26);
                $dailyRate = floatval($emp->daily_rate) ?: ($monthlySalary / $divisor);
                $hourlyRate = floatval($emp->hourly_rate) ?: ($dailyRate / 8);

                // Base regular salary for this period
                $basePeriodPay = round($monthlySalary * $factor, 2);

                // Multipliers from Database Settings (Admin / Manager configurable)
                $holidayMultiplier = \App\Models\PayrollSetting::getMultiplier('holiday_multiplier', 2.00);
                $specialHolidayMultiplier = \App\Models\PayrollSetting::getMultiplier('special_holiday_multiplier', 1.30);
                $restDayMultiplier = \App\Models\PayrollSetting::getMultiplier('rest_day_multiplier', 1.30);
                $otMultiplier = \App\Models\PayrollSetting::getMultiplier('overtime_multiplier', 1.30);

                // 2. DTR Attendance, Holidays, Rest Days & Overtime
                $dtrs = DtrRecord::where('employee_id', $emp->id)
                    ->whereBetween('record_date', [$validated['start_date'], $validated['end_date']])
                    ->get();

                $daysWorked = $dtrs->whereIn('status', ['present', 'late', 'holiday', 'regular_holiday', 'special_holiday', 'overtime', 'rest_day'])->count();
                
                // Overtime calculation (x1.3 default multiplier)
                $otHours = $dtrs->sum('ot_hours');
                $otPay = round($otHours * $hourlyRate * $otMultiplier, 2);

                // Regular Holiday calculation (x2.0 default multiplier)
                $holidayDays = $dtrs->whereIn('status', ['holiday', 'regular_holiday'])->count();
                $holidayPay = round($holidayDays * $dailyRate * $holidayMultiplier, 2);

                // Special Holiday calculation (x1.3 default multiplier)
                $specialHolidayDays = $dtrs->where('status', 'special_holiday')->count();
                $specialHolidayPay = round($specialHolidayDays * $dailyRate * $specialHolidayMultiplier, 2);

                // Rest Day Duty calculation (x1.3 default multiplier)
                $restDayDays = $dtrs->where('status', 'rest_day')->count();
                $restDayPay = round($restDayDays * $dailyRate * $restDayMultiplier, 2);

                // Absences / Tardiness / Undertime
                $absentDays = $dtrs->where('status', 'absent')->count();
                $lateMinutes = $dtrs->sum('late_minutes');
                $undertimeMinutes = $dtrs->sum('undertime_minutes');
                $tardinessDeduction = round(($dailyRate * $absentDays) + (($hourlyRate / 60) * ($lateMinutes + $undertimeMinutes)), 2);

                // If no DTR records logged, default days worked based on period
                if ($dtrs->isEmpty()) {
                    $daysWorked = ($validated['period_type'] === '15_days') ? 11 : 22;
                }
                $regularPay = $basePeriodPay;

                // 3. Incentives earned in this date range
                $incentivesTotal = floatval(EmployeeIncentive::where('employee_id', $emp->id)
                    ->whereBetween('date_earned', [$validated['start_date'], $validated['end_date']])
                    ->sum('total_incentive'));

                // 4. Gross Pay (Regular + OT + Holiday + Special Holiday + Rest Day + Incentives)
                $grossPay = round($regularPay + $otPay + $holidayPay + $specialHolidayPay + $restDayPay + $incentivesTotal, 2);

                // 5. Deductions
                // A. Government Contributions (half if 15 days, full if 30 days)
                $sss = round(min(1350.00, $monthlySalary * 0.045) * $factor, 2);
                $philhealth = round(($monthlySalary * 0.025) * $factor, 2);
                $pagibig = round(100.00 * $factor, 2);
                $tax = 0.00;
                if ($monthlySalary > 20833) {
                    $tax = round(($monthlySalary - 20833) * 0.15 * $factor, 2);
                }

                // B. Active Loans & Cash Advances
                $loanDeduction = 0.00;
                $caDeduction = 0.00;
                $activeDeductions = Deduction::where('employee_id', $emp->id)
                    ->where('status', 'active')
                    ->where('remaining_balance', '>', 0)
                    ->get();

                foreach ($activeDeductions as $ded) {
                    $amort = floatval($ded->monthly_amortization) * $factor;
                    $deductNow = min($ded->remaining_balance, $amort);

                    if ($ded->deduction_type === 'loan') {
                        $loanDeduction += $deductNow;
                    } elseif ($ded->deduction_type === 'cash_advance') {
                        $caDeduction += $deductNow;
                    }

                    // Decrement remaining balance
                    $ded->remaining_balance = max(0, $ded->remaining_balance - $deductNow);
                    if ($ded->remaining_balance <= 0) {
                        $ded->status = 'completed';
                    }
                    $ded->save();
                }

                $absenceTardiness = $dtrs->isNotEmpty() ? $tardinessDeduction : 0;

                $totalDeductions = round(
                    $sss + $philhealth + $pagibig + $tax + $loanDeduction + $caDeduction + $absenceTardiness,
                    2
                );

                $netPay = max(0, round($grossPay - $totalDeductions, 2));

                PayrollRecord::create([
                    'payroll_period_id' => $period->id,
                    'employee_id' => $emp->id,
                    'basic_salary' => $monthlySalary,
                    'days_worked' => $daysWorked,
                    'regular_pay' => $regularPay,
                    'ot_hours' => $otHours,
                    'ot_pay' => $otPay,
                    'holiday_days' => $holidayDays,
                    'holiday_pay' => $holidayPay,
                    'special_holiday_days' => $specialHolidayDays,
                    'special_holiday_pay' => $specialHolidayPay,
                    'rest_day_days' => $restDayDays,
                    'rest_day_pay' => $restDayPay,
                    'incentives_total' => $incentivesTotal,
                    'gross_pay' => $grossPay,
                    'sss_deduction' => $sss,
                    'philhealth_deduction' => $philhealth,
                    'pagibig_deduction' => $pagibig,
                    'tax_deduction' => $tax,
                    'loan_deduction' => $loanDeduction,
                    'cash_advance_deduction' => $caDeduction,
                    'absence_tardiness_deduction' => $absenceTardiness,
                    'total_deductions' => $totalDeductions,
                    'net_pay' => $netPay,
                    'payment_status' => 'unpaid',
                ]);
            }

            DB::commit();
            return redirect()->route('manager.payroll.periods.show', $period)->with('success', "Payroll generated successfully for {$period->period_name}!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to generate payroll: ' . $e->getMessage());
        }
    }

    public function show(PayrollPeriod $period)
    {
        $period->load(['records.employee']);
        return view('manager.payroll.records.show', compact('period'));
    }

    public function updateRecord(Request $request, PayrollRecord $record)
    {
        $validated = $request->validate([
            'days_worked' => 'required|numeric|min:0',
            'regular_pay' => 'required|numeric|min:0',
            'ot_hours' => 'required|numeric|min:0',
            'ot_pay' => 'required|numeric|min:0',
            'holiday_days' => 'nullable|numeric|min:0',
            'holiday_pay' => 'nullable|numeric|min:0',
            'special_holiday_days' => 'nullable|numeric|min:0',
            'special_holiday_pay' => 'nullable|numeric|min:0',
            'rest_day_days' => 'nullable|numeric|min:0',
            'rest_day_pay' => 'nullable|numeric|min:0',
            'incentives_total' => 'required|numeric|min:0',
            'gross_pay' => 'required|numeric|min:0',
            'sss_deduction' => 'required|numeric|min:0',
            'philhealth_deduction' => 'required|numeric|min:0',
            'pagibig_deduction' => 'required|numeric|min:0',
            'tax_deduction' => 'required|numeric|min:0',
            'loan_deduction' => 'required|numeric|min:0',
            'cash_advance_deduction' => 'required|numeric|min:0',
            'absence_tardiness_deduction' => 'required|numeric|min:0',
            'total_deductions' => 'required|numeric|min:0',
            'net_pay' => 'required|numeric|min:0',
            'payment_status' => 'required|in:unpaid,paid',
            'notes' => 'nullable|string|max:500',
        ]);

        $record->update($validated);

        return redirect()->back()->with('success', "Payroll record for {$record->employee->full_name} updated successfully.");
    }

    public function printPayslip(PayrollRecord $record)
    {
        $record->load(['payrollPeriod', 'employee']);
        $incentives = EmployeeIncentive::where('employee_id', $record->employee_id)
            ->whereBetween('date_earned', [$record->payrollPeriod->start_date, $record->payrollPeriod->end_date])
            ->get();

        $dtrs = DtrRecord::where('employee_id', $record->employee_id)
            ->whereBetween('record_date', [$record->payrollPeriod->start_date, $record->payrollPeriod->end_date])
            ->get();

        $presentDays = $dtrs->whereIn('status', ['present', 'late'])->count();
        $absentDays = $dtrs->where('status', 'absent')->count();
        $lateMins = $dtrs->sum('late_minutes');
        $onLeaveDays = $dtrs->where('status', 'on_leave')->count();
        $holidayDays = $dtrs->whereIn('status', ['holiday', 'regular_holiday'])->count();
        $specialHolidayDays = $dtrs->where('status', 'special_holiday')->count();
        $restDayDays = $dtrs->where('status', 'rest_day')->count();
        $overtimeDays = $dtrs->where('status', 'overtime')->count();
        $offDutyDays = $dtrs->where('status', 'off_duty')->count();

        // If no DTR records in range, calculate based on days worked
        if ($dtrs->isEmpty()) {
            $expectedDays = ($record->payrollPeriod->period_type === '15_days') ? 11 : 22;
            $presentDays = floatval($record->days_worked);
            $absentDays = max(0, $expectedDays - $presentDays);
        }

        return view('manager.payroll.records.payslip_print', compact(
            'record',
            'incentives',
            'presentDays',
            'absentDays',
            'lateMins',
            'onLeaveDays',
            'holidayDays',
            'specialHolidayDays',
            'restDayDays',
            'overtimeDays',
            'offDutyDays'
        ));
    }

    public function printSummary(PayrollPeriod $period)
    {
        $period->load(['records.employee']);
        return view('manager.payroll.records.summary_print', compact('period'));
    }

    public function annual(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        // Fetch all records for the year grouped by employee
        $records = PayrollRecord::whereHas('payrollPeriod', function ($q) use ($year) {
            $q->whereYear('start_date', $year);
        })->with(['employee', 'payrollPeriod'])->get();

        $employees = Employee::where('status', 'active')->get();

        $annualData = [];
        foreach ($employees as $emp) {
            $empRecords = $records->where('employee_id', $emp->id);
            $annualData[] = [
                'employee' => $emp,
                'total_gross' => $empRecords->sum('gross_pay'),
                'total_incentives' => $empRecords->sum('incentives_total'),
                'total_deductions' => $empRecords->sum('total_deductions'),
                'total_net' => $empRecords->sum('net_pay'),
                'periods_count' => $empRecords->count(),
            ];
        }

        return view('manager.payroll.annual', compact('annualData', 'year'));
    }

    public function destroyPeriod(PayrollPeriod $period)
    {
        $period->delete();
        return redirect()->route('manager.payroll.periods.index')->with('success', 'Payroll period and records removed.');
    }
}
