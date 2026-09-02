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

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Checks how many paid leave days the employee has used this year.
     * Default annual limit is 5 days (from client flowchart).
     */
    public static function getPaidLeaveDaysUsed(int $employeeId, int $year): int
    {
        return (int) self::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('is_paid', true)
            ->whereYear('start_date', $year)
            ->sum('days_count');
    }
}
