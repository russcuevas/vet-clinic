<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeIncentive extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'incentive_rule_id',
        'title',
        'calculation_basis',
        'rate_applied',
        'base_amount_or_count',
        'total_incentive',
        'date_earned',
        'payroll_period_id',
        'notes',
    ];

    protected $casts = [
        'rate_applied' => 'decimal:2',
        'base_amount_or_count' => 'decimal:2',
        'total_incentive' => 'decimal:2',
        'date_earned' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function rule()
    {
        return $this->belongsTo(IncentiveRule::class, 'incentive_rule_id');
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }
}
