<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThirteenthMonthPay extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'year',
        'basic_salary',
        'daily_rate',
        'monthly_breakdown',
        'total_basic_earned',
        'thirteenth_month_pay',
        'sl_total_quota',
        'sl_days_used',
        'sl_days_remaining',
        'sl_monetized_amount',
        'other_bonus',
        'deductions',
        'total_payout',
        'payment_status',
        'payout_date',
        'notes',
    ];

    protected $casts = [
        'monthly_breakdown' => 'array',
        'basic_salary' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'total_basic_earned' => 'decimal:2',
        'thirteenth_month_pay' => 'decimal:2',
        'sl_total_quota' => 'decimal:2',
        'sl_days_used' => 'decimal:2',
        'sl_days_remaining' => 'decimal:2',
        'sl_monetized_amount' => 'decimal:2',
        'other_bonus' => 'decimal:2',
        'deductions' => 'decimal:2',
        'total_payout' => 'decimal:2',
        'payout_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Compute default 13th month data & monetized SL for an employee and year.
     * Formula:
     * 1. Number of days work/present in a month X Rate = Month bonus computation
     * 2. Total month bonus / 12 = 13 month bonus pay
     * 3. Monetized SL = Unused SL days * Rate
     * 4. Total Payout = 13th month pay + Monetized SL
     */
    public static function computeDefaults(Employee $employee, int $year): array
    {
        $dailyRate = floatval($employee->daily_rate) ?: round(floatval($employee->basic_salary) / 22, 2);
        $monthlySalary = floatval($employee->basic_salary);

        $hiredDate = $employee->date_hired ? Carbon::parse($employee->date_hired) : null;
        $breakdown = [];
        $totalBasicEarned = 0.00;

        for ($m = 1; $m <= 12; $m++) {
            $monthDate = Carbon::createFromDate($year, $m, 1);
            $monthName = $monthDate->format('F');
            $monthShort = $monthDate->format('M');

            // Determine if employee was active/hired during this month
            $isHired = true;
            if ($hiredDate) {
                if ($hiredDate->year > $year || ($hiredDate->year == $year && $hiredDate->month > $m)) {
                    $isHired = false;
                }
            }

            if (!$isHired) {
                $daysWorked = 0;
            } else {
                // Check actual logged DTR records for this month
                $dtrCount = DtrRecord::where('employee_id', $employee->id)
                    ->whereYear('record_date', $year)
                    ->whereMonth('record_date', $m)
                    ->whereIn('status', ['present', 'late', 'on_leave'])
                    ->count();

                // Check PayrollRecord days worked if processed
                $payrollDays = floatval(PayrollRecord::where('employee_id', $employee->id)
                    ->whereHas('payrollPeriod', function ($q) use ($year, $m) {
                        $q->whereYear('start_date', $year)->whereMonth('start_date', $m);
                    })
                    ->sum('days_worked'));

                if ($dtrCount > 0) {
                    $daysWorked = $dtrCount;
                } elseif ($payrollDays > 0) {
                    $daysWorked = $payrollDays;
                } else {
                    // Default standard 22 working days for regular months
                    $daysWorked = 22;
                }
            }

            // Month bonus computation: Days worked X Rate
            $monthAmount = round($daysWorked * $dailyRate, 2);
            $totalBasicEarned += $monthAmount;

            $breakdown[] = [
                'month' => $m,
                'month_name' => $monthName,
                'month_short' => $monthShort,
                'days_worked' => $daysWorked,
                'rate' => $dailyRate,
                'amount' => $monthAmount,
            ];
        }

        // 13th Month Pay = Total Month Bonus / 12
        $thirteenthMonthPay = round($totalBasicEarned / 12, 2);

        // Monetized Sick Leave (SL): 5 days annual quota - SL used
        $leaveBalances = LeaveApplication::getEmployeeLeaveBalances($employee->id, $year);
        $slQuota = LeaveApplication::LIMIT_SL; // 5 days
        $slUsed = $leaveBalances['sl']['used'] ?? 0;
        $slRemaining = $leaveBalances['sl']['remaining'] ?? max(0, $slQuota - $slUsed);
        $slMonetizedAmount = round($slRemaining * $dailyRate, 2);

        $totalPayout = round($thirteenthMonthPay + $slMonetizedAmount, 2);

        return [
            'employee_id' => $employee->id,
            'year' => $year,
            'basic_salary' => $monthlySalary,
            'daily_rate' => $dailyRate,
            'monthly_breakdown' => $breakdown,
            'total_basic_earned' => $totalBasicEarned,
            'thirteenth_month_pay' => $thirteenthMonthPay,
            'sl_total_quota' => $slQuota,
            'sl_days_used' => $slUsed,
            'sl_days_remaining' => $slRemaining,
            'sl_monetized_amount' => $slMonetizedAmount,
            'other_bonus' => 0.00,
            'deductions' => 0.00,
            'total_payout' => $totalPayout,
            'payment_status' => 'unpaid',
            'payout_date' => Carbon::createFromDate($year, 12, 15)->toDateString(),
        ];
    }
}
