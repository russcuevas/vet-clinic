<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveAnnualLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'employee_id',
        'employee_code',
        'employee_name',
        'position',
        'basic_salary',
        'daily_rate',
        'vl_quota',
        'vl_used',
        'vl_forfeited',
        'sl_quota',
        'sl_used',
        'sl_unused',
        'sl_payout_amount',
        'sl_credited',
        'spl_quota',
        'spl_used',
        'spl_forfeited',
        'total_quota',
        'total_used',
        'archived_at',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'basic_salary' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'sl_payout_amount' => 'decimal:2',
        'sl_credited' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Archive/snapshot all active employees for a given year.
     */
    public static function archiveYear(int $year, ?string $notes = null): int
    {
        $employees = Employee::where('status', 'active')->get();
        $count = 0;

        foreach ($employees as $emp) {
            $balances = LeaveApplication::getEmployeeLeaveBalances($emp->id, $year);
            $dailyRate = floatval($emp->daily_rate) ?: (floatval($emp->basic_salary) / 22);

            $vlUsed = $balances['vl']['used'];
            $vlForfeited = $balances['vl']['remaining']; // VL is non-convertible

            $slUsed = $balances['sl']['used'];
            $slUnused = $balances['sl']['remaining']; // SL is convertible
            $slPayout = round($slUnused * $dailyRate, 2);

            $splUsed = $balances['spl']['used'];
            $splForfeited = $balances['spl']['remaining']; // SPL is non-convertible

            $totalUsed = $balances['total']['used'];

            $isCredited = EmployeeIncentive::where('employee_id', $emp->id)
                ->where('title', 'like', "%Sick Leave (SL) Conversion - {$year}%")
                ->exists();

            self::updateOrCreate(
                ['year' => $year, 'employee_id' => $emp->id],
                [
                    'employee_code' => $emp->employee_code,
                    'employee_name' => $emp->full_name,
                    'position' => $emp->position,
                    'basic_salary' => $emp->basic_salary,
                    'daily_rate' => $dailyRate,
                    'vl_quota' => 5,
                    'vl_used' => $vlUsed,
                    'vl_forfeited' => $vlForfeited,
                    'sl_quota' => 5,
                    'sl_used' => $slUsed,
                    'sl_unused' => $slUnused,
                    'sl_payout_amount' => $slPayout,
                    'sl_credited' => $isCredited,
                    'spl_quota' => 2,
                    'spl_used' => $splUsed,
                    'spl_forfeited' => $splForfeited,
                    'total_quota' => 12,
                    'total_used' => $totalUsed,
                    'archived_at' => now(),
                    'notes' => $notes ?: "Archived annual leave summary snapshot for year {$year}.",
                ]
            );
            $count++;
        }

        return $count;
    }
}
