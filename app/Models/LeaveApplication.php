<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'days_count',
        'reason',
        'status',
        'is_paid',
        'exceeded_limit',
        'approved_by',
        'admin_remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_count' => 'integer',
        'is_paid' => 'boolean',
        'exceeded_limit' => 'boolean',
    ];

    public const LIMIT_VL = 5;
    public const LIMIT_SL = 5;
    public const LIMIT_SPL = 2;
    public const TOTAL_ANNUAL_LIMIT = 12;

    public const LEAVE_LIMITS = [
        'vacation_leave' => self::LIMIT_VL,
        'sick_leave' => self::LIMIT_SL,
        'special_leave' => self::LIMIT_SPL,
    ];

    public const LEAVE_LABELS = [
        'vacation_leave' => 'Vacation Leave (VL)',
        'sick_leave' => 'Sick Leave (SL)',
        'special_leave' => 'Special Leave (SPL)',
    ];

    public const LEAVE_SHORT_LABELS = [
        'vacation_leave' => 'VL',
        'sick_leave' => 'SL',
        'special_leave' => 'SPL',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Checks how many paid leave days the employee has used for a specific leave type this year.
     */
    public static function getPaidLeaveDaysUsedByType(int $employeeId, string $leaveType, int $year): int
    {
        return (int) self::where('employee_id', $employeeId)
            ->where('leave_type', $leaveType)
            ->where('status', 'approved')
            ->where('is_paid', true)
            ->whereYear('start_date', $year)
            ->sum('days_count');
    }

    /**
     * Checks total paid leave days used across all types this year.
     */
    public static function getPaidLeaveDaysUsed(int $employeeId, int $year): int
    {
        return (int) self::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('is_paid', true)
            ->whereYear('start_date', $year)
            ->sum('days_count');
    }

    /**
     * Get detailed leave balance breakdown (VL, SL, SPL, and Total Annual).
     */
    public static function getEmployeeLeaveBalances(int $employeeId, int $year): array
    {
        $vlUsed = self::getPaidLeaveDaysUsedByType($employeeId, 'vacation_leave', $year);
        $slUsed = self::getPaidLeaveDaysUsedByType($employeeId, 'sick_leave', $year);
        $splUsed = self::getPaidLeaveDaysUsedByType($employeeId, 'special_leave', $year);
        $totalUsed = $vlUsed + $slUsed + $splUsed;

        return [
            'vl' => [
                'name' => 'Vacation Leave (VL)',
                'code' => 'VL',
                'used' => $vlUsed,
                'limit' => self::LIMIT_VL,
                'remaining' => max(0, self::LIMIT_VL - $vlUsed),
            ],
            'sl' => [
                'name' => 'Sick Leave (SL)',
                'code' => 'SL',
                'used' => $slUsed,
                'limit' => self::LIMIT_SL,
                'remaining' => max(0, self::LIMIT_SL - $slUsed),
            ],
            'spl' => [
                'name' => 'Special Leave (SPL)',
                'code' => 'SPL',
                'used' => $splUsed,
                'limit' => self::LIMIT_SPL,
                'remaining' => max(0, self::LIMIT_SPL - $splUsed),
            ],
            'total' => [
                'used' => $totalUsed,
                'limit' => self::TOTAL_ANNUAL_LIMIT,
                'remaining' => max(0, self::TOTAL_ANNUAL_LIMIT - $totalUsed),
            ],
        ];
    }
}
