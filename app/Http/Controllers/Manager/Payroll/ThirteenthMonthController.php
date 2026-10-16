<?php

namespace App\Http\Controllers\Manager\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ThirteenthMonthPay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThirteenthMonthController extends Controller
{
    /**
     * Display 13th Month Pay & Monetized SL Dashboard / Register.
     */
    public function index(Request $request)
    {
        $year = intval($request->input('year', Carbon::now()->year));
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        $records = ThirteenthMonthPay::where('year', $year)->with('employee')->get()->keyBy('employee_id');

        $staffList = [];
        $total13thPool = 0.00;
        $totalSlPool = 0.00;
        $totalNetPayout = 0.00;

        foreach ($employees as $emp) {
            if ($records->has($emp->id)) {
                $record = $records->get($emp->id);
                $isSaved = true;
            } else {
                $computed = ThirteenthMonthPay::computeDefaults($emp, $year);
                $record = new ThirteenthMonthPay($computed);
                $record->employee = $emp;
                $isSaved = false;
            }

            $staffList[] = [
                'employee' => $emp,
                'record' => $record,
                'is_saved' => $isSaved,
            ];

            $total13thPool += floatval($record->thirteenth_month_pay);
            $totalSlPool += floatval($record->sl_monetized_amount);
            $totalNetPayout += floatval($record->total_payout);
        }

        return view('manager.payroll.thirteenth_month.index', compact(
            'staffList',
            'year',
            'total13thPool',
            'totalSlPool',
            'totalNetPayout'
        ));
    }

    /**
     * Generate / Save 13th Month Pay & Monetized SL records.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2099',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $year = intval($validated['year']);

        DB::beginTransaction();
        try {
            if (!empty($validated['employee_id'])) {
                $employees = Employee::where('id', $validated['employee_id'])->get();
            } else {
                $employees = Employee::where('status', 'active')->get();
            }

            $count = 0;
            foreach ($employees as $emp) {
                $defaults = ThirteenthMonthPay::computeDefaults($emp, $year);
                ThirteenthMonthPay::updateOrCreate(
                    [
                        'employee_id' => $emp->id,
                        'year' => $year,
                    ],
                    $defaults
                );
                $count++;
            }

            DB::commit();
            return redirect()->route('manager.payroll.thirteenth_month.index', ['year' => $year])
                ->with('success', "Successfully generated 13th Month Pay & Monetized SL for {$count} employee(s) for Year {$year}!");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to generate 13th month pay: ' . $e->getMessage());
        }
    }

    /**
     * Update/Override 13th Month Pay & Monetized SL.
     */
    public function update(Request $request, ThirteenthMonthPay $record)
    {
        $validated = $request->validate([
            'daily_rate' => 'required|numeric|min:0',
            'monthly_days' => 'nullable|array',
            'monthly_days.*' => 'nullable|numeric|min:0|max:31',
            'thirteenth_month_pay' => 'nullable|numeric|min:0',
            'sl_days_remaining' => 'nullable|numeric|min:0|max:30',
            'sl_monetized_amount' => 'nullable|numeric|min:0',
            'other_bonus' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'total_payout' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:unpaid,paid',
            'payout_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $dailyRate = floatval($validated['daily_rate']);
        $existingBreakdown = $record->monthly_breakdown ?? [];

        $totalBasicEarned = 0.00;
        $updatedBreakdown = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthDate = Carbon::createFromDate($record->year, $m, 1);
            $monthName = $monthDate->format('F');
            $monthShort = $monthDate->format('M');

            $days = isset($validated['monthly_days'][$m])
                ? floatval($validated['monthly_days'][$m])
                : (isset($existingBreakdown[$m - 1]['days_worked']) ? floatval($existingBreakdown[$m - 1]['days_worked']) : 22);

            $monthAmount = round($days * $dailyRate, 2);
            $totalBasicEarned += $monthAmount;

            $updatedBreakdown[] = [
                'month' => $m,
                'month_name' => $monthName,
                'month_short' => $monthShort,
                'days_worked' => $days,
                'rate' => $dailyRate,
                'amount' => $monthAmount,
            ];
        }

        $calc13th = round($totalBasicEarned / 12, 2);
        $thirteenthMonthPay = (isset($validated['thirteenth_month_pay']) && $validated['thirteenth_month_pay'] !== null)
            ? floatval($validated['thirteenth_month_pay'])
            : $calc13th;

        $slRemaining = floatval($validated['sl_days_remaining'] ?? $record->sl_days_remaining ?? 0);
        $calcSlAmount = round($slRemaining * $dailyRate, 2);
        $slMonetizedAmount = (isset($validated['sl_monetized_amount']) && $validated['sl_monetized_amount'] !== null)
            ? floatval($validated['sl_monetized_amount'])
            : $calcSlAmount;

        $otherBonus = floatval($validated['other_bonus'] ?? 0);
        $deductions = floatval($validated['deductions'] ?? 0);

        $calcTotalPayout = max(0, round($thirteenthMonthPay + $slMonetizedAmount + $otherBonus - $deductions, 2));
        $totalPayout = (isset($validated['total_payout']) && $validated['total_payout'] !== null)
            ? floatval($validated['total_payout'])
            : $calcTotalPayout;

        $record->update([
            'daily_rate' => $dailyRate,
            'monthly_breakdown' => $updatedBreakdown,
            'total_basic_earned' => $totalBasicEarned,
            'thirteenth_month_pay' => $thirteenthMonthPay,
            'sl_days_remaining' => $slRemaining,
            'sl_monetized_amount' => $slMonetizedAmount,
            'other_bonus' => $otherBonus,
            'deductions' => $deductions,
            'total_payout' => $totalPayout,
            'payment_status' => $validated['payment_status'],
            'payout_date' => $validated['payout_date'] ?? $record->payout_date,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', "13th Month Pay & Monetized SL for {$record->employee->full_name} updated successfully.");
    }

    /**
     * Print official 13th Month Pay & SL Voucher / Payslip.
     */
    public function printPayslip(ThirteenthMonthPay $record)
    {
        $record->load('employee');
        return view('manager.payroll.thirteenth_month.payslip_print', compact('record'));
    }

    /**
     * Print full annual 13th Month Summary Report for accounting.
     */
    public function printSummary(int $year)
    {
        $records = ThirteenthMonthPay::where('year', $year)
            ->with('employee')
            ->get()
            ->sortBy('employee.first_name');

        return view('manager.payroll.thirteenth_month.summary_print', compact('records', 'year'));
    }

    /**
     * Delete a 13th month pay record.
     */
    public function destroy(ThirteenthMonthPay $record)
    {
        $year = $record->year;
        $name = $record->employee->full_name;
        $record->delete();

        return redirect()->route('manager.payroll.thirteenth_month.index', ['year' => $year])
            ->with('success', "13th month record for {$name} deleted.");
    }
}
